<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Enums\Staff\DayOfWeek;
use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class BusinessHoursStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'hours';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $defaults = [];

        foreach (DayOfWeek::cases() as $day) {
            $isWeekday = ! in_array($day, [DayOfWeek::Saturday, DayOfWeek::Sunday]);
            $defaults[$day->value] = $isWeekday;
            $defaults["{$day->value}_open"] = $isWeekday ? '07:00' : '08:00';
            $defaults["{$day->value}_close"] = $isWeekday ? '18:00' : '17:00';
        }

        // Override with existing settings if available
        $existingHours = app(SettingsManager::class)->get('operating_hours');
        if ($existingHours) {
            $hours = json_decode($existingHours, true);
            if (is_array($hours)) {
                // First, mark all days as closed
                foreach (DayOfWeek::cases() as $day) {
                    $defaults[$day->value] = false;
                }

                // Then mark saved days as open
                foreach ($hours as $dayValue => $times) {
                    $defaults[$dayValue] = true;
                    $defaults["{$dayValue}_open"] = $times['open'] ?? '07:00';
                    $defaults["{$dayValue}_close"] = $times['close'] ?? '18:00';
                }
            }
        }

        return $defaults;
    }

    public static function make(Onboarding $page): Step
    {
        $dayFields = [];
        foreach (DayOfWeek::cases() as $day) {
            $dayFields[] = Grid::make(3)->schema([
                Toggle::make("hours.{$day->value}")
                    ->label($day->getLabel())
                    ->live(),
                TimePicker::make("hours.{$day->value}_open")
                    ->label('Open')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get("hours.{$day->value}")),
                TimePicker::make("hours.{$day->value}_close")
                    ->label('Close')
                    ->seconds(false)
                    ->visible(fn (Get $get) => $get("hours.{$day->value}")),
            ]);
        }

        return Step::make('Business Hours')
            ->icon(Heroicon::OutlinedClock)
            ->description('When are you open?')
            ->schema([
                Section::make('Set Your Business Hours')
                    ->description('Toggle each day on or off and set your opening and closing times.')
                    ->schema($dayFields),
            ])
            ->afterValidation(fn () => self::save($page->hours));
    }

    public static function save(array $data): void
    {
        $hours = [];

        foreach (DayOfWeek::cases() as $day) {
            if (! empty($data[$day->value])) {
                $hours[$day->value] = [
                    'open' => $data["{$day->value}_open"] ?? '07:00',
                    'close' => $data["{$day->value}_close"] ?? '18:00',
                ];
            }
        }

        app(SettingsManager::class)->set('operating_hours', json_encode($hours));
    }
}
