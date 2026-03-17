<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Mail\NewOrderMessage;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    /**
     * Get messages for an order.
     */
    public function show(Order $order)
    {
        $messages = $order->messages()->oldest()->get();

        return response()->json(['messages' => $messages]);
    }

    /**
     * Send a message on an order.
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email'],
        ]);

        $message = $order->messages()->create([
            'sender_type' => 'customer',
            'sender_name' => $request->sender_name,
            'message' => $request->message,
        ]);

        // Email the baker
        $storeEmail = Setting::get('store_email');
        if ($storeEmail) {
            Mail::to($storeEmail)
                ->send(new NewOrderMessage($message));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }
}
