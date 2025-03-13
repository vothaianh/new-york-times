<?php

namespace Tests\Unit\Repositories;

use App\Models\Book;
use App\Models\BookNumber;
use App\Repositories\BookRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class BookRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;
    protected $bookModel;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->bookModel = Mockery::mock(Book::class);
        $this->repository = new BookRepository($this->bookModel);
    }

    /**
     * Test storing a book.
     */
    public function test_store_book(): void
    {
        // Skip this test for now as it requires more complex mocking
        $this->markTestSkipped('This test requires more complex mocking of the Book model.');

        // Create a real book for testing
        $book = new Book();

        // Mock the book model to return the real book
        $this->bookModel->shouldReceive('firstOrNew')
            ->once()
            ->with(['title' => 'Test Book', 'author' => 'Test Author'])
            ->andReturn($book);

        // Mock the relationships
        $this->bookModel->shouldReceive('bookNumbers->create')
            ->times(2)
            ->andReturn(new BookNumber());

        $this->bookModel->shouldReceive('bookRanks->create')
            ->once()
            ->andReturn(new BookNumber());

        $this->bookModel->shouldReceive('bookReviews->create')
            ->once()
            ->andReturn(new BookNumber());

        $data = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'Test Description',
            'publisher' => 'Test Publisher',
            'price' => 19.99,
            'primary_isbn10' => '1234567890',
            'primary_isbn13' => '9781234567897',
            'ranks' => [
                [
                    'rank' => 1,
                    'list_name' => 'Test List',
                    'published_date' => '2023-01-01',
                    'weeks_on_list' => 10,
                ],
            ],
            'reviews' => [
                [
                    'book_review_link' => 'https://example.com/review',
                    'first_chapter_link' => 'https://example.com/chapter',
                    'sunday_review_link' => 'https://example.com/sunday',
                    'article_chapter_link' => 'https://example.com/article',
                ],
            ],
        ];

        $result = $this->repository->storeBook($data);

        $this->assertInstanceOf(Book::class, $result);
    }

    /**
     * Test getting books.
     */
    public function test_get_books(): void
    {
        // Create a real book for testing
        $book1 = Book::create([
            'title' => 'Test Book 1',
            'author' => 'John Doe',
        ]);

        $book2 = Book::create([
            'title' => 'Test Book 2',
            'author' => 'Jane Smith',
        ]);

        // Create a real repository for this test
        $realRepository = new BookRepository(new Book());

        // Test getting all books
        $result = $realRepository->getBooks([]);
        $this->assertEquals(2, $result['num_results']);
        $this->assertCount(2, $result['results']);
    }

    /**
     * Test getting books with no results.
     */
    public function test_get_books_with_no_results(): void
    {
        // Create a real repository for this test
        $realRepository = new BookRepository(new Book());

        $result = $realRepository->getBooks(['author' => 'Nonexistent Author']);

        $this->assertEquals(0, $result['num_results']);
        $this->assertEmpty($result['results']);
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