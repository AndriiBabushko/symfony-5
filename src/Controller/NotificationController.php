<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Entity\Notification;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications', name: 'notification_')]
class NotificationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(NotificationRepository $notificationRepository): JsonResponse
    {
        $notifications = $notificationRepository->findAll();

        $notificationData = array_map(function (Notification $notification) {
            return [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'data' => $notification->getData(),
                'userId' => $notification->getUser()->getId(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $notifications);

        return new JsonResponse(['data' => $notificationData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(NotificationRepository $notificationRepository, int $id): JsonResponse
    {
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $notificationData = [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'userId' => $notification->getUser()->getId(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $notificationData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid user ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $notification = new Notification();
        $notification->setType($data['type'] ?? null);
        $notification->setData($data['data'] ?? null);
        $notification->setUser($user);
        $notification->setCreatedAt(new DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $notificationData = [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'userId' => $notification->getUser()->getId(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $notificationData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, NotificationRepository $notificationRepository, int $id): JsonResponse
    {
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $notification->setType($data['type'] ?? $notification->getType());
        $notification->setData($data['data'] ?? $notification->getData());

        $this->entityManager->flush();

        $notificationData = [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'userId' => $notification->getUser()->getId(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $notificationData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(NotificationRepository $notificationRepository, int $id): JsonResponse
    {
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Notification deleted successfully'], Response::HTTP_OK);
    }
}
