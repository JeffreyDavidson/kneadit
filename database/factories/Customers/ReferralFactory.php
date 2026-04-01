<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\ReferralStatus;
use App\Models\Customers\Referral;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Referral> */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_tenant_id' => fake()->uuid(),
            'referred_tenant_id' => null,
            'referral_code' => strtoupper(Str::random(8)),
            'referred_email' => fake()->optional()->safeEmail(),
            'status' => ReferralStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReferralStatus::Completed,
        ]);
    }
}
