<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpService
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiKeyParamName;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var int
     */
    protected $timeout = 30;

    /**
     * @var int
     */
    protected $retryTimes = 3;

    /**
     * @var int
     */
    protected $retryMilliseconds = 100;

    /**
     * HttpService constructor.
     *
     * @param string $baseUrl
     * @param string|null $apiKey
     * @param string $apiKeyParamName
     */
    public function __construct(string $baseUrl = '', ?string $apiKey = null, string $apiKeyParamName = 'api-key')
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->apiKeyParamName = $apiKeyParamName;
    }

    /**
     * Set the base URL for requests.
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Set the API key.
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Set headers for the request.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set timeout for the request.
     *
     * @param int $seconds
     * @return $this
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set retry options for the request.
     *
     * @param int $times
     * @param int $milliseconds
     * @return $this
     */
    public function setRetry(int $times, int $milliseconds = 100): self
    {
        $this->retryTimes = $times;
        $this->retryMilliseconds = $milliseconds;
        return $this;
    }

    /**
     * Make a GET request.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return Response
     */
    public function get(string $endpoint, array $queryParams = []): Response
    {
        $url = $this->buildUrl($endpoint);
        $queryParams = $this->attachApiKey($queryParams);
        
        try {
            return $this->buildRequest()
                ->get($url, $queryParams);
        } catch (\Exception $e) {
            Log::error('HTTP GET request failed', [
                'url' => $url,
                'params' => $queryParams,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Make a POST request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $queryParams
     * @return Response
     */
    public function post(string $endpoint, array $data = [], array $queryParams = []): Response
    {
        $url = $this->buildUrl($endpoint);
        $queryParams = $this->attachApiKey($queryParams);
        $data = $this->attachApiKey($data);

        try {
            return $this->buildRequest()
                ->post($url . $this->buildQueryString($queryParams), $data);
        } catch (\Exception $e) {
            Log::error('HTTP POST request failed', [
                'url' => $url,
                'params' => $queryParams,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Make a PUT request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $queryParams
     * @return Response
     */
    public function put(string $endpoint, array $data = [], array $queryParams = []): Response
    {
        $url = $this->buildUrl($endpoint);
        $queryParams = $this->attachApiKey($queryParams);
        $data = $this->attachApiKey($data);

        try {
            return $this->buildRequest()
                ->put($url . $this->buildQueryString($queryParams), $data);
        } catch (\Exception $e) {
            Log::error('HTTP PUT request failed', [
                'url' => $url,
                'params' => $queryParams,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Make a DELETE request.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return Response
     */
    public function delete(string $endpoint, array $queryParams = []): Response
    {
        $url = $this->buildUrl($endpoint);
        $queryParams = $this->attachApiKey($queryParams);

        try {
            return $this->buildRequest()
                ->delete($url, $queryParams);
        } catch (\Exception $e) {
            Log::error('HTTP DELETE request failed', [
                'url' => $url,
                'params' => $queryParams,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build the full URL for the request.
     *
     * @param string $endpoint
     * @return string
     */
    protected function buildUrl(string $endpoint): string
    {
        if (empty($this->baseUrl)) {
            return $endpoint;
        }

        $baseUrl = rtrim($this->baseUrl, '/');
        $endpoint = ltrim($endpoint, '/');

        return "{$baseUrl}/{$endpoint}";
    }

    /**
     * Build the query string for the request.
     *
     * @param array $params
     * @return string
     */
    protected function buildQueryString(array $params): string
    {
        if (empty($params)) {
            return '';
        }

        return '?' . http_build_query($params);
    }

    /**
     * Attach the API key to the parameters.
     *
     * @param array $params
     * @return array
     */
    protected function attachApiKey(array $params): array
    {
        if ($this->apiKey && !isset($params[$this->apiKeyParamName])) {
            $params[$this->apiKeyParamName] = $this->apiKey;
        }

        return $params;
    }

    /**
     * Build the HTTP request with common configurations.
     *
     * @return PendingRequest
     */
    protected function buildRequest(): PendingRequest
    {
        return Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retryMilliseconds);
    }
}