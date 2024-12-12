<?php

namespace App\Services;

use App\Entity\Post;
use App\Entity\Reaction;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\ReactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReactionService
{
    private EntityManagerInterface $entityManager;
    private ReactionRepository $reactionRepository;
    private UserRepository $userRepository;
    private PostRepository $postRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReactionRepository $reactionRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->reactionRepository = $reactionRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllReactions(): array
    {
        $reactions = $this->reactionRepository->findAll();
        return array_map([$this, 'serializeReaction'], $reactions);
    }

    public function getReactionById(int $id): array
    {
        $reaction = $this->reactionRepository->find($id);

        if (!$reaction) {
            throw new NotFoundHttpException('Reaction not found');
        }

        return $this->serializeReaction($reaction);
    }

    public function createReaction(array $data): array
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

        $reaction = $this->createReactionObject($user, $post, $data['type']);

        $this->entityManager->persist($reaction);
        $this->entityManager->flush();

        return $this->serializeReaction($reaction);
    }

    public function updateReaction(int $id, array $data): array
    {
        $reaction = $this->reactionRepository->find($id);

        if (!$reaction) {
            throw new NotFoundHttpException('Reaction not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($reaction, $method)) {
                $reaction->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($reaction);

        $this->entityManager->flush();

        return $this->serializeReaction($reaction);
    }

    public function deleteReaction(int $id): void
    {
        $reaction = $this->reactionRepository->find($id);

        if (!$reaction) {
            throw new NotFoundHttpException('Reaction not found');
        }

        $this->entityManager->remove($reaction);
        $this->entityManager->flush();
    }

    private function createReactionObject(User $user, Post $post, string $type): Reaction
    {
        $reaction = new Reaction();
        $reaction->setUser($user);
        $reaction->setPost($post);
        $reaction->setType($type);
        $reaction->setCreatedAt(new \DateTime());

        return $reaction;
    }

    private function serializeReaction(Reaction $reaction): array
    {
        return [
            'id' => $reaction->getId(),
            'type' => $reaction->getType(),
            'userId' => $reaction->getUser()->getId(),
            'postId' => $reaction->getPost()->getId(),
            'createdAt' => $reaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
