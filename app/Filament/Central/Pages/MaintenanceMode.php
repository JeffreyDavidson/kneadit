<?php

namespace App\Filament\Central\Pages;

use App\Models\PlatformSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use UnitEnum;

class MaintenanceMode extends Page
{
    use InteractsWithFormActions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Maintenance Mode';

    protected string $view = 'filament.central.pages.maintenance-mode';

    public bool $maintenance_mode = false;

    public ?string $maintenance_message = '';

    public ?string $maintenance_scheduled_start = null;

    public ?string $maintenance_scheduled_end = null;

    public array $affected_services = [];

    public function mount(): void
    {
        $this->maintenance_mode = PlatformSetting::get('maintenance_mode', '0') === '1';
        $this->maintenance_message = PlatformSetting::get('maintenance_message', '');
        $this->maintenance_scheduled_start = PlatformSetting::get('maintenance_scheduled_start');
        $this->maintenance_scheduled_end = PlatformSetting::get('maintenance_scheduled_end');
        $this->affected_services = json_decode(PlatformSetting::get('affected_services', '[]'), true) ?: [];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                View::make('filament.central.pages.partials.maintenance-status')
                    ->viewData([
                        'active' => $this->maintenance_mode,
                    ]),

                Section::make('Maintenance Settings')
                    ->description('Control platform-wide maintenance mode for all tenants.')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Enable Maintenance Mode')
                            ->helperText('When enabled, affected services will show a maintenance page.')
                            ->live(),

                        Textarea::make('maintenance_message')
                            ->label('Maintenance Message')
                            ->placeholder('We are currently performing scheduled maintenance. We\'ll be back shortly!')
                            ->rows(3)
                            ->columnSpanFull(),

                        DateTimePicker::make('maintenance_scheduled_start')
                            ->label('Scheduled Start')
                            ->helperText('Optional: when maintenance is expected to begin.'),

                        DateTimePicker::make('maintenance_scheduled_end')
                            ->label('Scheduled End')
                            ->helperText('Optional: when maintenance is expected to end.'),

                        CheckboxList::make('affected_services')
                            ->label('Affected Services')
                            ->options([
                                'storefront' => 'Storefront',
                                'admin' => 'Admin Panel',
                                'api' => 'API',
                            ])
                            ->helperText('Select which services should show the maintenance page.'),
                    ]),

                Section::make('Preview')
                    ->description('This is what bakers will see when maintenance mode is active.')
                    ->visible(fn () => $this->maintenance_mode)
                    ->schema([
                        View::make('filament.central.pages.partials.maintenance-preview')
                            ->viewData([
                                'message' => $this->maintenance_message,
                                'scheduled_end' => $this->maintenance_scheduled_end,
                            ]),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->action('save')
                        ->icon('heroicon-o-check')
                        ->color('primary'),
                ]),
            ]);
    }

    public function save(): void
    {
        PlatformSetting::set('maintenance_mode', $this->maintenance_mode ? '1' : '0');
        PlatformSetting::set('maintenance_message', $this->maintenance_message ?? '');
        PlatformSetting::set('maintenance_scheduled_start', $this->maintenance_scheduled_start);
        PlatformSetting::set('maintenance_scheduled_end', $this->maintenance_scheduled_end);
        PlatformSetting::set('affected_services', json_encode($this->affected_services));

        Notification::make()
            ->title($this->maintenance_mode ? 'Maintenance mode activated' : 'Maintenance mode deactivated')
            ->success()
            ->send();
    }

    public function getFormActions(): array
    {
        return [];
    }
}
