<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private PostRepository $postRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllComments(): array
    {
        $comments = $this->commentRepository->findAll();

        return array_map([$this, 'serializeComment'], $comments);
    }

    public function getCommentById(int $id): array
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        return $this->serializeComment($comment);
    }

    public function createComment(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $post = $this->postRepository->find($data['postId']);
        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        $comment = $this->createCommentObject($user, $post, $data['content']);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $this->serializeComment($comment);
    }

    public function updateComment(int $id, array $data): array
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($comment, $method)) {
                $comment->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($comment);

        $this->entityManager->flush();

        return $this->serializeComment($comment);
    }

    public function deleteComment(int $id): void
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }

    private function createCommentObject(User $user, Post $post, string $content): Comment
    {
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPost($post);
        $comment->setContent($content);
        $comment->setCreatedAt(new \DateTime());

        return $comment;
    }

    private function serializeComment(Comment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'userId' => $comment->getUser()->getId(),
            'postId' => $comment->getPost()->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
