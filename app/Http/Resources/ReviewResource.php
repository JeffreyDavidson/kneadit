<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Review */
class ReviewResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'customer_name' => $this->customer_name,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
