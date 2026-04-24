<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __invoke(StoreApiOrderRequest $request, CreateOrder $createOrder): JsonResponse
    {
        try {
            $order = $createOrder($request->toData());
        } catch (MinimumOrderAmountNotMetException $e) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'Minimum %s order is $%.2f.',
                    $e->deliveryType,
                    $e->minimum,
                ),
            ]);
        } catch (InsufficientStockException $e) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'Insufficient stock for %s.',
                    implode(', ', $e->shortages),
                ),
            ]);
        }

        if (! $order) {
            throw ValidationException::withMessages([
                'delivery_date' => 'This date is fully booked or no valid items in order.',
            ]);
        }

        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
