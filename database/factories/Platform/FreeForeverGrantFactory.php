<?php

namespace Database\Factories\Platform;

use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FreeForeverGrant>
 */
#[UseModel(FreeForeverGrant::class)]
class FreeForeverGrantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'granted_by_user_id' => User::factory(),
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => ['revoked_at' => now()]);
    }
}
