<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function __invoke(AvailabilityService $availabilityService): JsonResponse
    {
        return ApiResponse::success($availabilityService->getAvailability(), 'Availability retrieved successfully.');
    }
}
