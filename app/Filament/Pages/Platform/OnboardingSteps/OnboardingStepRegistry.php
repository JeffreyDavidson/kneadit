<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Schemas\Components\Wizard\Step;

final class OnboardingStepRegistry
{
    /** @var array<int, class-string<OnboardingStep>> */
    private const array STEPS = [
        WelcomeStep::class,
        ContactStep::class,
        BrandingStep::class,
        ProductStep::class,
        BusinessHoursStep::class,
        ComplianceStep::class,
        DeliveryStep::class,
        PaymentsStep::class,
        PreviewStep::class,
        CompleteStep::class,
    ];

    /**
     * Hydrate default values for all steps.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(TenantSettings $settings): array
    {
        $defaults = [];

        foreach (self::STEPS as $step) {
            $defaults[$step::key()] = $step::defaults($settings);
        }

        return $defaults;
    }

    /**
     * Build all wizard steps.
     *
     * @return array<int, Step>
     */
    public static function steps(Onboarding $page): array
    {
        return array_map(
            fn (string $step): Step => $step::make($page),
            self::STEPS,
        );
    }
}
