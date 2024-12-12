<?php

namespace App\Services;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageService
{
    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;
    private UserRepository $userRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageRepository $messageRepository,
        UserRepository $userRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllMessages(): array
    {
        $messages = $this->messageRepository->findAll();
        return array_map([$this, 'serializeMessage'], $messages);
    }

    public function getMessageById(int $id): array
    {
        $message = $this->messageRepository->find($id);

        if (!$message) {
            throw new NotFoundHttpException('Message not found');
        }

        return $this->serializeMessage($message);
    }

    public function createMessage(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $sender = $this->userRepository->find($data['senderId']);
        if (!$sender) {
            throw new NotFoundHttpException('Sender not found');
        }

        $receiver = $this->userRepository->find($data['receiverId']);
        if (!$receiver) {
            throw new NotFoundHttpException('Receiver not found');
        }

        $message = $this->createMessageObject($sender, $receiver, $data['content']);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->serializeMessage($message);
    }

    public function updateMessage(int $id, array $data): array
    {
        $message = $this->messageRepository->find($id);

        if (!$message) {
            throw new NotFoundHttpException('Message not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($message, $method)) {
                $message->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($message);

        $this->entityManager->flush();

        return $this->serializeMessage($message);
    }

    public function deleteMessage(int $id): void
    {
        $message = $this->messageRepository->find($id);

        if (!$message) {
            throw new NotFoundHttpException('Message not found');
        }

        $this->entityManager->remove($message);
        $this->entityManager->flush();
    }

    private function createMessageObject(User $sender, User $receiver, string $content): Message
    {
        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTime());

        return $message;
    }

    private function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'senderId' => $message->getSender()->getId(),
            'receiverId' => $message->getReceiver()->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
