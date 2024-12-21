<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

class RuntimeConstraintExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $code = $this->getCode($exception);
        $errors = $this->getErrors($exception);

        $event->setResponse(new JsonResponse([
            "data" => [
                "code" => $code,
                "errors" => $errors,
            ]
        ], $code));
    }

    /**
     * @param Throwable $exception
     * @return int
     */
    private function getCode(Throwable $exception): int
    {
        if (method_exists($exception, "getStatusCode")) {
            return array_key_exists($exception->getStatusCode(), Response::$statusTexts)
                ? $exception->getStatusCode()
                : Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return array_key_exists($exception->getCode(), Response::$statusTexts)
            ? $exception->getCode()
            : Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * @param Throwable $exception
     * @return array
     */
    private function getErrors(Throwable $exception): array
    {
        if (method_exists($exception, "getConstraintViolationList")) {
            return $this->getAssociativeErrorsForConstraintViolationList($exception->getConstraintViolationList());
        }

        if ($tmpErrors = json_decode($exception->getMessage(), true)) {
            return $tmpErrors['errors'] ?? [['message' => $exception->getMessage()]];
        }

        return [['message' => $exception->getMessage()]];
    }

    /**
     * @param ConstraintViolationList $list
     * @return array
     */
    private function getAssociativeErrorsForConstraintViolationList(ConstraintViolationList $list): array
    {
        $errors = [];
        foreach ($list as $error) {
            $errors[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $errors;
    }
}
