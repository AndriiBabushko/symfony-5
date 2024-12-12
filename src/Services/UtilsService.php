<?php

namespace App\Services;

use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class UtilsService
{
    /**
     * @param mixed $content
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public static function checkRequestBody(mixed $content, array $fields): bool
    {
        $errors = '';

        if (!isset($content)) {
            throw new RuntimeException('Empty content', Response::HTTP_BAD_REQUEST);
        }

        foreach ($fields as $field) {
            if (!isset($content[$field])) {
                $errors = $errors . ' ' . $field . ';';
            }
        }

        if ($errors) {
            throw new RuntimeException('Required fields are missed:' . $errors, Response::HTTP_BAD_REQUEST);
        }

        return true;
    }
}