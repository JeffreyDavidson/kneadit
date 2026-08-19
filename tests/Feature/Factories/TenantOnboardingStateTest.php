<?php

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Setting;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpCentralTest());

test('onboarded() factory state produces a fully-onboarded tenant', function () {
    $tenant = Tenant::factory()->onboarded()->create();
    $tenant->refresh();

    // Central-DB columns set by the wizard
    expect($tenant->store_name)->not->toBeEmpty()
        ->and($tenant->store_logo)->not->toBeEmpty()
        ->and($tenant->storefront_enabled)->toBeTrue()
        ->and($tenant->brand_color_primary)->not->toBe(BrandingSettings::DEFAULT_BRAND_COLOR);

    $tenantData = $tenant->run(fn () => [
        'products' => DB::table('products')->count(),
        'categories' => DB::table('categories')->count(),
        'completedAt' => resolve(SettingsManager::class)->get('onboarding_completed_at'),
    ]);

    if (! is_array($tenantData)
        || ! is_int($tenantData['products'] ?? null)
        || ! is_int($tenantData['categories'] ?? null)) {
        throw new UnexpectedValueException('The onboarded tenant counts must be integers.');
    }

    expect($tenantData['products'])->toBeGreaterThan(0)
        ->and($tenantData['categories'])->toBeGreaterThan(0)
        ->and($tenantData['completedAt'])->not->toBeNull();
});

test('partiallyOnboarded() factory state leaves the redirect flag unset', function () {
    $tenant = Tenant::factory()->partiallyOnboarded()->create();

    $completedAt = $tenant->run(fn () => Setting::query()->where('key', 'onboarding_completed_at')->value('value'));

    expect($completedAt)->toBeNull();
});

test('justSignedUp() factory state leaves tenant DB empty of seeded content', function () {
    $tenant = Tenant::factory()->justSignedUp()->create();

    $counts = $tenant->run(fn () => [
        'products' => DB::table('products')->count(),
        'categories' => DB::table('categories')->count(),
    ]);

    if (! is_array($counts)
        || ! is_int($counts['products'] ?? null)
        || ! is_int($counts['categories'] ?? null)) {
        throw new UnexpectedValueException('The new tenant counts must be integers.');
    }

    expect($counts['products'])->toBe(0)
        ->and($counts['categories'])->toBe(0);
});
