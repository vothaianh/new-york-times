<?php

namespace App\Repositories;

interface BookRepositoryInterface
{
    /**
     * Get all best sellers.
     *
     * @param array $params
     * @return array
     */
    public function getBooks(array $params): array;

    /**
     * Store best seller data.
     *
     * @param array $data
     * @return mixed
     */
    public function storeBook(array $data);
}