<?php

namespace App\Http\Controllers\Order;

use App\Actions\Orders\SendOrderMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderMessageRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Orders\Order;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function store(StoreOrderMessageRequest $request, Order $order, SendOrderMessage $sendMessage): JsonResponse
    {
        $message = $sendMessage(
            order: $order,
            senderName: $request->string('sender_name')->toString(),
            message: $request->string('message')->toString(),
        );

        return ApiResponse::success($message, 'Message sent successfully.');
    }

    public function show(Order $order): JsonResponse
    {
        return ApiResponse::success(
            $order->messages()->oldest()->get(),
            'Messages retrieved successfully.',
        );
    }
}
