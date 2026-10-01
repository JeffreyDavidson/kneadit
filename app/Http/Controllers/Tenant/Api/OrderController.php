<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\MinimumOrderAmountNotMetException;
use App\Exceptions\Orders\NoOrderableItemsException;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Orders\Order;
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
        } catch (NoOrderableItemsException) {
            throw ValidationException::withMessages(['items' => NoOrderableItemsException::CUSTOMER_MESSAGE]);
        } catch (PickupSlotUnavailableException) {
            throw ValidationException::withMessages([
                'delivery_time' => PickupSlotUnavailableException::CUSTOMER_MESSAGE,
            ]);
        }

        if (! $order instanceof Order) {
            throw ValidationException::withMessages([
                'delivery_date' => 'This date is fully booked or no valid items in order.',
            ]);
        }

        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
