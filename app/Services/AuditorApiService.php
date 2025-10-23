<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

/**
 * Centralized service for communicating with the Auditor .NET API backend.
 *
 * This service handles all HTTP communication with the external API,
 * including authentication, error handling, retries, and logging.
 */
class AuditorApiService
{
    protected string $baseUrl;
    protected bool $verifySSL;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retryDelay;
    protected array $endpoints;

    public function __construct()
    {
        $this->baseUrl = config('services.auditor_api.base_url');
        $this->verifySSL = config('services.auditor_api.verify_ssl');
        $this->timeout = config('services.auditor_api.timeout');
        $this->retryTimes = config('services.auditor_api.retry_times');
        $this->retryDelay = config('services.auditor_api.retry_delay');
        $this->endpoints = config('services.auditor_api.endpoints');
    }

    /**
     * Authenticate user and get access token.
     *
     * @param string $username
     * @param string $password
     * @param bool $validateAppAccess
     * @return array{success: bool, data: object|null, error: string|null}
     */
    public function login(string $username, string $password, bool $validateAppAccess = true): array
    {
        $data = [
            'appName' => 'Auditor',
            'user' => $username,
            'password' => $password,
            'validateAppAcess' => $validateAppAccess,
        ];

        try {
            $response = $this->request('POST', $this->endpoints['login'], $data, null, false);

            if ($response->successful() && isset($response['access_token'])) {
                Log::info('User authenticated successfully', ['user' => $username]);

                return [
                    'success' => true,
                    'data' => $response->object(),
                    'error' => null,
                ];
            }

            $errorMessage = $response->json()['error'] ?? 'Invalid credentials. Please try again.';
            Log::warning('Authentication failed', ['user' => $username, 'error' => $errorMessage]);

            return [
                'success' => false,
                'data' => null,
                'error' => $errorMessage,
            ];
        } catch (ConnectionException $e) {
            Log::error('Connection error during authentication', [
                'user' => $username,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => 'Connection failed. Please check your internet connection.',
            ];
        } catch (\Exception $e) {
            Log::error('Exception during authentication', [
                'user' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => 'An error occurred. Please try again later.',
            ];
        }
    }

    /**
     * Perform a GET request to the API.
     *
     * @param string $endpoint
     * @param string|null $token
     * @param bool $useRateLimit
     * @param string|null $rateLimitKey
     * @return Response
     * @throws RequestException
     */
    public function get(string $endpoint, ?string $token = null, bool $useRateLimit = false, ?string $rateLimitKey = null): Response
    {
        return $this->request('GET', $endpoint, [], $token, $useRateLimit, $rateLimitKey);
    }

    /**
     * Perform a POST request to the API.
     *
     * @param string $endpoint
     * @param array $data
     * @param string|null $token
     * @param bool $useRateLimit
     * @param string|null $rateLimitKey
     * @return Response
     * @throws RequestException
     */
    public function post(string $endpoint, array $data, ?string $token = null, bool $useRateLimit = false, ?string $rateLimitKey = null): Response
    {
        return $this->request('POST', $endpoint, $data, $token, $useRateLimit, $rateLimitKey);
    }

    /**
     * Perform a PUT request to the API.
     *
     * @param string $endpoint
     * @param array $data
     * @param string|null $token
     * @param bool $useRateLimit
     * @param string|null $rateLimitKey
     * @return Response
     * @throws RequestException
     */
    public function put(string $endpoint, array $data, ?string $token = null, bool $useRateLimit = false, ?string $rateLimitKey = null): Response
    {
        return $this->request('PUT', $endpoint, $data, $token, $useRateLimit, $rateLimitKey);
    }

    /**
     * Perform a DELETE request to the API.
     *
     * @param string $endpoint
     * @param string|null $token
     * @param bool $useRateLimit
     * @param string|null $rateLimitKey
     * @return Response
     * @throws RequestException
     */
    public function delete(string $endpoint, ?string $token = null, bool $useRateLimit = false, ?string $rateLimitKey = null): Response
    {
        return $this->request('DELETE', $endpoint, [], $token, $useRateLimit, $rateLimitKey);
    }

    /**
     * Build full URL from endpoint.
     *
     * @param string $endpoint
     * @return string
     */
    public function buildUrl(string $endpoint): string
    {
        // If endpoint starts with /, remove it to avoid double slashes
        $endpoint = ltrim($endpoint, '/');
        return "{$this->baseUrl}/{$endpoint}";
    }

    /**
     * Get a named endpoint from configuration.
     *
     * @param string $name
     * @return string
     */
    public function getEndpoint(string $name): string
    {
        return $this->endpoints[$name] ?? $name;
    }

    /**
     * Core request method that handles all HTTP communication.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param string|null $token
     * @param bool $useRateLimit
     * @param string|null $rateLimitKey
     * @return Response
     * @throws RequestException
     */
    protected function request(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $token = null,
        bool $useRateLimit = false,
        ?string $rateLimitKey = null
    ): Response {
        $url = $this->buildUrl($endpoint);

        // Prepare HTTP client
        $http = Http::timeout($this->timeout);

        if (!$this->verifySSL) {
            $http = $http->withoutVerifying();
        }

        if ($token) {
            $http = $http->withToken($token);
        }

        // Add retry logic
        $http = $http->retry(
            $this->retryTimes,
            $this->retryDelay,
            function ($exception, $request) {
                // Only retry on connection exceptions
                return $exception instanceof ConnectionException;
            },
            false // Don't throw on final failure
        );

        // Execute request (with or without rate limiting)
        if ($useRateLimit && $rateLimitKey) {
            return RateLimiter::attempt(
                $rateLimitKey,
                5,
                function () use ($http, $method, $url, $data) {
                    return $this->executeRequest($http, $method, $url, $data);
                },
                60
            ) ?: $this->executeRequest($http, $method, $url, $data);
        }

        return $this->executeRequest($http, $method, $url, $data);
    }

    /**
     * Execute the actual HTTP request.
     *
     * @param \Illuminate\Http\Client\PendingRequest $http
     * @param string $method
     * @param string $url
     * @param array $data
     * @return Response
     */
    protected function executeRequest($http, string $method, string $url, array $data): Response
    {
        $startTime = microtime(true);

        try {
            $response = match (strtoupper($method)) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Log successful requests
            Log::info("API Request: {$method} {$url}", [
                'status' => $response->status(),
                'duration_ms' => $duration,
            ]);

            return $response;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::error("API Request Failed: {$method} {$url}", [
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
            ]);

            throw $e;
        }
    }

    /**
     * Handle API response and extract data.
     *
     * @param Response $response
     * @param string $context
     * @return array{success: bool, data: mixed, error: string|null, status: int}
     */
    public function handleResponse(Response $response, string $context = 'API request'): array
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->object() ?? $response->json(),
                'error' => null,
                'status' => $response->status(),
            ];
        }

