<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'contributor',
        'author',
        'contributor_note',
        'price',
        'publisher',
        'isbns',
        'ranks_history',
        'reviews',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'isbns' => 'array',
        'ranks_history' => 'array',
        'reviews' => 'array',
    ];

    /**
     * Get the book numbers for the book.
     *
     * @return HasMany
     */
    public function bookNumbers(): HasMany
    {
        return $this->hasMany(BookNumber::class);
    }

    /**
     * Add a book number to the book.
     *
     * @param string $key
     * @param string $number
     * @return BookNumber
     */
    public function addBookNumber(string $key, string $number): BookNumber
    {
        return $this->bookNumbers()->create([
            'key' => $key,
            'number' => $number,
        ]);
    }

    /**
     * Get the ranks for the book.
     *
     * @return HasMany
     */
    public function bookRanks(): HasMany
    {
        return $this->hasMany(BookRank::class);
    }

    /**
     * Add a rank to the book.
     *
     * @param array $rankData
     * @return BookRank
     */
    public function addBookRank(array $rankData): BookRank
    {
        return $this->bookRanks()->create($rankData);
    }

    /**
     * Get the reviews for the book.
     *
     * @return HasMany
     */
    public function bookReviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    /**
     * Add a review to the book.
     *
     * @param array $reviewData
     * @return BookReview
     */
    public function addBookReview(array $reviewData): BookReview
    {
        return $this->bookReviews()->create($reviewData);
    }
}