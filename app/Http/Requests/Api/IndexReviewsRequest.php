<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexReviewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'featured' => ['sometimes', 'in:true,false,1,0'],
            'product_id' => ['sometimes', 'integer'],
        ];
    }
}
