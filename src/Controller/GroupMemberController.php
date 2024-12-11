<?php

namespace App\Controller;

use App\Repository\GroupMemberRepository;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Entity\GroupMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/group-members', name: 'group_member_')]
class GroupMemberController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(GroupMemberRepository $groupMemberRepository): JsonResponse
    {
        $groupMembers = $groupMemberRepository->findAll();

        $groupMemberData = array_map(function (GroupMember $groupMember) {
            return [
                'id' => $groupMember->getId(),
                'userId' => $groupMember->getUser()->getId(),
                'groupId' => $groupMember->getGroup()->getId(),
                'role' => $groupMember->getRole(),
            ];
        }, $groupMembers);

        return new JsonResponse(['data' => $groupMemberData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(GroupMemberRepository $groupMemberRepository, int $id): JsonResponse
    {
        $groupMember = $groupMemberRepository->find($id);

        if (!$groupMember) {
            return new JsonResponse(['error' => 'GroupMember not found'], Response::HTTP_NOT_FOUND);
        }

        $groupMemberData = [
            'id' => $groupMember->getId(),
            'userId' => $groupMember->getUser()->getId(),
            'groupId' => $groupMember->getGroup()->getId(),
            'role' => $groupMember->getRole(),
            'joinedAt' => $groupMember->getJoinedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupMemberData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository, GroupRepository $groupRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid user ID'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['groupId']) || !is_numeric($data['groupId'])) {
            return new JsonResponse(['error' => 'Invalid group ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $group = $groupRepository->find($data['groupId']);
        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $groupMember = new GroupMember();
        $groupMember->setUser($user);
        $groupMember->setGroup($group);
        $groupMember->setJoinedAt(new DateTime());
        $groupMember->setRole($data['role'] ?? 'member');

        $this->entityManager->persist($groupMember);
        $this->entityManager->flush();

        $groupMemberData = [
            'id' => $groupMember->getId(),
            'userId' => $groupMember->getUser()->getId(),
            'groupId' => $groupMember->getGroup()->getId(),
            'role' => $groupMember->getRole(),
            'joinedAt' => $groupMember->getJoinedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupMemberData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, GroupMemberRepository $groupMemberRepository, int $id): JsonResponse
    {
        $groupMember = $groupMemberRepository->find($id);

        if (!$groupMember) {
            return new JsonResponse(['error' => 'GroupMember not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $groupMember->setRole($data['role'] ?? $groupMember->getRole());

        $this->entityManager->flush();

        $groupMemberData = [
            'id' => $groupMember->getId(),
            'userId' => $groupMember->getUser()->getId(),
            'groupId' => $groupMember->getGroup()->getId(),
            'role' => $groupMember->getRole(),
            'joinedAt' => $groupMember->getJoinedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupMemberData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(GroupMemberRepository $groupMemberRepository, int $id): JsonResponse
    {
        $groupMember = $groupMemberRepository->find($id);

        if (!$groupMember) {
            return new JsonResponse(['error' => 'GroupMember not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($groupMember);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'GroupMember deleted successfully'], Response::HTTP_OK);
    }
}
