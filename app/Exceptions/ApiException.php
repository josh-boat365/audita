<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Custom exception for API-related errors.
 */
class ApiException extends Exception
{
    protected int $statusCode;
    protected ?string $apiError;

    public function __construct(
        string $message = 'API request failed',
        int $statusCode = 500,
        ?string $apiError = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->apiError = $apiError;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the API error message.
     *
     * @return string|null
     */
    public function getApiError(): ?string
    {
        return $this->apiError;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function render($request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error' => $this->apiError,
                'status' => $this->statusCode,
            ], $this->statusCode);
        }

        return redirect()->back()
            ->with('toast_error', $this->getMessage());
    }
}
