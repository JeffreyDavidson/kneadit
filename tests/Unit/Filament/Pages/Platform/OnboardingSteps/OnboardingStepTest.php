<?php

use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use App\Filament\Pages\Platform\OnboardingSteps\BusinessHoursStep;
use App\Filament\Pages\Platform\OnboardingSteps\CompleteStep;
use App\Filament\Pages\Platform\OnboardingSteps\ComplianceStep;
use App\Filament\Pages\Platform\OnboardingSteps\ContactStep;
use App\Filament\Pages\Platform\OnboardingSteps\DeliveryStep;
use App\Filament\Pages\Platform\OnboardingSteps\PaymentsStep;
use App\Filament\Pages\Platform\OnboardingSteps\PreviewStep;
use App\Filament\Pages\Platform\OnboardingSteps\ProductStep;
use App\Filament\Pages\Platform\OnboardingSteps\WelcomeStep;

test('onboarding steps expose their registry key', function (string $step, string $key) {
    expect($step::key())->toBe($key);
})->with([
    'branding' => [BrandingStep::class, 'branding'],
    'business hours' => [BusinessHoursStep::class, 'hours'],
    'complete' => [CompleteStep::class, 'complete'],
    'compliance' => [ComplianceStep::class, 'compliance'],
    'contact' => [ContactStep::class, 'contact'],
    'delivery' => [DeliveryStep::class, 'delivery'],
    'payments' => [PaymentsStep::class, 'payments'],
    'preview' => [PreviewStep::class, 'preview'],
    'product' => [ProductStep::class, 'product'],
    'welcome' => [WelcomeStep::class, 'welcome'],
]);

test('compliance step exposes all US states', function () {
    expect(ComplianceStep::usStates())
        ->toBeArray()
        ->toHaveCount(50)
        ->toHaveKey('FL', 'Florida')
        ->toHaveKey('CA', 'California')
        ->toHaveKey('NY', 'New York');
});
