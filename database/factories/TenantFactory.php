<?php

namespace Database\Factories;

use App\Enums\SubscriptionTier;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->slug(2),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'plan' => SubscriptionTier::Starter->value,
            'is_active' => true,
            'storefront_enabled' => true,
            'brand_color_primary' => '#d4920c',
            'brand_color_secondary' => '#1c1410',
        ];
    }

    /**
     * Tenant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Tenant is on trial.
     */
    public function onTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Set the subscription plan.
     */
    public function withPlan(SubscriptionTier $tier): static
    {
        return $this->state(fn (array $attributes) => ['plan' => $tier->value]);
    }
}
