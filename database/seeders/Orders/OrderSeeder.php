<?php

namespace Database\Seeders\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->count() > 0) {
            return;
        }

        $customers = Customer::all();
        $products = Product::all();
        $user = User::query()->first(); // Assuming we have at least one user

        $paymentMethods = [PaymentMethod::Cash, PaymentMethod::PayPal];

        $deliveryAddresses = [
            '123 Main Street, Orlando, FL 32801',
            '456 Disney World Drive, Bay Lake, FL 32830',
            '789 Universal Boulevard, Orlando, FL 32819',
            '321 International Drive, Orlando, FL 32821',
            '654 Church Street, Orlando, FL 32801',
        ];

        for ($i = 0; $i < 65; $i++) {
            $customer = $customers->random();
            $requestedDate = Date::now()->subDays(random_int(0, 60));

            // Weight the status distribution - most delivered, fewer cancelled
            $statusWeights = [
                OrderStatus::Delivered->value => 50,  // 50% delivered
                OrderStatus::Pending->value => 15,    // 15% pending
                OrderStatus::Confirmed->value => 15,  // 15% confirmed
                OrderStatus::Baking->value => 10,     // 10% baking
                OrderStatus::Ready->value => 8,       // 8% ready
                OrderStatus::Cancelled->value => 2,   // 2% cancelled
            ];

            $status = $this->weightedRandomSelect($statusWeights);

            // Payment status logic - most paid, some unpaid for recent orders
            $paymentStatus = PaymentStatus::Paid;
            if ($requestedDate->isAfter(Date::now()->subDays(7)) && in_array($status, [OrderStatus::Pending->value, OrderStatus::Confirmed->value])) {
                $paymentStatus = random_int(0, 100) < 30 ? PaymentStatus::Unpaid : PaymentStatus::Paid; // 30% chance unpaid for recent pending/confirmed
            }

            $isDelivery = random_int(0, 100) < 40; // 40% delivery, 60% pickup

            $order = Order::query()->create([
                'order_number' => 'ORD-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => Arr::random($paymentMethods),
                'delivery_address' => $isDelivery ? Arr::random($deliveryAddresses) : null,
                'delivery_date' => $requestedDate->toDateString(),
                'delivery_time' => $this->randomBusinessTime(),
                'notes' => $this->randomOrderNotes(),
                'subtotal' => 0, // Will be calculated after adding items
                'delivery_fee' => $isDelivery ? random_int(3, 8) : 0,
                'discount_amount' => 0,
                'total' => 0, // Will be calculated after adding items
            ]);

            // Add 1-5 items to each order
            $itemCount = random_int(1, 5);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = random_int(1, 3);
                $unitPrice = $product->price;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'special_instructions' => $this->randomSpecialInstructions(),
                ]);

                $subtotal += $quantity * $unitPrice->dollars();
            }

            // Update order totals
            $total = $subtotal + $order->delivery_fee->dollars() - $order->discount_amount->dollars();
            $order->update([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
        }
    }

    private function weightedRandomSelect(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = random_int(1, $totalWeight);
        $currentWeight = 0;

        foreach ($weights as $item => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $item;
            }
        }

        return array_key_first($weights);
    }

    private function randomBusinessTime(): string
    {
        $hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17]; // 8 AM to 5 PM
        $minutes = [0, 15, 30, 45];

        $hour = Arr::random($hours);
        $minute = Arr::random($minutes);

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function randomOrderNotes(): ?string
    {
        $notes = [
            null,
            null,
            null, // 60% chance of no notes
            'Please call when ready for pickup',
            'Birthday surprise - please keep confidential!',
            'Nut allergy - please ensure no cross-contamination',
            'Urgent order for tomorrow morning event',
            'Customer prefers text messages',
            'Delivery to back door please',
            'Cash payment on delivery',
        ];

        return Arr::random($notes);
    }

    private function randomSpecialInstructions(): ?string
    {
        $instructions = [
            null,
            null,
            null,
            null, // 80% chance of no special instructions
            'Extra chocolate chips please',
            'Light on the frosting',
            'Make it extra sweet',
            'No nuts please',
            'Add sprinkles',
            'Write "Happy Birthday Sarah" on top',
        ];

        return Arr::random($instructions);
    }
}
