<?php

namespace Database\Factories\Platform;

use App\Models\Platform\ImpersonationToken;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ImpersonationToken>
 */
#[UseModel(ImpersonationToken::class)]
class ImpersonationTokenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(64),
            'tenant_id' => Tenant::factory(),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subHour()]);
    }
}
