<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Services\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications', name: 'notification_')]
class NotificationController extends AbstractController
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(Request $request, NotificationRepository $notificationRepository): JsonResponse
    {
        $requestData = $request->query->all();
        $itemsPerPage = isset($requestData['itemsPerPage']) ? (int)$requestData['itemsPerPage'] : 10;
        $page = isset($requestData['page']) ? (int)$requestData['page'] : 1;

        $notificationsData = $notificationRepository->getAllNotificationsByFilter($requestData, $itemsPerPage, $page);

        $notifications = array_map(
            [$this->notificationService, 'serializeNotification'],
            $notificationsData['notifications']
        );

        return new JsonResponse([
            'data' => $notifications,
            'meta' => [
                'totalItems' => $notificationsData['totalItems'],
                'totalPageCount' => $notificationsData['totalPageCount'],
                'currentPage' => $page,
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $notification = $this->notificationService->getNotificationById($id);
        return new JsonResponse(['data' => $notification], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $notification = $this->notificationService->createNotification($data);

        return new JsonResponse(['data' => $notification], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $notification = $this->notificationService->updateNotification($id, $data);

        return new JsonResponse(['data' => $notification], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->notificationService->deleteNotification($id);

        return new JsonResponse(['message' => 'Notification deleted successfully'], Response::HTTP_OK);
    }
}
