<?php

namespace App\Http\Resources;

use App\Models\Inventory\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin Category */
class CategoryResource extends JsonApiResource
{
    /** @var array<string, class-string> */
    protected array $relationships = [
        'products' => ProductResource::class,
    ];

    public function toId(Request $request): string
    {
        return (string) $this->resource->getKey();
    }

    public function toType(Request $request): string
    {
        return 'categories';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'sort_order' => $this->resource->sort_order,
        ];
    }
}
