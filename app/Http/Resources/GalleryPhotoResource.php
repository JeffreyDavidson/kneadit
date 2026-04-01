<?php

namespace App\Http\Resources;

use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GalleryPhoto */
class GalleryPhotoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_path' => $this->image_path,
            'category' => $this->category,
        ];
    }
}
