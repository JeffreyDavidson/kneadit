<?php

namespace App\Actions\Orders;

use App\Models\Orders\Order;
use Illuminate\Support\Str;

class GenerateOrderNumber
{
    /**
     * Random ORD-{10 chars} format. 62^10 ≈ 8.4×10^17 combinations —
     * effectively unguessable, eliminating order_number enumeration as
     * a path to other customers' orders. Loops on the rare collision.
     */
    public function __invoke(): string
    {
        do {
            $candidate = 'ORD-' . strtoupper(Str::random(10));
        } while (Order::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }
}
