<?php

namespace App\Controller;

use App\Services\InteractionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/interactions', name: 'interaction_')]
class InteractionController extends AbstractController
{
    private InteractionService $interactionService;

    public function __construct(InteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $interactions = $this->interactionService->getAllInteractions();
        return new JsonResponse(['data' => $interactions], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $interaction = $this->interactionService->getInteractionById($id);
        return new JsonResponse(['data' => $interaction], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $interaction = $this->interactionService->createInteraction($data);

        return new JsonResponse(['data' => $interaction], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $interaction = $this->interactionService->updateInteraction($id, $data);

        return new JsonResponse(['data' => $interaction], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->interactionService->deleteInteraction($id);

        return new JsonResponse(['message' => 'Interaction deleted successfully'], Response::HTTP_OK);
    }
}
