<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use JsonSerializable;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    private ?array $_api_defaultSuccessData = ['success' => true];

    public function respondNotFound(
        string|\Exception $message,
        ?string $key = 'error'
    ): JsonResponse {
        return $this->apiResponse(
            [$key => $this->morphMessage($message)],
            Response::HTTP_NOT_FOUND
        );
    }

    public function respondWithSuccess(
        array|Arrayable|JsonSerializable|null $contents = null
    ): JsonResponse {
        $contents = $this->morphToArray($contents) ?? [];

        $data = [] === $contents
            ? $this->_api_defaultSuccessData
            : $contents;

        return $this->apiResponse($data);
    }

    public function setDefaultSuccessResponse(?array $content = null): self
    {
        $this->_api_defaultSuccessData = $content ?? [];
        return $this;
    }

    public function respondOk(string $message): JsonResponse
    {
        return $this->respondWithSuccess(['success' => $message]);
    }

    public function respondUnAuthenticated(?string $message = null): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message ?? 'Unauthenticated'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function respondForbidden(?string $message = null): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message ?? 'Forbidden'],
            Response::HTTP_FORBIDDEN
        );
    }

    public function respondError(?string $message = null): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message ?? 'Error'],
            Response::HTTP_BAD_REQUEST
        );
    }

    public function respondCreated(
        array|Arrayable|JsonSerializable|null $data = null
    ): JsonResponse {
        $data ??= [];
        return $this->apiResponse(
            $this->morphToArray($data),
            Response::HTTP_CREATED
        );
    }

    public function respondFailedValidation(
        string|\Exception $message,
        ?string $key = 'message'
    ): JsonResponse {
        return $this->apiResponse(
            [$key => $this->morphMessage($message)],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function respondTeapot(): JsonResponse
    {
        return $this->apiResponse(
            ['message' => 'I\'m a teapot'],
            Response::HTTP_I_AM_A_TEAPOT
        );
    }

    public function respondNoContent(
        array|Arrayable|JsonSerializable|null $data = null
    ): JsonResponse {
        $data ??= [];
        $data = $this->morphToArray($data);

        return $this->apiResponse($data, Response::HTTP_NO_CONTENT);
    }

    private function apiResponse(array $data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    private function morphToArray(array|Arrayable|JsonSerializable|null $data): ?array
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        return $data;
    }

    private function morphMessage(string|\Exception $message): string
    {
        return $message instanceof Exception
            ? $message->getMessage()
            : $message;
    }
}
