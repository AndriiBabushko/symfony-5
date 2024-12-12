<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestCheckerService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function check(mixed $content, array $fields): bool
    {
        $errors = [];

        if (!isset($content)) {
            throw new BadRequestException('Empty content', Response::HTTP_BAD_REQUEST);
        }

        foreach ($fields as $field) {
            if (!isset($content[$field])) {
                $errors[] = [
                    'field' => $field,
                    'message' => 'This field is required.',
                ];
            }
        }

        if (!empty($errors)) {
            throw new BadRequestException(
                json_encode(['errors' => $errors]),
                Response::HTTP_BAD_REQUEST
            );
        }

        return true;
    }

    public function validateRequestDataByConstraints(
        array|object $data,
        ?array $constraints = null,
        ?bool $removeSquareBracketFromPropertyPath = false
    ): void {
        $errors = $this->validator->validate($data, $constraints ? new Collection($constraints) : null);

        if (count($errors) === 0) {
            return;
        }

        $validationErrors = [];

        foreach ($errors as $error) {
            $key = $error->getPropertyPath();

            if ($removeSquareBracketFromPropertyPath) {
                $key = preg_replace('/\[.*?\]/', '', $key);
            }

            $validationErrors[] = [
                'field' => str_replace(['[', ']'], '', $key),
                'message' => $error->getMessage(),
            ];
        }

        throw new UnprocessableEntityHttpException(json_encode([
            "errors" => $validationErrors,
        ]));
    }
}
