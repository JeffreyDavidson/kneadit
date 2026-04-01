<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreInfoResource;
use App\Http\Responses\ApiResponse;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\JsonResponse;

class StoreInfoController extends Controller
{
    public function __invoke(TenantSettings $settings): JsonResponse
    {
        return ApiResponse::success(new StoreInfoResource($settings), 'Store info retrieved successfully.');
    }
}
