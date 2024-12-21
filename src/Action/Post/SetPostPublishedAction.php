<?php

declare(strict_types=1);

namespace App\Action\Post;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetPostPublishedAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/posts/publish', name: 'set_post_published', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'])) {
            return new JsonResponse([
                'message' => 'The "id" field is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $post = $this->entityManager->getRepository(Post::class)->find($data['id']);

        if (!$post) {
            return new JsonResponse([
                'message' => 'Post not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($post->getStatus() === 'published') {
            return new JsonResponse([
                'message' => 'This post is already published.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $post->setStatus('published');

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Post published successfully!',
            'post' => [
                'id' => $post->getId(),
                'status' => $post->getStatus(),
            ],
        ], Response::HTTP_OK);
    }
}
