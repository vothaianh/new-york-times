<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\BestSellersRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BestSellersRequestTest extends TestCase
{
    /**
     * Test validation passes with valid data.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        $rules = (new BestSellersRequest())->rules();

        $validator = Validator::make([
            'author' => 'John Doe',
            'isbn' => ['1234567890', '9781234567897'],
            'title' => 'Test Book',
            'offset' => 20,
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test validation fails with invalid ISBN format.
     */
    public function test_validation_fails_with_invalid_isbn_format(): void
    {
        $rules = (new BestSellersRequest())->rules();

        $validator = Validator::make([
            'isbn' => ['invalid-isbn', '123456'],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('isbn.0', $validator->errors()->toArray());
        $this->assertArrayHasKey('isbn.1', $validator->errors()->toArray());
    }

    /**
     * Test validation fails with invalid offset.
     */
    public function test_validation_fails_with_invalid_offset(): void
    {
        $rules = (new BestSellersRequest())->rules();

        $validator = Validator::make([
            'offset' => 15, // Not a multiple of 20
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('offset', $validator->errors()->toArray());
    }

    /**
     * Test validation fails with negative offset.
     */
    public function test_validation_fails_with_negative_offset(): void
    {
        $rules = (new BestSellersRequest())->rules();

        $validator = Validator::make([
            'offset' => -20,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('offset', $validator->errors()->toArray());
    }

    /**
     * Test validation passes with empty request.
     */
    public function test_validation_passes_with_empty_request(): void
    {
        $rules = (new BestSellersRequest())->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->passes());
    }
}