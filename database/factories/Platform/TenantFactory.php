<?php

namespace Database\Factories\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use App\Filament\Pages\Platform\OnboardingSteps\BusinessHoursStep;
use App\Filament\Pages\Platform\OnboardingSteps\ComplianceStep;
use App\Filament\Pages\Platform\OnboardingSteps\ContactStep;
use App\Filament\Pages\Platform\OnboardingSteps\DeliveryStep;
use App\Filament\Pages\Platform\OnboardingSteps\PaymentsStep;
use App\Filament\Pages\Platform\OnboardingSteps\ProductStep;
use App\Filament\Pages\Platform\OnboardingSteps\WelcomeStep;
use App\Models\Platform\Setting;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use Database\Seeders\Customers\CustomerSeeder;
use Database\Seeders\Inventory\CategorySeeder;
use Database\Seeders\Inventory\ProductSeeder;
use Database\Seeders\Operations\SettingSeeder;
use Database\Seeders\Orders\OrderSeeder;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Tenant>
 */
#[UseModel(Tenant::class)]
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->slug(2) . '-' . bin2hex(random_bytes(4)),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'plan' => SubscriptionTier::Starter->value,
            'is_active' => true,
            'is_demo' => false,
            'storefront_enabled' => true,
            'brand_color_primary' => '#d4920c',
            'brand_color_secondary' => '#1c1410',
        ];
    }

    /**
     * Tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    /**
     * Tenant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Mark the tenant as demo data so queued work can be identified safely.
     */
    public function demo(): static
    {
        return $this->state(fn (array $attributes) => ['is_demo' => true]);
    }

    /**
     * Tenant is on trial.
     */
    public function onTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Set the subscription plan.
     */
    public function withPlan(SubscriptionTier $tier): static
    {
        return $this->state(fn (array $attributes) => ['plan' => $tier->value]);
    }

    public function starter(): static
    {
        return $this->withPlan(SubscriptionTier::Starter);
    }

    public function growth(): static
    {
        return $this->withPlan(SubscriptionTier::Growth);
    }

    public function pro(): static
    {
        return $this->withPlan(SubscriptionTier::Pro);
    }

    /**
     * Fully onboarded tenant: tenant DB seeded with defaults + every
     * onboarding wizard step replayed with realistic mock input via
     * OnboardingFixtures. Result mirrors a real user who completed
     * the wizard end-to-end.
     */
    public function onboarded(): static
    {
        return $this->afterCreating(function (Tenant $tenant): void {
            $tenant->run(function () use ($tenant): void {
                self::seedBaseTenantData();

                WelcomeStep::save(OnboardingFixtures::welcome($tenant));
                BrandingStep::save(OnboardingFixtures::branding());
                BusinessHoursStep::save(OnboardingFixtures::businessHours());
                ContactStep::save(OnboardingFixtures::contact($tenant));
                DeliveryStep::save(OnboardingFixtures::delivery());
                ComplianceStep::save(OnboardingFixtures::compliance());
                PaymentsStep::save(OnboardingFixtures::payments());
                ProductStep::save(OnboardingFixtures::product());

                resolve(SettingsManager::class)->set('onboarding_completed_at', now()->toISOString());
            });
        });
    }

    /**
     * Tenant who started the wizard but didn't finish — Welcome and
     * Branding done, no other steps. Middleware still redirects to
     * onboarding (no completed_at flag).
     */
    public function partiallyOnboarded(): static
    {
        return $this->afterCreating(function (Tenant $tenant): void {
            $tenant->run(function () use ($tenant): void {
                self::seedBaseTenantData();

                WelcomeStep::save(OnboardingFixtures::welcome($tenant));
                BrandingStep::save(OnboardingFixtures::branding());

                Setting::query()
                    ->where('key', 'onboarding_completed_at')
                    ->delete();
            });
        });
    }

    /**
     * Tenant who just signed up and hasn't touched onboarding —
     * empty tenant DB (only migrations ran), no settings, no
     * products/categories/orders. Middleware redirects.
     */
    public function justSignedUp(): static
    {
        return $this;
    }

    /**
     * Seed the minimum tenant DB content the wizard expects to find when
     * each step's save() runs (categories for ProductStep, settings
     * defaults). Called inside tenancy context.
     */
    private static function seedBaseTenantData(): void
    {
        // Tenant admin user — OrderSeeder reads users().first() and attaches
        // orders to it. Mirrors DatabaseSeeder::seedTenantData().
        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => 'baker@kneaditbakery.com'],
            [
                'name' => 'KneadIt Baker',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        (new SettingSeeder)->run();
        (new CategorySeeder)->run();
        (new CustomerSeeder)->run();
        (new ProductSeeder)->run();
        (new OrderSeeder)->run();
    }
}
