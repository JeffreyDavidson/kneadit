<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CapacityResource;
use App\Services\Inventory\CapacityCalculator;

class CapacityController extends Controller
{
    public function __invoke(string $date, CapacityCalculator $calculator): CapacityResource
    {
        return new CapacityResource([
            'date' => $date,
            'available' => $calculator->isAvailable($date),
            'remaining' => $calculator->remainingSlots($date),
            'max' => $calculator->getMaxOrders($date),
        ]);
    }
}
