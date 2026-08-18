<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Schemas\Components\Wizard\Step;

abstract class OnboardingStep
{
    /** Key used for Livewire array property binding ("{key}.field_name"). */
    abstract public static function key(): string;

    /**
     * Default values for this step's fields, hydrated from settings.
     *
     * @return array<string, mixed>
     */
    abstract public static function defaults(TenantSettings $settings): array;

    /** Build the Filament Wizard Step. */
    abstract public static function make(Onboarding $page): Step;

    /**
     * Persist this step's data.
     *
     * @param array<string, mixed> $data
     */
    abstract public static function save(array $data): void;
}
