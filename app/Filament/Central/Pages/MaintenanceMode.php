<?php

namespace App\Filament\Central\Pages;

use App\DataTransferObjects\Settings\SettingValue;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MaintenanceMode extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Maintenance Mode';

    protected string $view = 'filament.central.pages.maintenance-mode';

    public bool $maintenance_mode = false;

    public ?string $maintenance_message = '';

    public ?string $maintenance_scheduled_start = null;

    public ?string $maintenance_scheduled_end = null;

    /** @var array<int, string> */
    public array $affected_services = [];

    public function mount(): void
    {
        $this->maintenance_mode = platformSettings('maintenance_mode', '0') === '1';
        $this->maintenance_message = SettingValue::nullableString(platformSettings('maintenance_message')) ?? '';
        $this->maintenance_scheduled_start = SettingValue::nullableString(platformSettings('maintenance_scheduled_start'));
        $this->maintenance_scheduled_end = SettingValue::nullableString(platformSettings('maintenance_scheduled_end'));
        $this->affected_services = array_values(array_filter(
            SettingValue::decodedList(platformSettings('affected_services')),
            is_string(...),
        ));
    }

    public function toggleMaintenance(): void
    {
        $this->maintenance_mode = ! $this->maintenance_mode;

        platformSettings(['maintenance_mode' => $this->maintenance_mode ? '1' : '0']);

        Notification::make()
            ->title($this->maintenance_mode ? 'Maintenance mode activated' : 'Platform brought back online')
            ->success()
            ->send();
    }

    public function save(): void
    {
        platformSettings(['maintenance_message' => $this->maintenance_message ?? '']);
        platformSettings(['maintenance_scheduled_start' => $this->maintenance_scheduled_start]);
        platformSettings(['maintenance_scheduled_end' => $this->maintenance_scheduled_end]);
        platformSettings(['affected_services' => json_encode($this->affected_services)]);

        Notification::make()
            ->title('Maintenance settings saved')
            ->success()
            ->send();
    }
}
