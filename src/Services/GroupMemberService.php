<?php

namespace App\Services;

use App\Entity\Group;
use App\Entity\GroupMember;
use App\Entity\User;
use App\Repository\GroupMemberRepository;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupMemberService
{
    private EntityManagerInterface $entityManager;
    private GroupMemberRepository $groupMemberRepository;
    private UserRepository $userRepository;
    private GroupRepository $groupRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GroupMemberRepository $groupMemberRepository,
        UserRepository $userRepository,
        GroupRepository $groupRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->groupMemberRepository = $groupMemberRepository;
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllGroupMembers(): array
    {
        $groupMembers = $this->groupMemberRepository->findAll();
        return array_map([$this, 'serializeGroupMember'], $groupMembers);
    }

    public function getGroupMemberById(int $id): array
    {
        $groupMember = $this->groupMemberRepository->find($id);

        if (!$groupMember) {
            throw new NotFoundHttpException('GroupMember not found');
        }

        return $this->serializeGroupMember($groupMember);
    }

    public function createGroupMember(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $group = $this->groupRepository->find($data['groupId']);
        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        $groupMember = $this->createGroupMemberObject($group, $user, $data['role'] ?? 'member');
        $this->entityManager->persist($groupMember);
        $this->entityManager->flush();

        return $this->serializeGroupMember($groupMember);
    }

    public function updateGroupMember(int $id, array $data): array
    {
        $groupMember = $this->groupMemberRepository->find($id);

        if (!$groupMember) {
            throw new NotFoundHttpException('GroupMember not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($groupMember, $method)) {
                $groupMember->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($groupMember);

        $this->entityManager->flush();

        return $this->serializeGroupMember($groupMember);
    }

    public function deleteGroupMember(int $id): void
    {
        $groupMember = $this->groupMemberRepository->find($id);

        if (!$groupMember) {
            throw new NotFoundHttpException('GroupMember not found');
        }

        $this->entityManager->remove($groupMember);
        $this->entityManager->flush();
    }

    private function createGroupMemberObject(Group $group, User $user, string $role): GroupMember
    {
        $groupMember = new GroupMember();
        $groupMember->setGroup($group);
        $groupMember->setUser($user);
        $groupMember->setRole($role);
        $groupMember->setJoinedAt(new \DateTime());

        return $groupMember;
    }

    private function serializeGroupMember(GroupMember $groupMember): array
    {
        return [
            'id' => $groupMember->getId(),
            'groupId' => $groupMember->getGroup()->getId(),
            'userId' => $groupMember->getUser()->getId(),
            'role' => $groupMember->getRole(),
            'joinedAt' => $groupMember->getJoinedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
