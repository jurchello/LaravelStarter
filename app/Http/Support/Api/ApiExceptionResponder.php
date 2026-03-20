<?php

declare(strict_types=1);

namespace App\Http\Support\Api;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;
use App\Application\Shared\Exceptions\ApplicationValidationException;
use App\Http\Resources\Api\ApiEnvelopeResource;
use App\Http\Resources\Api\SiteApiErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final class ApiExceptionResponder
{
    public function __construct(
        private readonly ApiRequestClassifier $classifier,
    ) {}

    public function toResponse(Throwable $exception, Request $request): ?JsonResponse
    {
        if ($this->classifier->isAdmin($request)) {
            return $this->toAdminResponse($exception, $request);
        }

        if ($this->classifier->isSite($request)) {
            return $this->toSiteResponse($exception, $request);
        }

        return null;
    }

    private function toAdminResponse(Throwable $exception, Request $request): JsonResponse
    {
        $status = $this->resolveStatus($exception);
        $errors = $this->resolveAdminErrors($exception, $status);

        return (new ApiEnvelopeResource(
            data: null,
            meta: [],
            errors: $errors,
        ))->response($request)->setStatusCode($status);
    }

    private function toSiteResponse(Throwable $exception, Request $request): JsonResponse
    {
        $status = $this->resolveStatus($exception);
        $payload = [
            'message' => $this->resolveSiteMessage($exception, $status),
        ];

        if ($exception instanceof ValidationException) {
            $payload['errors'] = $exception->errors();
        }

        return (new SiteApiErrorResource($payload))
            ->response($request)
            ->setStatusCode($status);
    }

    private function resolveStatus(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof ValidationException => 422,
            $exception instanceof ApplicationValidationException => 422,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof TooManyRequestsHttpException => 429,
            $exception instanceof ApplicationNotFoundException => 404,
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof NotFoundHttpException => 404,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => 500,
        };
    }

    /**
     * @return array<int, string>
     */
    private function resolveAdminErrors(Throwable $exception, int $status): array
    {
        if ($exception instanceof ValidationException) {
            return array_values($exception->validator->errors()->all());
        }

        if ($exception instanceof ApplicationValidationException) {
            return [$exception->getMessage()];
        }

        return [$this->resolveDefaultMessage($status)];
    }

    private function resolveSiteMessage(Throwable $exception, int $status): string
    {
        if ($exception instanceof ValidationException) {
            return $exception->getMessage();
        }

        return $this->resolveDefaultMessage($status);
    }

    private function resolveDefaultMessage(int $status): string
    {
        return match ($status) {
            401 => 'Unauthorized.',
            403 => 'Forbidden.',
            404 => 'Resource not found.',
            429 => 'Too many requests.',
            default => 'An unexpected error occurred.',
        };
    }
}
