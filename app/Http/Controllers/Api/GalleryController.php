<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryPhotoResource;
use App\Http\Responses\ApiResponse;
use App\Models\GalleryPhoto;
use Illuminate\Http\JsonResponse;

class GalleryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $photos = GalleryPhoto::query()->visible()->ordered()->get();

        return ApiResponse::success(GalleryPhotoResource::collection($photos), 'Gallery retrieved successfully.');
    }
}
