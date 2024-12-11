<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\ReactionRepository;
use App\Entity\Reaction;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reactions', name: 'reaction_')]
class ReactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(ReactionRepository $reactionRepository): JsonResponse
    {
        $reactions = $reactionRepository->findAll();

        $reactionData = array_map(function (Reaction $reaction) {
            return [
                'id' => $reaction->getId(),
                'type' => $reaction->getType(),
                'userId' => $reaction->getUser()->getId(),
                'postId' => $reaction->getPost()->getId(),
                'createdAt' => $reaction->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $reactions);

        return new JsonResponse(['data' => $reactionData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(ReactionRepository $reactionRepository, int $id): JsonResponse
    {
        $reaction = $reactionRepository->find($id);

        if (!$reaction) {
            return new JsonResponse(['error' => 'Reaction not found'], Response::HTTP_NOT_FOUND);
        }

        $reactionData = [
            'id' => $reaction->getId(),
            'type' => $reaction->getType(),
            'userId' => $reaction->getUser()->getId(),
            'postId' => $reaction->getPost()->getId(),
            'createdAt' => $reaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $reactionData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository, PostRepository $postRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['userId']) || !is_numeric($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid user ID'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['postId']) || !is_numeric($data['postId'])) {
            return new JsonResponse(['error' => 'Invalid post ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $postRepository->find($data['postId']);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $reaction = new Reaction();
        $reaction->setType($data['type'] ?? null);
        $reaction->setUser($user);
        $reaction->setPost($post);
        $reaction->setCreatedAt(new \DateTime());

        $this->entityManager->persist($reaction);
        $this->entityManager->flush();

        $reactionData = [
            'id' => $reaction->getId(),
            'type' => $reaction->getType(),
            'userId' => $reaction->getUser()->getId(),
            'postId' => $reaction->getPost()->getId(),
            'createdAt' => $reaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $reactionData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, ReactionRepository $reactionRepository, int $id): JsonResponse
    {
        $reaction = $reactionRepository->find($id);

        if (!$reaction) {
            return new JsonResponse(['error' => 'Reaction not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $reaction->setType($data['type'] ?? $reaction->getType());
        $this->entityManager->flush();

        $reactionData = [
            'id' => $reaction->getId(),
            'type' => $reaction->getType(),
            'userId' => $reaction->getUser()->getId(),
            'postId' => $reaction->getPost()->getId(),
            'createdAt' => $reaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $reactionData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(ReactionRepository $reactionRepository, int $id): JsonResponse
    {
        $reaction = $reactionRepository->find($id);

        if (!$reaction) {
            return new JsonResponse(['error' => 'Reaction not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($reaction);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Reaction deleted successfully'], Response::HTTP_OK);
    }
}
