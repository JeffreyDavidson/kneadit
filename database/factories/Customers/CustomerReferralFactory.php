<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\CustomerReferralStatus;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerReferral>
 */
#[UseModel(CustomerReferral::class)]
class CustomerReferralFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_customer_id' => Customer::factory(),
            'referred_customer_id' => null,
            'order_id' => null,
            'reward_coupon_id' => null,
            'status' => CustomerReferralStatus::Pending,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerReferralStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
