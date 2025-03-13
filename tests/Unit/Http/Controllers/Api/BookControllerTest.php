<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\BookController;
use App\Http\Requests\BestSellersRequest;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected $bookService;
    protected $controller;
    protected $request;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->bookService = Mockery::mock(BookService::class);
        $this->request = Mockery::mock(BestSellersRequest::class);
        $this->controller = new BookController($this->bookService);
    }

    /**
     * Test bestSellers method returns successful response.
     */
    public function test_best_sellers_returns_successful_response(): void
    {
        // Mock the request validated data
        $validatedData = ['author' => 'John Doe'];
        $this->request->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        // Mock the BookService response
        $serviceResponse = [
            'num_results' => 2,
            'results' => [
                [
                    'title' => 'Test Book 1',
                    'author' => 'John Doe',
                ],
                [
                    'title' => 'Test Book 2',
                    'author' => 'John Doe',
                ],
            ],
        ];

        $this->bookService->shouldReceive('getBooks')
            ->once()
            ->with($validatedData)
            ->andReturn($serviceResponse);

        // Call the controller method
        $response = $this->controller->bestSellers($this->request);

        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals(2, $responseData['count']);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals('Test Book 1', $responseData['data'][0]['title']);
    }

    /**
     * Test bestSellers method returns error response.
     */
    public function test_best_sellers_returns_error_response(): void
    {
        // Mock the request validated data
        $validatedData = [];
        $this->request->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        // Mock the BookService error response
        $errorResponse = [
            'status' => 'error',
            'message' => 'Failed to fetch data from NYT API',
            'code' => 500,
        ];

        $this->bookService->shouldReceive('getBooks')
            ->once()
            ->with($validatedData)
            ->andReturn($errorResponse);

        // Call the controller method
        $response = $this->controller->bestSellers($this->request);

        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Failed to fetch data from NYT API', $responseData['message']);
    }

    /**
     * Test bestSellers method with empty results.
     */
    public function test_best_sellers_with_empty_results(): void
    {
        // Mock the request validated data
        $validatedData = ['author' => 'Nonexistent Author'];
        $this->request->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        // Mock the BookService response with empty results
        $serviceResponse = [
            'num_results' => 0,
            'results' => [],
        ];

        $this->bookService->shouldReceive('getBooks')
            ->once()
            ->with($validatedData)
            ->andReturn($serviceResponse);

        // Call the controller method
        $response = $this->controller->bestSellers($this->request);

        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals(0, $responseData['count']);
        $this->assertEmpty($responseData['data']);
    }

    /**
     * Test bestSellers method with different query parameters.
     */
    public function test_best_sellers_with_different_query_parameters(): void
    {
        // Test cases with different query parameters
        $testCases = [
            [
                'params' => ['author' => 'John Doe'],
                'results' => 1,
                'title' => 'Book by John Doe',
            ],
            [
                'params' => ['title' => 'Test Book'],
                'results' => 2,
                'title' => 'Test Book 1',
            ],
            [
                'params' => ['isbn' => ['1234567890']],
                'results' => 1,
                'title' => 'Book with ISBN',
            ],
        ];

        foreach ($testCases as $case) {
            // Mock the request validated data
            $validatedData = $case['params'];
            $this->request->shouldReceive('validated')
                ->once()
                ->andReturn($validatedData);

            // Mock the BookService response
            $serviceResponse = [
                'num_results' => $case['results'],
                'results' => array_fill(0, $case['results'], [
                    'title' => $case['title'],
                    'author' => 'Author',
                ]),
            ];

            $this->bookService->shouldReceive('getBooks')
                ->once()
                ->with($validatedData)
                ->andReturn($serviceResponse);

            // Call the controller method
            $response = $this->controller->bestSellers($this->request);

            // Assert the response
            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertEquals(200, $response->getStatusCode());

            $responseData = json_decode($response->getContent(), true);
            $this->assertTrue($responseData['success']);
            $this->assertEquals($case['results'], $responseData['count']);
            $this->assertCount($case['results'], $responseData['data']);

            if ($case['results'] > 0) {
                $this->assertEquals($case['title'], $responseData['data'][0]['title']);
            }
        }
    }

    /**
     * Test bestSellers method with different error codes.
     */
    public function test_best_sellers_with_different_error_codes(): void
    {
        // Test cases with different error codes
        $errorCodes = [400, 401, 403, 404, 500, 503];

        foreach ($errorCodes as $code) {
            // Mock the request validated data
            $validatedData = [];
            $this->request->shouldReceive('validated')
                ->once()
                ->andReturn($validatedData);

            // Mock the BookService error response
            $errorResponse = [
                'status' => 'error',
                'message' => "Error with code $code",
                'code' => $code,
            ];

            $this->bookService->shouldReceive('getBooks')
                ->once()
                ->with($validatedData)
                ->andReturn($errorResponse);

            // Call the controller method
            $response = $this->controller->bestSellers($this->request);

            // Assert the response
            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertEquals($code, $response->getStatusCode());

            $responseData = json_decode($response->getContent(), true);
            $this->assertFalse($responseData['success']);
            $this->assertEquals("Error with code $code", $responseData['message']);
        }
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}