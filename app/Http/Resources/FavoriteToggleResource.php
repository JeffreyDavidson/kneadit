<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @property array{customer_email: string, product_id: int, favorited: bool} $resource */
class FavoriteToggleResource extends JsonApiResource
{
    /**
     * @param array{customer_email: string, product_id: int, favorited: bool} $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    public function toId(Request $request): string
    {
        return "{$this->resource['customer_email']}-{$this->resource['product_id']}";
    }

    public function toType(Request $request): string
    {
        return 'favorite-toggles';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'customer_email' => $this->resource['customer_email'],
            'product_id' => $this->resource['product_id'],
            'favorited' => $this->resource['favorited'],
        ];
    }
}
