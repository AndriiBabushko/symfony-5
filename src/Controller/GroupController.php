<?php

namespace App\Controller;

use App\Services\GroupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/groups', name: 'group_')]
class GroupController extends AbstractController
{
    private GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $groups = $this->groupService->getAllGroups();
        return new JsonResponse(['data' => $groups], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $group = $this->groupService->getGroupById($id);
        return new JsonResponse(['data' => $group], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $group = $this->groupService->createGroup($data);

        return new JsonResponse(['data' => $group], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $group = $this->groupService->updateGroup($id, $data);

        return new JsonResponse(['data' => $group], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->groupService->deleteGroup($id);

        return new JsonResponse(['message' => 'Group deleted successfully'], Response::HTTP_OK);
    }
}
