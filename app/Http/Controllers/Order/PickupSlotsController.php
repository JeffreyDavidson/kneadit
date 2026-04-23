<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Scheduling\PickupSlotResolver;
use Illuminate\Http\JsonResponse;

class PickupSlotsController extends Controller
{
    public function __invoke(string $date, PickupSlotResolver $resolver): JsonResponse
    {
        return ApiResponse::success([
            'date' => $date,
            'slots' => $resolver->availableSlots($date),
        ], 'Pickup slots resolved.');
    }
}
