<?php

namespace App\Http\Controllers\Order;

use App\Enums\SenderType;
use App\Events\OrderMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderMessageRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * Get messages for an order.
     */
    public function show(Order $order): JsonResponse
    {
        $messages = $order->messages()->oldest()->get();

        return response()->json(['messages' => $messages]);
    }

    /**
     * Send a message on an order.
     */
    public function store(StoreOrderMessageRequest $request, Order $order): JsonResponse
    {
        $message = $order->messages()->create([
            'sender_type' => SenderType::Customer,
            'sender_name' => $request->sender_name,
            'message' => $request->message,
        ]);

        event(new OrderMessageSent($message));

        return response()->json(['success' => true, 'message' => $message]);
    }
}
