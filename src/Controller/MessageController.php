<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Services\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/messages', name: 'message_')]
class MessageController extends AbstractController
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(Request $request, MessageRepository $messageRepository): JsonResponse
    {
        $requestData = $request->query->all();
        $itemsPerPage = isset($requestData['itemsPerPage']) ? (int)$requestData['itemsPerPage'] : 10;
        $page = isset($requestData['page']) ? (int)$requestData['page'] : 1;

        $messagesData = $messageRepository->getAllMessagesByFilter($requestData, $itemsPerPage, $page);

        $messages = array_map([$this->messageService, 'serializeMessage'], $messagesData['messages']);

        return new JsonResponse([
            'data' => $messages,
            'meta' => [
                'totalItems' => $messagesData['totalItems'],
                'totalPageCount' => $messagesData['totalPageCount'],
                'currentPage' => $page,
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $message = $this->messageService->getMessageById($id);

        return new JsonResponse(['data' => $message], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $this->messageService->createMessage($data);

        return new JsonResponse(['data' => $message], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $this->messageService->updateMessage($id, $data);

        return new JsonResponse(['data' => $message], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->messageService->deleteMessage($id);

        return new JsonResponse(['message' => 'Message deleted successfully'], Response::HTTP_OK);
    }
}
