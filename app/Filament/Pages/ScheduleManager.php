<?php

namespace App\Filament\Pages;

use App\Models\BusinessSchedule;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

use App\Traits\HasPlanGating;
class ScheduleManager extends Page
{
    use HasPlanGating, RequiresRole;


    protected static string $requiredPlan = 'pro';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Schedule Manager';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.schedule-manager';
    protected static ?string $title = 'Business Schedule';

    // Day properties
    public array $schedule = [];

    public function mount(): void
    {
        $this->loadSchedule();
    }

    protected function loadSchedule(): void
    {
        $this->schedule = [];
        for ($day = 0; $day <= 6; $day++) {
            $record = BusinessSchedule::forDay($day);
            $this->schedule[$day] = [
                'is_open' => $record?->is_open ?? false,
                'open_time' => $record?->open_time ?? '07:00',
                'close_time' => $record?->close_time ?? '18:00',
                'order_cutoff_time' => $record?->order_cutoff_time ?? null,
                'max_orders' => $record?->max_orders ?? null,
            ];
        }
    }

    public function content(Schema $schema): Schema
    {
        $daySchemas = [];
        foreach (BusinessSchedule::DAYS as $dayNum => $dayName) {
            $daySchemas[] = Section::make($dayName)
                ->schema([
                    Grid::make(5)->schema([
                        Toggle::make("schedule.{$dayNum}.is_open")
                            ->label('Open'),
                        TextInput::make("schedule.{$dayNum}.open_time")
                            ->label('Opens')
                            ->type('time'),
                        TextInput::make("schedule.{$dayNum}.close_time")
                            ->label('Closes')
                            ->type('time'),
                        TextInput::make("schedule.{$dayNum}.order_cutoff_time")
                            ->label('Order Cutoff')
                            ->type('time'),
                        TextInput::make("schedule.{$dayNum}.max_orders")
                            ->label('Max Orders')
                            ->numeric(),
                    ]),
                ])
                ->compact();
        }

        $daySchemas[] = Actions::make([
            Action::make('save')
                ->label('Save Schedule')
                ->color('primary')
                ->action('save'),
        ])->alignEnd()->columnSpanFull();

        return $schema->schema($daySchemas);
    }

    public function save(): void
    {
        foreach ($this->schedule as $dayOfWeek => $data) {
            BusinessSchedule::updateOrCreate(
                ['day_of_week' => $dayOfWeek],
                [
                    'is_open' => $data['is_open'] ?? false,
                    'open_time' => $data['is_open'] ? ($data['open_time'] ?: null) : null,
                    'close_time' => $data['is_open'] ? ($data['close_time'] ?: null) : null,
                    'order_cutoff_time' => $data['is_open'] ? ($data['order_cutoff_time'] ?: null) : null,
                    'max_orders' => $data['is_open'] ? ($data['max_orders'] ?: null) : null,
                ]
            );
        }

        Notification::make()
            ->title('Schedule saved successfully!')
            ->success()
            ->send();
    }
}
