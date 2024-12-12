<?php

namespace App\Services;

use App\Entity\Friend;
use App\Entity\User;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FriendService
{
    private EntityManagerInterface $entityManager;
    private FriendRepository $friendRepository;
    private UserRepository $userRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FriendRepository $friendRepository,
        UserRepository $userRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->friendRepository = $friendRepository;
        $this->userRepository = $userRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllFriends(): array
    {
        $friends = $this->friendRepository->findAll();
        return array_map([$this, 'serializeFriend'], $friends);
    }

    public function getFriendById(int $id): array
    {
        $friend = $this->friendRepository->find($id);

        if (!$friend) {
            throw new NotFoundHttpException('Friendship not found');
        }

        return $this->serializeFriend($friend);
    }

    public function createFriendship(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $friend = $this->userRepository->find($data['friendId']);
        if (!$friend) {
            throw new NotFoundHttpException('Friend not found');
        }

        $friendship = $this->createFriendObject($user, $friend, $data['status']);

        $this->entityManager->persist($friendship);
        $this->entityManager->flush();

        return $this->serializeFriend($friendship);
    }

    public function updateFriendship(int $id, array $data): array
    {
        $friend = $this->friendRepository->find($id);

        if (!$friend) {
            throw new NotFoundHttpException('Friendship not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($friend, $method)) {
                $friend->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($friend);

        $this->entityManager->flush();

        return $this->serializeFriend($friend);
    }

    public function deleteFriendship(int $id): void
    {
        $friend = $this->friendRepository->find($id);

        if (!$friend) {
            throw new NotFoundHttpException('Friendship not found');
        }

        $this->entityManager->remove($friend);
        $this->entityManager->flush();
    }

    private function createFriendObject(User $user, User $friend, string $status): Friend
    {
        $friendship = new Friend();
        $friendship->setUser($user);
        $friendship->setFriend($friend);
        $friendship->setStatus($status);
        $friendship->setCreatedAt(new \DateTime());

        return $friendship;
    }

    private function serializeFriend(Friend $friend): array
    {
        return [
            'id' => $friend->getId(),
            'status' => $friend->getStatus(),
            'userId' => $friend->getUser()->getId(),
            'friendId' => $friend->getFriend()->getId(),
            'createdAt' => $friend->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
