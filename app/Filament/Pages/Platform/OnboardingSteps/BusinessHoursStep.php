<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Staff\DayOfWeek;
use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Support\TimezoneOptions;
use DateTimeZone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

final class BusinessHoursStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'hours';
    }

    public static function label(): string
    {
        return 'Business hours';
    }

    /**
     * The bakery's own zone is shown once it has one. While it is still the UTC
     * default the field starts empty and the browser fills in its own zone.
     */
    public static function defaults(TenantSettings $settings): array
    {
        $timezone = $settings->orders->timezone;
        $defaults = ['timezone' => $timezone === 'UTC' ? '' : $timezone];

        foreach (DayOfWeek::cases() as $day) {
            $isWeekday = ! in_array($day, [DayOfWeek::Saturday, DayOfWeek::Sunday]);
            $defaults[$day->value] = $isWeekday;
            $defaults["{$day->value}_open"] = $isWeekday ? '07:00' : '08:00';
            $defaults["{$day->value}_close"] = $isWeekday ? '18:00' : '17:00';
        }

        // Override with existing settings if available
        $hours = SettingValue::decodedMap(resolve(SettingsManager::class)->get('operating_hours'));
        if ($hours !== []) {
            // First, mark all days as closed
            foreach (DayOfWeek::cases() as $day) {
                $defaults[$day->value] = false;
            }

            // Then mark saved days as open
            foreach ($hours as $dayValue => $times) {
                if (! is_array($times)) {
                    continue;
                }

                $defaults[$dayValue] = true;
                $defaults["{$dayValue}_open"] = is_string($times['open'] ?? null) ? $times['open'] : '07:00';
                $defaults["{$dayValue}_close"] = is_string($times['close'] ?? null) ? $times['close'] : '18:00';
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
                    ->visible(fn (Get $get): mixed => $get("hours.{$day->value}")),
                TimePicker::make("hours.{$day->value}_close")
                    ->label('Close')
                    ->seconds(false)
                    ->visible(fn (Get $get): mixed => $get("hours.{$day->value}")),
            ]);
        }

        return Step::make(self::label())
            ->icon(Heroicon::OutlinedClock)
            ->description('When are you open?')
            ->schema([
                Section::make('Set your business hours')
                    ->contained(false)
                    ->description('Toggle each day on or off and set your opening and closing times.')
                    ->schema([
                        Select::make('hours.timezone')
                            ->label('Time zone')
                            ->options(TimezoneOptions::grouped())
                            ->searchable()
                            ->required()
                            ->in(DateTimeZone::listIdentifiers())
                            ->helperText('Used for today, order cut-offs and pickup times. Your hours below are in this time zone.')
                            ->extraAlpineAttributes(['x-init' => self::browserTimezoneScript()]),
                        ...$dayFields,
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->hours));
    }

    /**
     * Fills an empty field with the browser's zone, or New York when the
     * browser reports none or one that is not in the list.
     */
    private static function browserTimezoneScript(): string
    {
        $zones = Js::from(array_keys(TimezoneOptions::options()));

        return <<<JS
            if (! \$wire.\$get('hours.timezone')) {
                let zone = null
                try { zone = Intl.DateTimeFormat().resolvedOptions().timeZone } catch (error) {}
                \$wire.\$set('hours.timezone', {$zones}.includes(zone) ? zone : 'America/New_York', false)
            }
            JS;
    }

    public static function save(array $data): void
    {
        $timezone = $data['timezone'] ?? null;

        if (is_string($timezone) && TimezoneOptions::isValid($timezone)) {
            resolve(SettingsManager::class)->set('timezone', $timezone);
        }

        $hours = [];

        foreach (DayOfWeek::cases() as $day) {
            if (! empty($data[$day->value])) {
                $hours[$day->value] = [
                    'open' => $data["{$day->value}_open"] ?? '07:00',
                    'close' => $data["{$day->value}_close"] ?? '18:00',
                ];
            }
        }

        resolve(SettingsManager::class)->set('operating_hours', json_encode($hours));
    }
}
