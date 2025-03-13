<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\BookNumber;
use App\Models\BookRank;
use App\Models\BookReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a book.
     */
    public function test_create_book(): void
    {
        $book = Book::create([
            'title' => 'Test Book',
            'description' => 'Test Description',
            'contributor' => 'Test Contributor',
            'author' => 'Test Author',
            'contributor_note' => 'Test Note',
            'price' => 19.99,
            'publisher' => 'Test Publisher',
        ]);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('Test Book', $book->title);
        $this->assertEquals('Test Author', $book->author);
        $this->assertEquals(19.99, $book->price);
    }

    /**
     * Test book with array attributes.
     */
    public function test_book_with_array_attributes(): void
    {
        $book = Book::create([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbns' => [
                ['isbn10' => '1234567890'],
                ['isbn13' => '9781234567897'],
            ],
            'ranks_history' => [
                [
                    'rank' => 1,
                    'list_name' => 'Test List',
                    'published_date' => '2023-01-01',
                ],
            ],
            'reviews' => [
                [
                    'book_review_link' => 'https://example.com/review',
                    'first_chapter_link' => 'https://example.com/chapter',
                ],
            ],
        ]);

        $this->assertIsArray($book->isbns);
        $this->assertCount(2, $book->isbns);
        $this->assertEquals('1234567890', $book->isbns[0]['isbn10']);

        $this->assertIsArray($book->ranks_history);
        $this->assertCount(1, $book->ranks_history);
        $this->assertEquals(1, $book->ranks_history[0]['rank']);

        $this->assertIsArray($book->reviews);
        $this->assertCount(1, $book->reviews);
        $this->assertEquals('https://example.com/review', $book->reviews[0]['book_review_link']);
    }

    /**
     * Test book relationship methods exist.
     */
    public function test_book_relationship_methods_exist(): void
    {
        $book = new Book();

        // Test that the relationship methods exist
        $this->assertTrue(method_exists($book, 'bookNumbers'));
        $this->assertTrue(method_exists($book, 'bookRanks'));
        $this->assertTrue(method_exists($book, 'bookReviews'));

        // Test that the helper methods exist
        $this->assertTrue(method_exists($book, 'addBookNumber'));
        $this->assertTrue(method_exists($book, 'addBookRank'));
        $this->assertTrue(method_exists($book, 'addBookReview'));
    }
}