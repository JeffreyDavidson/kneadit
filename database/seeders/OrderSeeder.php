<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::count() > 0) {
            return;
        }

        $customers = Customer::all();
        $products = Product::all();
        $user = User::first(); // Assuming we have at least one user

        $statuses = ['pending', 'confirmed', 'baking', 'ready', 'delivered', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid'];
        $paymentMethods = ['cash', 'paypal'];

        $deliveryAddresses = [
            '123 Main Street, Orlando, FL 32801',
            '456 Disney World Drive, Bay Lake, FL 32830',
            '789 Universal Boulevard, Orlando, FL 32819',
            '321 International Drive, Orlando, FL 32821',
            '654 Church Street, Orlando, FL 32801',
        ];

        for ($i = 0; $i < 65; $i++) {
            $customer = $customers->random();
            $requestedDate = Carbon::now()->subDays(rand(0, 60));

            // Weight the status distribution - most delivered, fewer cancelled
            $statusWeights = [
                'delivered' => 50,  // 50% delivered
                'pending' => 15,    // 15% pending
                'confirmed' => 15,  // 15% confirmed
                'baking' => 10,     // 10% baking
                'ready' => 8,       // 8% ready
                'cancelled' => 2,   // 2% cancelled
            ];

            $status = $this->weightedRandomSelect($statusWeights);

            // Payment status logic - most paid, some unpaid for recent orders
            $paymentStatus = 'paid';
            if ($requestedDate->isAfter(Carbon::now()->subDays(7)) && in_array($status, ['pending', 'confirmed'])) {
                $paymentStatus = rand(0, 100) < 30 ? 'unpaid' : 'paid'; // 30% chance unpaid for recent pending/confirmed
            }

            $isDelivery = rand(0, 100) < 40; // 40% delivery, 60% pickup

            $order = Order::create([
                'order_number' => 'ORD-'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'delivery_address' => $isDelivery ? $deliveryAddresses[array_rand($deliveryAddresses)] : null,
                'requested_date' => $requestedDate->toDateString(),
                'requested_time' => $this->randomBusinessTime(),
                'notes' => $this->randomOrderNotes(),
                'subtotal' => 0, // Will be calculated after adding items
                'delivery_fee' => $isDelivery ? rand(3, 8) : 0,
                'discount' => 0,
                'total' => 0, // Will be calculated after adding items
            ]);

            // Add 1-5 items to each order
            $itemCount = rand(1, 5);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'special_instructions' => $this->randomSpecialInstructions(),
                ]);

                $subtotal += $quantity * $unitPrice;
            }

            // Update order totals
            $total = $subtotal + $order->delivery_fee - $order->discount;
            $order->update([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
        }
    }

    private function weightedRandomSelect(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
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

        $hour = $hours[array_rand($hours)];
        $minute = $minutes[array_rand($minutes)];

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

        return $notes[array_rand($notes)];
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

        return $instructions[array_rand($instructions)];
    }
}
