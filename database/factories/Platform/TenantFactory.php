<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
#[UseModel(Tenant::class)]
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->slug(2) . '-' . bin2hex(random_bytes(4)),
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
     * Tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
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

    public function starter(): static
    {
        return $this->withPlan(SubscriptionTier::Starter);
    }

    public function growth(): static
    {
        return $this->withPlan(SubscriptionTier::Growth);
    }

    public function pro(): static
    {
        return $this->withPlan(SubscriptionTier::Pro);
    }
}
