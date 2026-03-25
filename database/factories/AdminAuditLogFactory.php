<?php

namespace Database\Factories;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAuditLog> */
class AdminAuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_id' => null,
            'action' => fake()->randomElement(['create', 'update', 'delete', 'view']),
            'description' => fake()->sentence(),
            'target_type' => null,
            'target_id' => null,
            'user_name' => fake()->name(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
