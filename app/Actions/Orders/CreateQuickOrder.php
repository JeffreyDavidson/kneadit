<?php

namespace App\Actions\Orders;

use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Events\Orders\OrderCreated;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateQuickOrder
{
    public function __invoke(CreateQuickOrderData $data): Order
    {
        $order = DB::transaction(function () use ($data) {
            $customer = $this->findOrCreateCustomer($data);

            $subtotal = collect($data->orderItems)->sum(fn (array $item) => $item['quantity'] * $item['unit_price']);
            $deliveryFee = ($data->deliveryType === DeliveryType::Delivery->value)
                ? Config::float('kneadit.delivery_fees.5to10', 5.0)
                : 0.00;

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $data->paymentMethod,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'delivery_address' => $data->deliveryType === DeliveryType::Delivery->value ? $data->deliveryAddress : null,
                'delivery_date' => $data->deliveryDate,
                'delivery_time' => $data->deliveryTime,
                'notes' => $data->notes,
                'user_id' => auth()->id(),
            ]);

            foreach ($data->orderItems as $item) {
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

        event(new OrderCreated($order));

        return $order;
    }

    private function findOrCreateCustomer(CreateQuickOrderData $data): Customer
    {
        if ($data->customerEmail) {
            $customer = Customer::query()->forEmail($data->customerEmail)->first();
            if ($customer) {
                return $customer;
            }
        }

        return Customer::query()->create([
            'name' => $data->customerName,
            'email' => $data->customerEmail,
            'phone' => $data->customerPhone,
        ]);
    }
}
