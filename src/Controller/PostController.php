<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Services\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/posts', name: 'post_')]
class PostController extends AbstractController
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(Request $request, PostRepository $postRepository): JsonResponse
    {
        $requestData = $request->query->all();
        $itemsPerPage = isset($requestData['itemsPerPage']) ? (int)$requestData['itemsPerPage'] : 10;
        $page = isset($requestData['page']) ? (int)$requestData['page'] : 1;

        $postsData = $postRepository->getAllPostsByFilter($requestData, $itemsPerPage, $page);

        $posts = array_map([$this->postService, 'serializePost'], $postsData['posts']);

        return new JsonResponse([
            'data' => $posts,
            'meta' => [
                'totalItems' => $postsData['totalItems'],
                'totalPageCount' => $postsData['totalPageCount'],
                'currentPage' => $page,
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        return new JsonResponse(['data' => $post], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $post = $this->postService->createPost($data);

        return new JsonResponse(['data' => $post], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $post = $this->postService->updatePost($id, $data);

        return new JsonResponse(['data' => $post], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->postService->deletePost($id);

        return new JsonResponse(['message' => 'Post deleted successfully'], Response::HTTP_OK);
    }
}
