<?php

namespace App\Services;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupService
{
    private EntityManagerInterface $entityManager;
    private GroupRepository $groupRepository;
    private UserRepository $userRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllGroups(): array
    {
        $groups = $this->groupRepository->findAll();
        return array_map([$this, 'serializeGroup'], $groups);
    }

    public function getGroupById(int $id): array
    {
        $group = $this->groupRepository->find($id);

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        return $this->serializeGroup($group);
    }

    public function createGroup(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $creator = $this->userRepository->find($data['creatorId']);
        if (!$creator) {
            throw new NotFoundHttpException('Creator not found');
        }

        $group = $this->createGroupObject($data['name'], $data['description'], $creator);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return $this->serializeGroup($group);
    }

    public function updateGroup(int $id, array $data): array
    {
        $group = $this->groupRepository->find($id);

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($group, $method)) {
                $group->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($group);

        $this->entityManager->flush();

        return $this->serializeGroup($group);
    }

    public function deleteGroup(int $id): void
    {
        $group = $this->groupRepository->find($id);

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }

    private function createGroupObject(string $name, string $description, User $creator): Group
    {
        $group = new Group();
        $group->setName($name);
        $group->setDescription($description);
        $group->setCreator($creator);
        $group->setCreatedAt(new \DateTime());

        return $group;
    }

    private function serializeGroup(Group $group): array
    {
        return [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'creatorId' => $group->getCreator()->getId(),
            'createdAt' => $group->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
