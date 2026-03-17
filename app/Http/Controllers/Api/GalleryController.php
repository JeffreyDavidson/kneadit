<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use Illuminate\Http\JsonResponse;

class GalleryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $photos = GalleryPhoto::visible()->ordered()->get(['id', 'title', 'image_path', 'category']);

        return response()->json([
            'data' => $photos,
            'message' => 'Gallery retrieved successfully.',
        ]);
    }
}
