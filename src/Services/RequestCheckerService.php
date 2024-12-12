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

    /**
     * Перевірка, чи всі обов'язкові поля присутні в запиті.
     *
     * @param mixed $content
     * @param array $fields
     * @return bool
     */
    public function check(mixed $content, array $fields): bool
    {
        $errors = '';

        if (!isset($content)) {
            throw new BadRequestException('Empty content', Response::HTTP_BAD_REQUEST);
        }

        foreach ($fields as $field) {
            if (!isset($content[$field])) {
                $errors .= $field . '; ';
            }
        }

        if ($errors) {
            throw new BadRequestException(
                'Required fields are missed: ' . rtrim($errors, '; '),
                Response::HTTP_BAD_REQUEST
            );
        }

        return true;
    }

    /**
     * Перевірка даних на відповідність вказаним обмеженням.
     *
     * @param array|object $data
     * @param array|null $constraints
     * @param bool|null $removeSquareBracketFromPropertyPath
     * @return void
     */
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

            $key = str_replace(['[', ']'], '', $key);

            if ($removeSquareBracketFromPropertyPath) {
                $key = preg_replace('/\[.*?\]/', '', $key);
            }

            $validationErrors[] = [
                'field' => $key,
                'message' => $error->getMessage(),
            ];
        }

        throw new UnprocessableEntityHttpException(json_encode([
            "code" => Response::HTTP_UNPROCESSABLE_ENTITY,
            "errors" => $validationErrors,
        ]));
    }


}
