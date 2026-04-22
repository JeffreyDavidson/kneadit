<?php

namespace App\Builders\Content;

use App\Models\Content\GalleryPhoto;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<GalleryPhoto> */
class GalleryPhotoQueryBuilder extends Builder
{
    public function visible(): static
    {
        $this->where('is_visible', true);

        return $this;
    }

    public function ordered(): static
    {
        $this->orderBy('sort_order');

        return $this;
    }
}
