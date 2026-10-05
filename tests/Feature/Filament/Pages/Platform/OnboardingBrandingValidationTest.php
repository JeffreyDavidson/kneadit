<?php

use App\Filament\Pages\Platform\Onboarding;
use App\Filament\Pages\Platform\OnboardingSteps\BrandingStep;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

function brandingColorPicker(string $statePath): ColorPicker
{
    $page = new Onboarding;
    $schema = Schema::make($page)->components([BrandingStep::make($page)]);

    return collect($schema->getFlatComponents())
        ->first(fn ($component): bool => $component instanceof ColorPicker && $component->getStatePath(false) === $statePath);
}

test('the branding step accepts only six digit hex colors', function (string $statePath, string $value, bool $valid) {
    $picker = brandingColorPicker($statePath);

    $validator = Validator::make(['color' => $value], ['color' => $picker->getValidationRules()]);

    expect($validator->passes())->toBe($valid);
})->with([
    'primary hex' => ['branding.color_primary', '#6b4c3b', true],
    'secondary upper case hex' => ['branding.color_secondary', '#D4A574', true],
    'primary css break-out' => ['branding.color_primary', 'red;}body{display:none', false],
    'secondary named color' => ['branding.color_secondary', 'red', false],
    'primary short hex' => ['branding.color_primary', '#fff', false],
]);
