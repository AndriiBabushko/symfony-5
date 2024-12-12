<?php

namespace App\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class DTOValidationService
{
    private static ?ValidatorInterface $validator = null;

    public function __construct(ValidatorInterface $validator)
    {
        self::$validator = $validator;
    }

    /**
     * Method to validate a single field of any DTO.
     *
     * @param object $dto
     * @param string $field
     * @param mixed $value
     * @throws ValidatorException
     */
    public static function validateField(object $dto, string $field, mixed $value): void
    {
        if (self::$validator === null) {
            throw new \LogicException('Validator is not set.');
        }

        $errors = self::$validator->validatePropertyValue($dto, $field, $value);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new ValidatorException(implode(', ', $messages));
        }
    }
}
