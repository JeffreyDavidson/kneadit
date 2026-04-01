<?php

namespace App\Filament\Pages\Operations;

use App\Actions\Tenants\UpdateSchedule;
use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Operations\BusinessSchedule;
use App\Services\Scheduling\ScheduleService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class ScheduleManager extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Schedule Manager';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.operations.schedule-manager';

    protected static ?string $title = 'Business Schedule';

    // Day properties
    /** @var array<int|string, mixed> */
    public array $schedule = [];

    public function mount(): void
    {
        $this->loadSchedule();
    }

    protected function loadSchedule(): void
    {
        $scheduleService = resolve(ScheduleService::class);

        $this->schedule = [];
        for ($day = 0; $day <= 6; $day++) {
            $record = $scheduleService->forDay($day);
            $this->schedule[$day] = [
                'is_open' => $record->is_open ?? false,
                'open_time' => $record->open_time ?? '07:00',
                'close_time' => $record->close_time ?? '18:00',
                'order_cutoff_time' => $record->order_cutoff_time ?? null,
                'max_orders' => $record->max_orders ?? null,
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
        resolve(UpdateSchedule::class)($this->schedule);

        Notification::make()
            ->title('Schedule saved successfully!')
            ->success()
            ->send();
    }
}
