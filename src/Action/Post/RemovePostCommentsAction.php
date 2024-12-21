<?php

declare(strict_types=1);

namespace App\Action\Post;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RemovePostCommentsAction
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Post $post): JsonResponse
    {
        if (count($post->getComments()) === 0) {
            return new JsonResponse([
                'message' => 'No comments to delete.',
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($post->getComments() as $comment) {
            $this->entityManager->remove($comment);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'All comments removed successfully!',
            'post' => [
                'id' => $post->getId(),
                'status' => $post->getStatus(),
            ],
        ], Response::HTTP_OK);
    }
}
