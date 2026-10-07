<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Schemas\Components\View;
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
            fn (string $step, int $index): Step => self::withStepCount($step::make($page), $index),
            self::STEPS,
            array_keys(self::STEPS),
        );
    }

    /**
     * The step labels, in order, for the step list beside the wizard.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return array_map(
            fn (string $step): string => $step::label(),
            self::STEPS,
        );
    }

    /** Put the small "Step N of M" line at the top of the step, above its heading. */
    private static function withStepCount(Step $step, int $index): Step
    {
        $children = $step->getDefaultChildComponents();

        return $step->schema([
            View::make('filament.pages.platform.onboarding-step-count')
                ->viewData([
                    'number' => $index + 1,
                    'total' => count(self::STEPS),
                ]),
            ...(is_array($children) ? $children : [$children]),
        ]);
    }
}
