<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookRepository implements BookRepositoryInterface
{
    /**
     * @var Book
     */
    protected $model;

    /**
     * BookRepository constructor.
     *
     * @param Book $model
     */
    public function __construct(Book $model)
    {
        $this->model = $model;
    }

    /**
     * Get all best sellers.
     *
     * @param array $params
     * @return array
     */
    public function getBooks(array $params): array
    {
        $query = $this->model->query();

        // Filter by author if provided
        if (isset($params['author'])) {
            $query->where('author', 'like', '%' . $params['author'] . '%');
        }

        // Filter by title if provided
        if (isset($params['title'])) {
            $query->where('title', 'like', '%' . $params['title'] . '%');
        }

        // Filter by ISBN if provided
        if (isset($params['isbn']) && is_array($params['isbn'])) {
            $query->whereHas('bookNumbers', function ($q) use ($params) {
                $q->whereIn('number', $params['isbn']);
            });
        }

        // Apply offset if provided
        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $query->offset($params['offset']);
        }

        // Limit results to 20 by default
        $query->limit(20);

        return [
            'status' => 'OK',
            'copyright' => 'Copyright (c) ' . date('Y') . ' The New York Times Company. All Rights Reserved.',
            'num_results' => $query->count(),
            'results' => $query->get()->toArray(),
        ];
    }

    /**
     * Store book data.
     *
     * @param array $data
     * @return mixed
     */
    public function storeBook(array $data)
    {
        try {
            DB::beginTransaction();

            // Extract ISBNs from the data
            $isbns = $data['isbns'] ?? [];

            // Extract ranks history from the data
            $ranksHistory = $data['ranks_history'] ?? [];

            // Extract reviews from the data
            $reviews = $data['reviews'] ?? [];

            // Create the book
            $book = $this->model->create($data);

            // Add book numbers for ISBNs
            if (!empty($isbns)) {
                foreach ($isbns as $isbn) {
                    if (isset($isbn['isbn10'])) {
                        $book->addBookNumber('isbn10', $isbn['isbn10']);
                    }

                    if (isset($isbn['isbn13'])) {
                        $book->addBookNumber('isbn13', $isbn['isbn13']);
                    }
                }
            }

            // Add book ranks from ranks history
            if (!empty($ranksHistory)) {
                foreach ($ranksHistory as $rank) {
                    $rankData = [
                        'rank' => $rank['rank'] ?? null,
                        'list_name' => $rank['list_name'] ?? null,
                        'published_date' => $rank['published_date'] ?? null,
                        'bestsellers_date' => $rank['bestsellers_date'] ?? null,
                        'weeks_on_list' => $rank['weeks_on_list'] ?? null,
                    ];

                    $book->addBookRank($rankData);
                }
            }

            // Add book reviews
            if (!empty($reviews)) {
                foreach ($reviews as $review) {
                    $reviewData = [
                        'source' => $review['source'] ?? null,
                        'summary' => $review['summary'] ?? null,
                        'url' => $review['url'] ?? null,
                        'publication_date' => $review['publication_date'] ?? null,
                        'byline' => $review['byline'] ?? null,
                    ];

                    $book->addBookReview($reviewData);
                }
            }

            DB::commit();
            
            return $book;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}