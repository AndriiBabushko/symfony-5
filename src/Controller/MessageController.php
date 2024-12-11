<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/messages', name: 'message_')]
class MessageController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->findAll();

        $messageData = array_map(function (Message $message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'senderId' => $message->getSender()->getId(),
                'receiverId' => $message->getReceiver()->getId(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $messages);

        return new JsonResponse(['data' => $messageData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(MessageRepository $messageRepository, int $id): JsonResponse
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $messageData = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'senderId' => $message->getSender()->getId(),
            'receiverId' => $message->getReceiver()->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $messageData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['senderId']) || !is_numeric($data['senderId'])) {
            return new JsonResponse(['error' => 'Invalid sender ID'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['receiverId']) || !is_numeric($data['receiverId'])) {
            return new JsonResponse(['error' => 'Invalid receiver ID'], Response::HTTP_BAD_REQUEST);
        }

        $sender = $userRepository->find($data['senderId']);
        if (!$sender) {
            return new JsonResponse(['error' => 'Sender not found'], Response::HTTP_NOT_FOUND);
        }

        $receiver = $userRepository->find($data['receiverId']);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Receiver not found'], Response::HTTP_NOT_FOUND);
        }

        $message = new Message();
        $message->setContent($data['content'] ?? null);
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setCreatedAt(new \DateTime());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $messageData = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'senderId' => $message->getSender()->getId(),
            'receiverId' => $message->getReceiver()->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $messageData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, MessageRepository $messageRepository, int $id): JsonResponse
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $message->setContent($data['content'] ?? $message->getContent());
        $this->entityManager->flush();

        $messageData = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'senderId' => $message->getSender()->getId(),
            'receiverId' => $message->getReceiver()->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $messageData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(MessageRepository $messageRepository, int $id): JsonResponse
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($message);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Message deleted successfully'], Response::HTTP_OK);
    }
}
