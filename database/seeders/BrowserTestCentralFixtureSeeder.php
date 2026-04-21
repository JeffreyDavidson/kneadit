<?php

namespace Database\Seeders;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Database\Seeder;
use RuntimeException;

// Idempotent seeder that ensures a stable central (platform-admin) user
// exists so Pest browser tests can authenticate against the landlord
// Filament panel.
//
// Not registered in DatabaseSeeder — opt-in only. Run via:
//   php artisan db:seed --class="Database\\Seeders\\BrowserTestCentralFixtureSeeder"
//
// Do NOT run in production (the guard below will refuse).
class BrowserTestCentralFixtureSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'browser-test-central@kneadit.test';

    public const ADMIN_PASSWORD = 'browser-test-password';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('BrowserTestCentralFixtureSeeder must never run in production.');
        }

        User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Browser Test Central Admin',
                'password' => self::ADMIN_PASSWORD,
                'role' => UserRole::PlatformAdmin,
                'email_verified_at' => now(),
            ],
        );
    }
}