        // Handle different error status codes
        $status = $response->status();
        $error = $this->getErrorMessage($response, $status);

        Log::error("{$context} failed", [
            'status' => $status,
            'error' => $error,
            'response' => $response->body(),
        ]);

        return [
            'success' => false,
            'data' => null,
            'error' => $error,
            'status' => $status,
        ];
    }

    /**
     * Extract error message from response.
     *
     * @param Response $response
     * @param int $status
     * @return string
     */
    protected function getErrorMessage(Response $response, int $status): string
    {
        // Try to get error from response body
        $body = $response->json();
        if (isset($body['error'])) {
            return $body['error'];
        }

        if (isset($body['message'])) {
            return $body['message'];
        }

        // Default messages based on status code
        return match ($status) {
            400 => 'Bad request. Please check your input.',
            401 => 'Unauthorized. Please login again.',
            403 => 'Forbidden. You do not have permission to perform this action.',
            404 => 'Resource not found.',
            422 => 'Validation failed. Please check your input.',
            429 => 'Too many requests. Please try again later.',
            500 => 'Server error. Please try again later or contact support.',
            503 => 'Service temporarily unavailable. Please try again later.',
            default => 'An error occurred. Please try again.',
        };
    }

    /**
     * Check if session has valid API token.
     *
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return !empty(session('api_token'));
    }

    /**
     * Get API token from session.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return session('api_token');
    }

    /**
     * Set API token in session.
     *
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void
    {
        session(['api_token' => $token]);
    }

    /**
     * Clear API token from session.
     *
     * @return void
     */
    public function clearToken(): void
    {
        session()->forget('api_token');
    }
}
