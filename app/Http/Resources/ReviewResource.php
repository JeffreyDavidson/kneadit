<?php

namespace App\Http\Resources;

use App\Models\Engagement\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/**
 * @property Review $resource
 *
 * @mixin Review
 */
class ReviewResource extends JsonApiResource
{
    /** @var array<string, class-string> */
    protected array $relationships = [
        'product' => ProductResource::class,
    ];

    public function toId(Request $request): string
    {
        return (string) $this->resource->id;
    }

    public function toType(Request $request): string
    {
        return 'reviews';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'customer_name' => $this->resource->customer_name,
            'rating' => $this->resource->rating,
            'comment' => $this->resource->comment,
            'created_at' => $this->resource->created_at?->toISOString(),
        ];
    }
}
