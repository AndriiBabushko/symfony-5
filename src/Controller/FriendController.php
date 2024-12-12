<?php

namespace App\Controller;

use App\Repository\FriendRepository;
use App\Services\FriendService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/friends', name: 'friend_')]
class FriendController extends AbstractController
{
    private FriendService $friendService;

    public function __construct(FriendService $friendService)
    {
        $this->friendService = $friendService;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(Request $request, FriendRepository $friendRepository): JsonResponse
    {
        $requestData = $request->query->all();
        $itemsPerPage = isset($requestData['itemsPerPage']) ? (int)$requestData['itemsPerPage'] : 10;
        $page = isset($requestData['page']) ? (int)$requestData['page'] : 1;

        $friendsData = $friendRepository->getAllFriendsByFilter($requestData, $itemsPerPage, $page);

        $friends = array_map([$this->friendService, 'serializeFriend'], $friendsData['friends']);

        return new JsonResponse([
            'data' => $friends,
            'meta' => [
                'totalItems' => $friendsData['totalItems'],
                'totalPageCount' => $friendsData['totalPageCount'],
                'currentPage' => $page,
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $friend = $this->friendService->getFriendById($id);

        return new JsonResponse(['data' => $friend], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $friend = $this->friendService->createFriendship($data);

        return new JsonResponse(['data' => $friend], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $friend = $this->friendService->updateFriendship($id, $data);

        return new JsonResponse(['data' => $friend], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->friendService->deleteFriendship($id);

        return new JsonResponse(['message' => 'Friendship deleted successfully'], Response::HTTP_OK);
    }
}
