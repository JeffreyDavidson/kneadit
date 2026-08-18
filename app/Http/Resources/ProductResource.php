<?php

namespace App\Http\Resources;

use App\Models\Inventory\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/**
 * @property Product $resource
 *
 * @mixin Product
 */
class ProductResource extends JsonApiResource
{
    /** @var array<string, class-string> */
    protected array $relationships = [
        'category' => CategoryResource::class,
    ];

    public function toId(Request $request): string
    {
        return (string) $this->resource->id;
    }

    public function toType(Request $request): string
    {
        return 'products';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'price' => $this->resource->price,
            'image' => $this->resource->image,
            'is_featured' => $this->resource->is_featured,
        ];
    }
}
