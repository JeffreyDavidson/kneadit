<?php

namespace App\Filament\Pages\Platform;

use App\Enums\Operations\ActivityAction;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Operations\ActivityLog;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogPage extends Page
{
    use RequiresManagerRole;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Activity Log';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.platform.activity-log';

    public ?string $filterAction = null;

    public ?string $filterModelType = null;

    public ?string $filterUser = null;

    public ?string $filterDateFrom = null;

    public ?string $filterDateTo = null;

    public int $page = 1;

    public int $perPage = 25;

    public ?int $expandedId = null;

    /** @return LengthAwarePaginator<int, ActivityLog> */
    public function getActivitiesProperty(): LengthAwarePaginator
    {
        $query = ActivityLog::query()->latest();

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterModelType) {
            $query->where('model_type', $this->filterModelType);
        }

        if ($this->filterUser) {
            $query->whereLike('user_name', "%{$this->filterUser}%");
        }

        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('created_at', '<=', $this->filterDateTo . ' 23:59:59');
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Filters')
                ->schema([
                    Grid::make(5)->schema([
                        Select::make('filterAction')
                            ->label('Action')
                            ->options($this->getActionOptions())
                            ->placeholder('All Actions')
                            ->live(),

                        Select::make('filterModelType')
                            ->label('Model Type')
                            ->options($this->getModelTypeOptions())
                            ->placeholder('All Types')
                            ->live(),

                        TextInput::make('filterUser')
                            ->label('User')
                            ->placeholder('Search by user…')
                            ->live(debounce: 300),

                        DatePicker::make('filterDateFrom')
                            ->label('From')
                            ->live(),

                        DatePicker::make('filterDateTo')
                            ->label('To')
                            ->live(),
                    ]),
                ]),
        ]);
    }

    /** @return array<string, string> */
    public function getActionOptions(): array
    {
        return ActivityLog::query()
            ->distinct()
            ->pluck('action')
            ->filter()
            ->mapWithKeys(fn (ActivityAction $a): array => [$a->value => $a->getLabel()])
            ->sort()
            ->all();
    }

    /** @return array<string, string> */
    public function getModelTypeOptions(): array
    {
        return ActivityLog::query()
            ->distinct()
            ->pluck('model_type')
            ->filter()
            ->mapWithKeys(fn (string $t): array => [$t => class_basename($t)])
            ->sort()
            ->all();
    }

    public function toggleExpanded(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->filterAction = null;
        $this->filterModelType = null;
        $this->filterUser = null;
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function updatedFilterAction(): void
    {
        $this->page = 1;
    }

    public function updatedFilterModelType(): void
    {
        $this->page = 1;
    }

    public function updatedFilterUser(): void
    {
        $this->page = 1;
    }

    public function updatedFilterDateFrom(): void
    {
        $this->page = 1;
    }

    public function updatedFilterDateTo(): void
    {
        $this->page = 1;
    }
}
