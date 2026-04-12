<?php

namespace Database\Factories\Engagement;

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageView> */
#[UseModel(PageView::class)]
class PageViewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page' => '/' . fake()->slug(2),
            'product_id' => Product::factory(),
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
