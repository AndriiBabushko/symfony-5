<?php

namespace App\Controller;

use App\Services\GroupMemberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/group-members', name: 'group_member_')]
class GroupMemberController extends AbstractController
{
    private GroupMemberService $groupMemberService;

    public function __construct(GroupMemberService $groupMemberService)
    {
        $this->groupMemberService = $groupMemberService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $groupMembers = $this->groupMemberService->getAllGroupMembers();
        return new JsonResponse(['data' => $groupMembers], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $groupMember = $this->groupMemberService->getGroupMemberById($id);
        return new JsonResponse(['data' => $groupMember], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $groupMember = $this->groupMemberService->createGroupMember($data);

        return new JsonResponse(['data' => $groupMember], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $groupMember = $this->groupMemberService->updateGroupMember($id, $data);

        return new JsonResponse(['data' => $groupMember], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->groupMemberService->deleteGroupMember($id);

        return new JsonResponse(['message' => 'GroupMember deleted successfully'], Response::HTTP_OK);
    }
}
