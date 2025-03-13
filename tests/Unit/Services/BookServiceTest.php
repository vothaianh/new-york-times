<?php

namespace Tests\Unit\Services;

use App\Models\Book;
use App\Repositories\BookRepositoryInterface;
use App\Services\BookService;
use App\Services\HttpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class BookServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $httpService;
    protected $repository;
    protected $bookService;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpService = Mockery::mock(HttpService::class);
        $this->repository = Mockery::mock(BookRepositoryInterface::class);

        // Configure the HTTP service mock
        $this->httpService->shouldReceive('setBaseUrl')->andReturnSelf();
        $this->httpService->shouldReceive('setApiKey')->andReturnSelf();

        $this->bookService = new BookService($this->httpService, $this->repository);
    }

    /**
     * Test getting books successfully.
     */
    public function test_get_books_successfully(): void
    {
        // Mock response data
        $responseData = [
            'num_results' => 2,
            'results' => [
                [
                    'title' => 'Test Book 1',
                    'author' => 'Author 1',
                    'isbns' => [
                        ['isbn10' => '1234567890'],
                    ],
                ],
                [
                    'title' => 'Test Book 2',
                    'author' => 'Author 2',
                    'isbns' => [
                        ['isbn13' => '9781234567897'],
                    ],
                ],
            ],
        ];

        // Mock HTTP response
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('failed')->andReturn(false);
        $mockResponse->shouldReceive('json')->andReturn($responseData);

        // Configure HTTP service to return the mock response
        $this->httpService->shouldReceive('get')
            ->once()
            ->with('/books/v3/lists/best-sellers/history.json', Mockery::any())
            ->andReturn($mockResponse);

        // Call the method
        $result = $this->bookService->getBooks([]);

        // Assert the result
        $this->assertEquals($responseData, $result);
    }

    /**
     * Test getting books with API error.
     */
    public function test_get_books_with_api_error(): void
    {
        // Mock HTTP response with error
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('failed')->andReturn(true);
        $mockResponse->shouldReceive('status')->andReturn(500);
        $mockResponse->shouldReceive('body')->andReturn('Internal Server Error');

        // Configure HTTP service to return the mock response
        $this->httpService->shouldReceive('get')
            ->once()
            ->with('/books/v3/lists/best-sellers/history.json', Mockery::any())
            ->andReturn($mockResponse);

        // Call the method
        $result = $this->bookService->getBooks([]);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to fetch data from NYT API', $result['message']);
        $this->assertEquals(500, $result['code']);
    }

    /**
     * Test getting books with exception.
     */
    public function test_get_books_with_exception(): void
    {
        // Configure HTTP service to throw an exception
        $this->httpService->shouldReceive('get')
            ->once()
            ->with('/books/v3/lists/best-sellers/history.json', Mockery::any())
            ->andThrow(new \Exception('Test exception', 400));

        // Call the method
        $result = $this->bookService->getBooks([]);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('An unexpected error occurred', $result['message']);
        $this->assertEquals(400, $result['code']);
    }

    /**
     * Test building query parameters.
     */
    public function test_build_query_params(): void
    {
        // Create a reflection method to access protected method
        $reflectionMethod = new \ReflectionMethod(BookService::class, 'buildQueryParams');
        $reflectionMethod->setAccessible(true);

        // Test with author parameter
        $params = ['author' => 'John Doe'];
        $result = $reflectionMethod->invoke($this->bookService, $params);
        $this->assertEquals('John Doe', $result['author']);

        // Test with title parameter
        $params = ['title' => 'Test Book'];
        $result = $reflectionMethod->invoke($this->bookService, $params);
        $this->assertEquals('Test Book', $result['title']);

        // Test with ISBN parameter
        $params = ['isbn' => ['1234567890', '9781234567897']];
        $result = $reflectionMethod->invoke($this->bookService, $params);
        $this->assertEquals('1234567890;9781234567897', $result['isbn']);

        // Test with offset parameter
        $params = ['offset' => 20];
        $result = $reflectionMethod->invoke($this->bookService, $params);
        $this->assertEquals(20, $result['offset']);
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