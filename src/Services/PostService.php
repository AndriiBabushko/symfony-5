<?php

namespace App\Services;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private PostRepository $postRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        PostRepository $postRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllPosts(): array
    {
        $posts = $this->postRepository->findAll();
        return array_map([$this, 'serializePost'], $posts);
    }

    public function getPostById(int $id): array
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        return $this->serializePost($post);
    }

    public function createPost(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $post = new Post();
        $post->setContent($data['content']);
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->serializePost($post);
    }

    public function updatePost(int $id, array $data): array
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($post, $method)) {
                $post->$method($value);
            }
        }

        $post->setUpdatedAt(new \DateTime());

        $this->requestCheckerService->validateRequestDataByConstraints($post);

        $this->entityManager->flush();

        return $this->serializePost($post);
    }

    public function deletePost(int $id): void
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function serializePost(Post $post): array
    {
        return [
            'id' => $post->getId(),
            'content' => $post->getContent(),
            'userId' => $post->getUser()->getId(),
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $post->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
