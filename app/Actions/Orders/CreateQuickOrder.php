<?php

namespace App\Actions\Orders;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CreateQuickOrder
{
    /**
     * Create an order from the admin quick order form.
     *
     * @param array<string, mixed> $data
     */
    public function __invoke(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->findOrCreateCustomer($data);

            $orderItems = $data['order_items'] ?? [];
            $subtotal = collect($orderItems)->sum(fn (array $item) => $item['quantity'] * $item['unit_price']);
            $deliveryFee = ($data['delivery_type'] === DeliveryType::Delivery->value) ? 5.00 : 0.00;

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'delivery_address' => $data['delivery_type'] === DeliveryType::Delivery->value ? $data['delivery_address'] : null,
                'delivery_date' => $data['delivery_date'],
                'delivery_time' => $data['delivery_time'],
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($orderItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);
            }

            return $order;
        });
    }

    /** @param array<string, mixed> $data */
    private function findOrCreateCustomer(array $data): Customer
    {
        if (! empty($data['customer_email'])) {
            $customer = Customer::query()->where('email', $data['customer_email'])->first();
            if ($customer) {
                return $customer;
            }
        }

        return Customer::query()->create([
            'name' => $data['customer_name'],
            'email' => $data['customer_email'] ?? null,
            'phone' => $data['customer_phone'] ?? null,
        ]);
    }
}
