<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryPhotoResource;
use App\Models\Content\GalleryPhoto;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GalleryController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $photos = GalleryPhoto::query()->visible()->ordered()->get();

        return GalleryPhotoResource::collection($photos);
    }
}
