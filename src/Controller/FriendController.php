<?php

namespace App\Controller;

use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Entity\Friend;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/friends', name: 'friend_')]
class FriendController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(FriendRepository $friendRepository): JsonResponse
    {
        $friends = $friendRepository->findAll();

        $friendData = array_map(function (Friend $friend) {
            return [
                'id' => $friend->getId(),
                'status' => $friend->getStatus(),
                'userId' => $friend->getUser()->getId(),
                'friendId' => $friend->getFriend()->getId(),
                'createdAt' => $friend->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $friends);

        return new JsonResponse(['data' => $friendData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(FriendRepository $friendRepository, int $id): JsonResponse
    {
        $friend = $friendRepository->find($id);

        if (!$friend) {
            return new JsonResponse(['error' => 'Friendship not found'], Response::HTTP_NOT_FOUND);
        }

        $friendData = [
            'id' => $friend->getId(),
            'status' => $friend->getStatus(),
            'userId' => $friend->getUser()->getId(),
            'friendId' => $friend->getFriend()->getId(),
            'createdAt' => $friend->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $friendData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid user ID'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['friendId']) || !is_numeric($data['friendId'])) {
            return new JsonResponse(['error' => 'Invalid friend ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $friend = $userRepository->find($data['friendId']);
        if (!$friend) {
            return new JsonResponse(['error' => 'Friend not found'], Response::HTTP_NOT_FOUND);
        }

        $friendship = new Friend();
        $friendship->setStatus($data['status']);
        $friendship->setUser($user);
        $friendship->setFriend($friend);
        $friendship->setCreatedAt(new DateTime());

        $this->entityManager->persist($friendship);
        $this->entityManager->flush();

        $friendshipData = [
            'id' => $friendship->getId(),
            'status' => $friendship->getStatus(),
            'userId' => $friendship->getUser()->getId(),
            'friendId' => $friendship->getFriend()->getId(),
            'createdAt' => $friendship->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $friendshipData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, FriendRepository $friendRepository, int $id): JsonResponse
    {
        $friend = $friendRepository->find($id);

        if (!$friend) {
            return new JsonResponse(['error' => 'Friendship not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['friendId']) && is_numeric($data['friendId'])) {
            $newFriend = $this->entityManager->getRepository(UserRepository::class)->find($data['friendId']);
            if (!$newFriend) {
                return new JsonResponse(['error' => 'Friend not found'], Response::HTTP_NOT_FOUND);
            }
            $friend->setFriend($newFriend);
        }

        $friend->setStatus($data['status'] ?? $friend->getStatus());

        $this->entityManager->flush();

        $friendData = [
            'id' => $friend->getId(),
            'status' => $friend->getStatus(),
            'userId' => $friend->getUser()->getId(),
            'friendId' => $friend->getFriend()->getId(),
            'createdAt' => $friend->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $friendData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(FriendRepository $friendRepository, int $id): JsonResponse
    {
        $friend = $friendRepository->find($id);

        if (!$friend) {
            return new JsonResponse(['error' => 'Friendship not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($friend);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Friendship deleted successfully'], Response::HTTP_OK);
    }
}
