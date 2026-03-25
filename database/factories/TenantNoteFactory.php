<?php

namespace Database\Factories;

use App\Models\TenantNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantNote> */
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
