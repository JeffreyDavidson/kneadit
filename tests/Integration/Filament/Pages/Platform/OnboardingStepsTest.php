<?php

use App\Filament\Pages\Platform\OnboardingSteps\CompleteStep;
use App\Filament\Pages\Platform\OnboardingSteps\PreviewStep;

beforeEach(function () {
    setUpTenantTest();
});

test('complete step key is complete', function () {
    expect(CompleteStep::key())->toBe('complete');
});

test('complete step defaults returns empty array', function () {
    $settings = app(App\Services\Settings\TenantSettings::class);

    expect(CompleteStep::defaults($settings))->toBeEmpty();
});

test('complete step save does nothing', function () {
    CompleteStep::save(['some' => 'data']);

    // No exception, no side effects
    expect(true)->toBeTrue();
});

test('preview step key is preview', function () {
    expect(PreviewStep::key())->toBe('preview');
});

test('preview step defaults returns empty array', function () {
    $settings = app(App\Services\Settings\TenantSettings::class);

    expect(PreviewStep::defaults($settings))->toBeEmpty();
});

test('preview step save does nothing', function () {
    PreviewStep::save(['some' => 'data']);

    // No exception, no side effects
    expect(true)->toBeTrue();
});
