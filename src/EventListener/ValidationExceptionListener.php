<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception->getPrevious() instanceof ValidationFailedException) {
            $previous = $exception->getPrevious();

            $violations = [];

            foreach ($previous->getViolations()->getIterator()->getArrayCopy() as $violation) {
                $key = explode(".", $violation->getMessage())[0];
                $value = explode(".", $violation->getMessage())[1];

                if (isset($violations[$key]) === false) {
                    $violations[$key] = [];
                }

                $violations[$key][] = $value;
            }

            $event->setResponse(new JsonResponse([
                'error' => 'Validation failed',
                'violations' => $violations,
            ], Response::HTTP_BAD_REQUEST));
        }
    }
}
