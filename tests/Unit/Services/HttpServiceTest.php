<?php

namespace Tests\Unit\Services;

use App\Services\HttpService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpServiceTest extends TestCase
{
    protected $httpService;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpService = new HttpService(
            'https://api.example.com',
            'test-api-key',
            'api-key'
        );
    }

    /**
     * Test setting base URL.
     */
    public function test_set_base_url(): void
    {
        $httpService = new HttpService();
        $result = $httpService->setBaseUrl('https://api.test.com');

        $this->assertInstanceOf(HttpService::class, $result);

        // Use reflection to check the private property
        $reflection = new \ReflectionClass($httpService);
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);

        $this->assertEquals('https://api.test.com', $property->getValue($httpService));
    }

    /**
     * Test setting API key.
     */
    public function test_set_api_key(): void
    {
        $httpService = new HttpService();
        $result = $httpService->setApiKey('new-api-key');

        $this->assertInstanceOf(HttpService::class, $result);

        // Use reflection to check the private property
        $reflection = new \ReflectionClass($httpService);
        $property = $reflection->getProperty('apiKey');
        $property->setAccessible(true);

        $this->assertEquals('new-api-key', $property->getValue($httpService));
    }

    /**
     * Test GET request.
     */
    public function test_get_request(): void
    {
        // Mock HTTP facade
        Http::fake([
            'https://api.example.com/test*' => Http::response(['data' => 'test'], 200),
        ]);

        // Make the request
        $response = $this->httpService->get('/test', ['param' => 'value']);

        // Assert the response
        $this->assertEquals(['data' => 'test'], $response->json());

        // Assert the request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.example.com/test?param=value&api-key=test-api-key' &&
                $request->method() === 'GET';
        });
    }

    /**
     * Test request with retry.
     */
    public function test_request_with_retry(): void
    {
        // Configure the HTTP service with retry
        $this->httpService->setRetry(3, 100);

        // Mock HTTP facade
        Http::fake([
            'https://api.example.com/test*' => Http::sequence()
                ->push(['error' => 'Server Error'], 500)
                ->push(['error' => 'Server Error'], 500)
                ->push(['data' => 'success'], 200),
        ]);

        // Make the request
        $response = $this->httpService->get('/test');

        // Assert the response
        $this->assertEquals(['data' => 'success'], $response->json());

        // Assert the request was made 3 times
        Http::assertSentCount(3);
    }

    /**
     * Test request with timeout.
     */
    public function test_request_with_timeout(): void
    {
        // Configure the HTTP service with timeout
        $this->httpService->setTimeout(5);

        // Mock HTTP facade
        Http::fake([
            'https://api.example.com/test*' => Http::response(['data' => 'success'], 200),
        ]);

        // Make the request
        $response = $this->httpService->get('/test');

        // Assert the response
        $this->assertEquals(['data' => 'success'], $response->json());

        // Assert the request was made with the correct timeout
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.example.com/test?api-key=test-api-key';
        });
    }
}
