<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'user_')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $userData = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'profilePicture' => $user->getProfilePicture(),
            ];
        }, $users);

        return new JsonResponse(['data' => $userData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'profilePicture' => $user->getProfilePicture(),
        ];

        return new JsonResponse(['data' => $userData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setUsername($data['username'] ?? null);
        $user->setEmail($data['email'] ?? null);
        $user->setPassword($data['password'] ?? null);
        $user->setCreatedAt(new \DateTime());
        $user->setProfilePicture($data['profilePicture'] ?? null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'profilePicture' => $user->getProfilePicture(),
        ];

        return new JsonResponse(['data' => $userData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $existingUser = $userRepository->findOneBy(['email' => $data['email']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }

        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setPassword($data['password'] ?? $user->getPassword());
        $user->setProfilePicture($data['profilePicture'] ?? $user->getProfilePicture());

        $this->entityManager->flush();

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'profilePicture' => $user->getProfilePicture(),
        ];

        return new JsonResponse(['data' => $userData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }
}
