<?php

namespace App\Http\Resources;

use App\Models\Customers\CustomerFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin CustomerFavorite */
class FavoriteResource extends JsonApiResource
{
    /** @var array<string, class-string> */
    protected array $relationships = [
        'product' => ProductResource::class,
    ];

    public function toId(Request $request): string
    {
        return (string) $this->resource->getKey();
    }

    public function toType(Request $request): string
    {
        return 'favorites';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'customer_email' => $this->resource->customer_email,
            'product_id' => $this->resource->product_id,
        ];
    }
}
