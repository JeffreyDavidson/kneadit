<?php

namespace App\Http\Resources;

use App\Models\Content\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/**
 * @property GalleryPhoto $resource
 *
 * @mixin GalleryPhoto
 */
class GalleryPhotoResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        return (string) $this->resource->id;
    }

    public function toType(Request $request): string
    {
        return 'gallery-photos';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'image_path' => $this->resource->image_path,
            'category' => $this->resource->category->value,
        ];
    }
}
