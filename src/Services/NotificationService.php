<?php

namespace App\Services;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;
    private UserRepository $userRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        UserRepository $userRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllNotifications(): array
    {
        $notifications = $this->notificationRepository->findAll();
        return array_map([$this, 'serializeNotification'], $notifications);
    }

    public function getNotificationById(int $id): array
    {
        $notification = $this->notificationRepository->find($id);

        if (!$notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        return $this->serializeNotification($notification);
    }

    public function createNotification(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $notification = $this->createNotificationObject($user, $data['type'], $data['data'] ?? null);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $this->serializeNotification($notification);
    }

    public function updateNotification(int $id, array $data): array
    {
        $notification = $this->notificationRepository->find($id);

        if (!$notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($notification, $method)) {
                $notification->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($notification);

        $this->entityManager->flush();

        return $this->serializeNotification($notification);
    }

    public function deleteNotification(int $id): void
    {
        $notification = $this->notificationRepository->find($id);

        if (!$notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    private function createNotificationObject(User $user, string $type, ?array $data): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setData($data);
        $notification->setCreatedAt(new \DateTime());
        $notification->setIsRead(false);

        return $notification;
    }

    private function serializeNotification(Notification $notification): array
    {
        return [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'userId' => $notification->getUser()->getId(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            'isRead' => $notification->isRead(),
        ];
    }
}
