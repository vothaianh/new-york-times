<?php

namespace App\Services;

use App\Repositories\BookRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookService
{
    /**
     * @var HttpService
     */
    protected $httpService;

    /**
     * @var BookRepositoryInterface
     */
    protected $repository;

    /**
     * BookService constructor.
     *
     * @param HttpService $httpService
     * @param BookRepositoryInterface $repository
     */
    public function __construct(HttpService $httpService, BookRepositoryInterface $repository)
    {
        $this->httpService = $httpService;
        $this->repository = $repository;

        // Configure the HTTP service with NYT API settings
        $this->httpService
            ->setBaseUrl(config('services.nyt.api_url'))
            ->setApiKey(config('services.nyt.api_key'));
    }

    /**
     * Get NYT Best Sellers list.
     *
     * @param array $params
     * @return array
     */
    public function getBooks(array $params): array
    {
        try {
            // First, try to get data from the database
            // $dbResults = $this->repository->getBooks($params);

            // If we have results in the database, return them
            // if (!empty($dbResults['results'])) {
            //     return $dbResults;
            // }

            // Otherwise, fetch from the NYT API
            $queryParams = $this->buildQueryParams($params);

            $response = $this->httpService->get('/books/v3/lists/best-sellers/history.json', $queryParams);

            if ($response->failed()) {
                Log::error('NYT API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'status' => 'error',
                    'message' => 'Failed to fetch data from NYT API',
                    'code' => $response->status(),
                ];
            }

            $data = $response->json();

            // Store the results in the database for future use
            if (isset($data['results']) && is_array($data['results'])) {
                foreach ($data['results'] as $result) {
                    $this->storeBookData($result);
                }
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Unexpected error in NYT API service', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'code' => $e->getCode(),
            ];
        }
    }

    /**
     * Build query parameters for the NYT API request.
     *
     * @param array $params
     * @return array
     */
    protected function buildQueryParams(array $params): array
    {
        $queryParams = [];

        // Add author parameter if provided
        if (isset($params['author'])) {
            $queryParams['author'] = $params['author'];
        }

        // Add ISBN parameter if provided
        if (isset($params['isbn']) && is_array($params['isbn'])) {
            $queryParams['isbn'] = implode(';', $params['isbn']);
        }

        // Add title parameter if provided
        if (isset($params['title'])) {
            $queryParams['title'] = $params['title'];
        }

        // Add offset parameter if provided
        if (isset($params['offset'])) {
            $queryParams['offset'] = $params['offset'];
        }

        return $queryParams;
    }

    /**
     * Store book data in the database.
     *
     * @param array $data
     * @return void
     */
    protected function storeBookData(array $data): void
    {
        try {
            $bookData = [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'contributor' => $data['contributor'] ?? null,
                'author' => $data['author'] ?? null,
                'contributor_note' => $data['contributor_note'] ?? null,
                'price' => $data['price'] ?? null,
                'publisher' => $data['publisher'] ?? null,
                'isbns' => $data['isbns'] ?? null,
                'ranks_history' => $data['ranks_history'] ?? null,
                'reviews' => $data['reviews'] ?? null,
            ];

            $this->repository->storeBook($bookData);

        } catch (\Exception $e) {
            Log::error('Failed to store book data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Create a new book with its associated book numbers.
     *
     * @param array $data
     * @return Book
     */
    public function createBook(array $data): Book
    {
        try {
            DB::beginTransaction();

            // Extract ISBNs from the data
            $isbns = $data['isbns'] ?? [];

            // Create the book
            $book = Book::create($data);

            DB::commit();

            return $book;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create book', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing book with its associated book numbers.
     *
     * @param Book $book
     * @param array $data
     * @return Book
     */
    public function updateBook(Book $book, array $data): Book
    {
        try {
            DB::beginTransaction();

            // Update the book
            $book->update($data);

            DB::commit();

            return $book;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update book', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'book_id' => $book->id,
                'data' => $data,
            ]);

            throw $e;
        }
    }


    /**
     * Find books by ISBN.
     *
     * @param string $isbn
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBooksByIsbn(string $isbn)
    {
        return Book::whereHas('bookNumbers', function ($query) use ($isbn) {
            $query->where('number', $isbn);
        })->get();
    }
}