<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookRank extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'primary_isbn10',
        'primary_isbn13',
        'rank',
        'list_name',
        'display_name',
        'published_date',
        'bestsellers_date',
        'weeks_on_list',
        'ranks_last_week',
        'asterisk',
        'dagger',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_date' => 'date',
        'bestsellers_date' => 'date',
        'weeks_on_list' => 'integer',
        'ranks_last_week' => 'integer',
        'rank' => 'integer',
        'asterisk' => 'boolean',
        'dagger' => 'boolean',
    ];

    /**
     * Get the book that owns the rank.
     *
     * @return BelongsTo
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
