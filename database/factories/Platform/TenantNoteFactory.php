<?php

namespace Database\Factories\Platform;

use App\Models\Platform\TenantNote;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantNote> */
#[UseModel(TenantNote::class)]
class TenantNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => fake()->uuid(),
            'body' => fake()->paragraph(),
            'author' => fake()->name(),
        ];
    }
}
