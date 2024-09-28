<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/lab1')]
class Lab1Controller extends AbstractController
{
    #[Route('/', name: 'app_lab1_create', methods: ['POST'])]
    public function create(Request $request, SessionInterface $session): Response
    {
        $requestBody = json_decode($request->getContent(), true);
        $id = uniqid();

        $requestBody['id'] = $id;

        $data = $session->get('data', []);

        $data[] = $requestBody;

        $session->set('data', $data);

        return new JsonResponse(['id' => $id, 'data' => $requestBody], Response::HTTP_CREATED);
    }

    #[Route('/', name: 'app_lab1_read', methods: ['GET'])]
    public function read(SessionInterface $session): Response
    {
        $data = $session->get('data', []);

        return new JsonResponse(['data' => array_values($data)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_lab1_read_one', methods: ['GET'])]
    public function readOne(string $id, SessionInterface $session): Response
    {
        $data = $session->get('data', []);

        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return new JsonResponse(['data' => $item], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'app_lab1_update', methods: ['PUT'])]
    public function update(Request $request, string $id, SessionInterface $session): Response
    {
        $data = $session->get('data', []);

        foreach ($data as &$item) {
            if ($item['id'] === $id) {
                $requestBody = json_decode($request->getContent(), true);
                $item = array_merge($item, $requestBody);
                $session->set('data', $data);

                return new JsonResponse(['data' => $item], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'app_lab1_delete', methods: ['DELETE'])]
    public function delete(string $id, SessionInterface $session): Response
    {
        $data = $session->get('data', []);

        foreach ($data as $index => $item) {
            if ($item['id'] === $id) {
                unset($data[$index]);

                $session->set('data', array_values($data));

                return new JsonResponse(['message' => 'Item deleted'], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
    }
}
