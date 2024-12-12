<?php

namespace App\Controller;

use App\Services\ReactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reactions', name: 'reaction_')]
class ReactionController extends AbstractController
{
    private ReactionService $reactionService;

    public function __construct(ReactionService $reactionService)
    {
        $this->reactionService = $reactionService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $reactions = $this->reactionService->getAllReactions();

        return new JsonResponse(['data' => $reactions], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $reaction = $this->reactionService->getReactionById($id);

        return new JsonResponse(['data' => $reaction], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reaction = $this->reactionService->createReaction($data);

        return new JsonResponse(['data' => $reaction], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reaction = $this->reactionService->updateReaction($id, $data);

        return new JsonResponse(['data' => $reaction], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->reactionService->deleteReaction($id);

        return new JsonResponse(['message' => 'Reaction deleted successfully'], Response::HTTP_OK);
    }
}
