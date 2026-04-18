<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __invoke(StoreApiOrderRequest $request, CreateOrder $createOrder): JsonResponse
    {
        $order = $createOrder($request->toData());

        if (! $order) {
            throw ValidationException::withMessages([
                'delivery_date' => 'This date is fully booked or no valid items in order.',
            ]);
        }

        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
