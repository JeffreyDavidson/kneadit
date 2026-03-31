<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\GalleryPhoto;
use Illuminate\Http\JsonResponse;

class GalleryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $photos = GalleryPhoto::query()->visible()->ordered()->get(['id', 'title', 'image_path', 'category']);

        return ApiResponse::success($photos, 'Gallery retrieved successfully.');
    }
}
