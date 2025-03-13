<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BestSellersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|array',
            'isbn.*' => 'string|regex:/^[0-9X]{10,13}$/',
            'title' => 'sometimes|string|max:255',
            'offset' => 'sometimes|integer|min:0|multiple_of:20',
        ];
    }
}