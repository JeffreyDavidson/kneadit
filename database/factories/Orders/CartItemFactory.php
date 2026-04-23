<?php

namespace Database\Factories\Orders;

use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
#[UseModel(CartItem::class)]
class CartItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 1.00, 50.00),
        ];
    }
}
