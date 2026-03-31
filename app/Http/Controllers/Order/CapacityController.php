<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckCapacityRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class CapacityController extends Controller
{
    public function __invoke(CheckCapacityRequest $request, string $date, CapacityCalculator $calculator): JsonResponse
    {
        $carbon = Date::parse($date);

        return ApiResponse::success([
            'available' => $calculator->isAvailable($carbon),
            'remaining' => $calculator->remainingSlots($carbon),
            'max_orders' => $calculator->getMaxOrders($carbon),
            'usage_percent' => $calculator->usagePercent($carbon),
        ], 'Capacity retrieved successfully.');
    }
}
