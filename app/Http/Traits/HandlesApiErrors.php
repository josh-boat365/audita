<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;

/**
 * Trait for handling API errors consistently across controllers.
 */
trait HandlesApiErrors
{
    /**
     * Handle API response and return appropriate redirect with message.
     *
     * @param Response $response
     * @param string $successMessage
     * @param string $successRoute
     * @param string $context
     * @return RedirectResponse
     */
    protected function handleApiResponse(
        Response $response,
        string $successMessage,
        string $successRoute,
        string $context = 'API request'
    ): RedirectResponse {
        if ($response->successful()) {
            Log::info("{$context} successful", [
                'status' => $response->status(),
            ]);

            return redirect()->route($successRoute)
                ->with('toast_success', $successMessage);
        }

        // Handle error response
        $errorMessage = $this->extractErrorMessage($response);

        Log::error("{$context} failed", [
            'status' => $response->status(),
            'error' => $errorMessage,
            'response_body' => $response->body(),
        ]);

        return redirect()->back()
            ->with('toast_error', $errorMessage);
    }

    /**
     * Handle API exception and return appropriate redirect with message.
     *
     * @param \Exception $exception
     * @param string $context
     * @param array $additionalData
     * @return RedirectResponse
     */
    protected function handleApiException(
        \Exception $exception,
        string $context = 'API request',
        array $additionalData = []
    ): RedirectResponse {
        // Handle connection exceptions specifically
        if ($exception instanceof ConnectionException) {
            Log::error("Connection error during {$context}", array_merge([
                'error' => $exception->getMessage(),
            ], $additionalData));

            return redirect()->back()
                ->with('toast_error', 'Connection failed. Please check your internet connection and try again.');
        }

        // Handle all other exceptions
        Log::error("Exception during {$context}", array_merge([
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ], $additionalData));

        return redirect()->back()
            ->with('toast_error', 'Something went wrong. Please try again or contact support.');
    }

    /**
     * Extract error message from API response.
     *
     * @param Response $response
     * @return string
     */
    protected function extractErrorMessage(Response $response): string
    {
        $status = $response->status();
        $body = $response->json();

        // Try to get error from response body
        if (isset($body['error'])) {
            return $body['error'];
        }

        if (isset($body['message'])) {
            return $body['message'];
        }

        // Return default message based on status code
        return match ($status) {
            400 => 'Bad request. Please check your input and try again.',
            401 => 'Session expired. Please login again.',
            403 => 'You do not have permission to perform this action.',
            404 => 'The requested resource was not found.',
            422 => 'Validation failed. Please check your input.',
            429 => 'Too many requests. Please try again later.',
            500 => 'Server error occurred. Please try again or contact support.',
            503 => 'Service temporarily unavailable. Please try again later.',
            default => 'An error occurred. Please try again.',
        };
    }

    /**
     * Check if user has valid API token in session.
     *
     * @return bool
     */
    protected function hasValidApiToken(): bool
    {
        return !empty(session('api_token'));
    }

    /**
     * Get API token from session.
     *
     * @return string|null
     */
    protected function getApiToken(): ?string
    {
        return session('api_token');
    }

    /**
     * Redirect to login if token is missing.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function redirectToLoginIfNoToken(string $message = 'Session expired, login to access the application'): RedirectResponse
    {
        if (!$this->hasValidApiToken()) {
            return redirect()->route('login')
                ->with('toast_warning', $message);
        }

        return redirect()->back();
    }

    /**
     * Validate API response and extract data or throw exception.
     *
     * @param Response $response
     * @param string $context
     * @return object|array
     * @throws \RuntimeException
     */
    protected function validateAndExtractData(Response $response, string $context = 'API request')
    {
        if (!$response->successful()) {
            $errorMessage = $this->extractErrorMessage($response);

            Log::error("{$context} failed", [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);

            throw new \RuntimeException($errorMessage, $response->status());
        }

        $data = $response->object() ?? $response->json();

        if (empty($data)) {
            Log::warning("{$context} returned empty data");
        }

        return $data;
    }

    /**
     * Log API request for debugging.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return void
     */
    protected function logApiRequest(string $method, string $endpoint, array $data = []): void
    {
        if (config('app.debug')) {
            Log::debug("API Request", [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
        }
    }

    /**
     * Handle session expiration.
     *
     * @return RedirectResponse
     */
    protected function handleSessionExpired(): RedirectResponse
    {
        session()->flush();

        return redirect()->route('login')
            ->with('toast_warning', 'Your session has expired. Please login again.');
    }
}
