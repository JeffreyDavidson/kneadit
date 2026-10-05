<?php

namespace Database\Factories\Platform;

use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BillingHandoffToken>
 */
#[UseModel(BillingHandoffToken::class)]
class BillingHandoffTokenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token_hash' => hash('sha256', Str::random(64)),
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory()->owner(),
            'expires_at' => now()->addMinutes(2),
            'created_at' => now(),
        ];
    }

    /** Store the hash of a known raw token so a test can present it. */
    public function forToken(string $token): static
    {
        return $this->state(['token_hash' => hash('sha256', $token)]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }

    public function consumed(): static
    {
        return $this->state([
            'consumed_at' => now()->subMinute(),
            'consumer_ip' => '127.0.0.1',
        ]);
    }
}
