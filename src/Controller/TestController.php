<?php

namespace App\Controller;

use App\Services\RequestCheckerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TestController extends AbstractController
{
    #[Route('/test/exception', name: 'test_exception', methods: ['GET'])]
    public function testException(ValidatorInterface $validator): JsonResponse
    {
        $data = [
            'email' => 'fsadfasdf@gsadf',
        ];

        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                json_encode(['data' => ['errors' => $violations]])
            );
        }

        return new JsonResponse(['message' => 'No exceptions thrown']);
    }

    #[Route('/test/request-check', name: 'test_request_check', methods: ['POST'])]
    public function testRequestCheck(Request $request, RequestCheckerService $checker): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        // Перевірка обов'язкових полів
        $checker->check($content, ['email', 'name']);

        // Перевірка валідації
        $constraints = [
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3, 'max' => 50]),
            ],
        ];
        $checker->validateRequestDataByConstraints($content, $constraints);

        return new JsonResponse(['message' => 'Validation passed']);
    }

}
