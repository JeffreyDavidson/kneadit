<?php

namespace Database\Factories\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CateringInquiryItem>
 */
#[UseModel(CateringInquiryItem::class)]
class CateringInquiryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'catering_inquiry_id' => CateringInquiry::factory(),
            'name' => fake()->randomElement(['Wedding cake', 'Macarons (50ct)', 'Cupcakes (dozen)', 'Setup fee']),
            'quantity' => fake()->numberBetween(1, 12),
            'unit_price' => fake()->randomFloat(2, 25, 400),
            'special_instructions' => fake()->optional(0.2)->sentence(),
            'sort_order' => 0,
        ];
    }
}
