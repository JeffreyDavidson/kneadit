<?php

namespace App\Filament\Central\Pages;

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

    /** @var array<string, mixed> */
    public array $affected_services = [];

    public function mount(): void
    {
        $this->maintenance_mode = platformSettings('maintenance_mode', '0') === '1';
        $this->maintenance_message = platformSettings('maintenance_message', '');
        $this->maintenance_scheduled_start = platformSettings('maintenance_scheduled_start');
        $this->maintenance_scheduled_end = platformSettings('maintenance_scheduled_end');
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) platformSettings('affected_services', '[]'), true) ?: [];
        $this->affected_services = $decoded;
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
        platformSettings(['maintenance_mode' => $this->maintenance_mode ? '1' : '0']);
        platformSettings(['maintenance_message' => $this->maintenance_message ?? '']);
        platformSettings(['maintenance_scheduled_start' => $this->maintenance_scheduled_start]);
        platformSettings(['maintenance_scheduled_end' => $this->maintenance_scheduled_end]);
        platformSettings(['affected_services' => json_encode($this->affected_services)]);

        Notification::make()
            ->title($this->maintenance_mode ? 'Maintenance mode activated' : 'Maintenance mode deactivated')
            ->success()
            ->send();
    }

    /** @return array<int, Action> */
    public function getFormActions(): array
    {
        return [];
    }
}
