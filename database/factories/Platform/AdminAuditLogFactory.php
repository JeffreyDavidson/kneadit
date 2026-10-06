<?php

namespace Database\Factories\Platform;

use App\Models\Platform\AdminAuditLog;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAuditLog> */
#[UseModel(AdminAuditLog::class)]
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
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
