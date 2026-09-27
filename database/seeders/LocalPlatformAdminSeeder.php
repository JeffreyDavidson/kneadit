<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class LocalPlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        throw_unless(
            app()->isLocal(),
            RuntimeException::class,
            'The local platform administrator seeder may only run in the local environment.',
        );

        $name = Config::get('kneadit.seed_admin.name');
        $email = Config::get('kneadit.seed_admin.email');
        $password = Config::get('kneadit.seed_admin.password');

        if (! is_string($email) || blank($email) || ! is_string($password) || blank($password)) {
            return;
        }

        throw_unless(is_string($name) && $name !== '', RuntimeException::class, 'SEED_ADMIN_NAME must be a non-empty string.');

        throw_unless(filter_var($email, FILTER_VALIDATE_EMAIL) !== false, RuntimeException::class, 'SEED_ADMIN_EMAIL must be a valid email address.');

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $existingAdmins = User::query()
                ->where('role', UserRole::PlatformAdmin)
                ->get();

            throw_if(
                $existingAdmins->count() > 1,
                RuntimeException::class,
                'Multiple local platform administrators exist. Choose the account to update before seeding.',
            );

            $user = $existingAdmins->first() ?? new User;
        }

        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::PlatformAdmin,
        ]);
        $user->email_verified_at = now();
        $user->save();

        $this->command?->info('Local platform administrator created or updated.');
    }
}
