<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    /**
     * Return availability for the next 30 days.
     */
    public function __invoke(AvailabilityService $availabilityService): JsonResponse
    {
        return response()->json($availabilityService->getAvailability());
    }
}
