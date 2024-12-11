<?php

namespace App\Controller;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/groups', name: 'group_')]
class GroupController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(GroupRepository $groupRepository): JsonResponse
    {
        $groups = $groupRepository->findAll();

        $groupData = array_map(function (Group $group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'description' => $group->getDescription(),
                'creatorId' => $group->getCreator()->getId(),
                'createdAt' => $group->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $groups);

        return new JsonResponse(['data' => $groupData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(GroupRepository $groupRepository, int $id): JsonResponse
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $groupData = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'creatorId' => $group->getCreator()->getId(),
            'createdAt' => $group->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['creatorId']) || !is_numeric($data['creatorId'])) {
            return new JsonResponse(['error' => 'Invalid creator ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['creatorId']);
        if (!$user) {
            return new JsonResponse(['error' => 'Creator not found'], Response::HTTP_NOT_FOUND);
        }

        $group = new Group();
        $group->setName($data['name'] ?? null);
        $group->setDescription($data['description'] ?? null);
        $group->setCreatedAt(new \DateTime());
        $group->setCreator($user);

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $groupData = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'creatorId' => $group->getCreator()->getId(),
            'createdAt' => $group->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, GroupRepository $groupRepository, int $id): JsonResponse
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $group->setName($data['name'] ?? $group->getName());
        $group->setDescription($data['description'] ?? $group->getDescription());

        $this->entityManager->flush();

        $groupData = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'creatorId' => $group->getCreator()->getId(),
            'createdAt' => $group->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $groupData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(GroupRepository $groupRepository, int $id): JsonResponse
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            return new JsonResponse(['error' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($group);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Group deleted successfully'], Response::HTTP_OK);
    }
}
