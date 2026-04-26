<?php

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Setting;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpCentralTest());

test('onboarded() factory state produces a fully-onboarded tenant', function () {
    $tenant = Tenant::factory()->onboarded()->create();

    // Central-DB columns set by the wizard
    expect($tenant->fresh()->store_name)->not->toBeEmpty()
        ->and($tenant->fresh()->store_logo)->not->toBeEmpty()
        ->and((bool) $tenant->fresh()->storefront_enabled)->toBeTrue()
        ->and($tenant->fresh()->brand_color_primary)->not->toBe(BrandingSettings::DEFAULT_BRAND_COLOR);

    $tenantData = $tenant->run(fn () => [
        'products' => DB::table('products')->count(),
        'categories' => DB::table('categories')->count(),
        'completedAt' => resolve(SettingsManager::class)->get('onboarding_completed_at'),
    ]);

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

    expect($counts['products'])->toBe(0)
        ->and($counts['categories'])->toBe(0);
});
