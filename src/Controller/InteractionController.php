<?php

namespace App\Controller;

use App\Repository\InteractionRepository;
use App\Entity\Interaction;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/interactions', name: 'interaction_')]
class InteractionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(InteractionRepository $interactionRepository): JsonResponse
    {
        $interactions = $interactionRepository->findAll();

        $interactionData = array_map(function (Interaction $interaction) {
            return [
                'id' => $interaction->getId(),
                'interactionType' => $interaction->getInteractionType(),
                'userId' => $interaction->getUser()->getId(),
                'targetId' => $interaction->getTargetId(),
                'createdAt' => $interaction->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $interactions);

        return new JsonResponse(['data' => $interactionData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(InteractionRepository $interactionRepository, int $id): JsonResponse
    {
        $interaction = $interactionRepository->find($id);

        if (!$interaction) {
            return new JsonResponse(['error' => 'Interaction not found'], Response::HTTP_NOT_FOUND);
        }

        $interactionData = [
            'id' => $interaction->getId(),
            'interactionType' => $interaction->getInteractionType(),
            'userId' => $interaction->getUser()->getId(),
            'targetId' => $interaction->getTargetId(),
            'createdAt' => $interaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $interactionData], Response::HTTP_OK);
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

        if (empty($data['targetId']) || !is_numeric($data['targetId'])) {
            return new JsonResponse(['error' => 'Invalid target ID'], Response::HTTP_BAD_REQUEST);
        }

        $interaction = new Interaction();
        $interaction->setUser($user);
        $interaction->setInteractionType($data['interactionType']);
        $interaction->setTargetId($data['targetId']);
        $interaction->setCreatedAt(new DateTime());

        $this->entityManager->persist($interaction);
        $this->entityManager->flush();

        $interactionData = [
            'id' => $interaction->getId(),
            'interactionType' => $interaction->getInteractionType(),
            'userId' => $interaction->getUser()->getId(),
            'targetId' => $interaction->getTargetId(),
            'createdAt' => $interaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $interactionData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, InteractionRepository $interactionRepository, int $id): JsonResponse
    {
        $interaction = $interactionRepository->find($id);

        if (!$interaction) {
            return new JsonResponse(['error' => 'Interaction not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $interaction->setInteractionType($data['interactionType']);
        $interaction->setTargetId($data['targetId'] ?? $interaction->getTargetId());

        $this->entityManager->flush();

        $interactionData = [
            'id' => $interaction->getId(),
            'interactionType' => $interaction->getInteractionType(),
            'userId' => $interaction->getUser()->getId(),
            'targetId' => $interaction->getTargetId(),
            'createdAt' => $interaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $interactionData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(InteractionRepository $interactionRepository, int $id): JsonResponse
    {
        $interaction = $interactionRepository->find($id);

        if (!$interaction) {
            return new JsonResponse(['error' => 'Interaction not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($interaction);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Interaction deleted successfully'], Response::HTTP_OK);
    }
}
