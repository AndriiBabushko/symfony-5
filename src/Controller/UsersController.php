<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\UserService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users', name: 'user_')]
class UsersController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid request data.'], Response::HTTP_BAD_REQUEST);
        }

        $hashedPassword = $passwordHasher->hashPassword(
            new User(),
            $data['password']
        );

        $user = $this->userService->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword
        );

        return new JsonResponse(['data' => $this->userService->serializeUser($user)], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required.'], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->userService->getUserByEmail($email);


        if (!$user || !$this->userService->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = $this->userService->generateJWT($user);

        return new JsonResponse(['token' => $token], Response::HTTP_OK);
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(Request $request, UserRepository $userRepository): JsonResponse
    {
        $requestData = $request->query->all();
        $itemsPerPage = isset($requestData['itemsPerPage']) ? (int)$requestData['itemsPerPage'] : 10;
        $page = isset($requestData['page']) ? (int)$requestData['page'] : 1;

        $usersData = $userRepository->getAllUsersByFilter($requestData, $itemsPerPage, $page);

        $users = array_map([$this->userService, 'serializeUser'], $usersData['users']);

        return new JsonResponse([
            'data' => $users,
            'meta' => [
                'totalItems' => $usersData['totalItems'],
                'totalPageCount' => $usersData['totalPageCount'],
                'currentPage' => $page,
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $this->userService->serializeUser($user)], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->userService->createUser(
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['profilePicture'] ?? null
        );

        return new JsonResponse(['data' => $this->userService->serializeUser($user)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->userService->updateUser($user, $data);

        return new JsonResponse(['data' => $this->userService->serializeUser($user)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->userService->deleteUser($user);

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/test_user_role', name: 'user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userEndpoint(): JsonResponse
    {
        return new JsonResponse(['message' => 'Access granted for ROLE_USER.'], 200);
    }

    #[Route('/test_admin_role', name: 'admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEndpoint(): JsonResponse
    {
        return new JsonResponse(['message' => 'Access granted for ROLE_ADMIN.'], 200);
    }
}
