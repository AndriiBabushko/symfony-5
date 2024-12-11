<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\PostRepository;
use App\Entity\Comment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/comments', name: 'comment_')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findAll();

        $commentData = array_map(function (Comment $comment) {
            return [
                'id' => $comment->getId(),
                'userId' => $comment->getUser()->getId(),
                'postId' => $comment->getPost()->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $comments);

        return new JsonResponse(['data' => $commentData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $commentData = [
            'id' => $comment->getId(),
            'userId' => $comment->getUser()->getId(),
            'postId' => $comment->getPost()->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $commentData], Response::HTTP_OK);
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

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPost($post);
        $comment->setContent($data['content'] ?? null);
        $comment->setCreatedAt(new DateTime());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $commentData = [
            'id' => $comment->getId(),
            'userId' => $comment->getUser()->getId(),
            'postId' => $comment->getPost()->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $commentData], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $comment->setContent($data['content'] ?? $comment->getContent());

        $this->entityManager->flush();

        $commentData = [
            'id' => $comment->getId(),
            'userId' => $comment->getUser()->getId(),
            'postId' => $comment->getPost()->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['data' => $commentData], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CommentRepository $commentRepository, int $id): JsonResponse
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Comment deleted successfully'], Response::HTTP_OK);
    }
}
