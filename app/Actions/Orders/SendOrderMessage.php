<?php

namespace App\Actions\Orders;

use App\Enums\SenderType;
use App\Events\Orders\OrderMessageSent;
use App\Models\Order;
use App\Models\OrderMessage;

class SendOrderMessage
{
    public function __invoke(Order $order, string $senderName, string $message, SenderType $senderType = SenderType::Customer): OrderMessage
    {
        $orderMessage = $order->messages()->create([
            'sender_type' => $senderType,
            'sender_name' => $senderName,
            'message' => $message,
        ]);

        if ($senderType === SenderType::Baker) {
            $order->messages()
                ->where('sender_type', SenderType::Customer)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        event(new OrderMessageSent($orderMessage));

        return $orderMessage;
    }
}
