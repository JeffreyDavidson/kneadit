<?php

namespace Database\Factories\Orders;

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
#[UseModel(OrderItem::class)]
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 5, 50),
        ];
    }
}
