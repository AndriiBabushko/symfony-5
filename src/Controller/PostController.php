<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/posts', name: 'post_')]
class PostController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAll();

        $postData = array_map(function (Post $post) {
            return [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            ];
        }, $posts);

        return new JsonResponse(['data' => $postData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(PostRepository $postRepository, int $id): JsonResponse
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $postData = [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ];

        return new JsonResponse(['data' => $postData], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->find($data['userId'] ?? null);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $post = new Post();
        $post->setContent($data['content'] ?? null);
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $postData = [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'userId' => $post->getUser()->getId(),
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $postData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, PostRepository $postRepository, int $id): JsonResponse
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $post->setContent($data['content'] ?? $post->getContent());
        $post->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        $postData = [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ];

        return new JsonResponse(['data' => $postData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(PostRepository $postRepository, int $id): JsonResponse
    {
        $post = $postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Post deleted successfully'], Response::HTTP_OK);
    }
}
