<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BestSellersRequest;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * @var BookService
     */
    protected $BookService;

    /**
     * BookController constructor.
     *
     * @param BookService $BookService
     */
    public function __construct(BookService $BookService)
    {
        $this->BookService = $BookService;
    }

    /**
     * Get NYT Best Sellers list.
     *
     * @param BestSellersRequest $request
     * @return JsonResponse
     */
    public function bestSellers(BestSellersRequest $request): JsonResponse
    {
        $data = $this->BookService->getBooks(
            $request->validated()
        );

        if (isset($data['status']) && $data['status'] === 'error') {
            return response()->json([
                'success' => false,
                'message' => $data['message'],
            ], $data['code']);
        }

        return response()->json([
            'success' => true,
            'count' => $data['num_results'],
            'data' => $data['results'],
        ]);
    }
}