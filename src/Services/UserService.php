<?php

namespace App\Services;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private RequestCheckerService $requestCheckerService;
    private JWTTokenManagerInterface $jwtManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestCheckerService $requestCheckerService,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->requestCheckerService = $requestCheckerService;
        $this->jwtManager = $jwtManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function generateJWT(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function isPasswordValid(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    /**
     * @param User $user
     * @return array
     */
    public function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'profilePicture' => $user->getProfilePicture(),
        ];
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string|null $profilePicture
     * @return User
     */
    public function createUser(
        string $username,
        string $email,
        string $password,
        ?string $profilePicture = null
    ): User {
        $user = $this->createUserObject($username, $email, $password, $profilePicture);

        $this->requestCheckerService->validateRequestDataByConstraints($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param array $data
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     */
    public function updateUser(User $user, array $data): void
    {
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException(json_encode([
                    'field' => 'email',
                    'message' => 'Email already exists.'
                ]));
            }
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (!method_exists($user, $method)) {
                continue;
            }
            $user->$method($value);
        }

        $this->requestCheckerService->validateRequestDataByConstraints($user);

        $this->entityManager->flush();
    }


    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string|null $profilePicture
     * @return User
     */
    private function createUserObject(
        string $username,
        string $email,
        string $password,
        ?string $profilePicture = null
    ): User {
        $user = new User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setPassword($password)
            ->setProfilePicture($profilePicture)
            ->setCreatedAt(new DateTime());

        return $user;
    }
}
