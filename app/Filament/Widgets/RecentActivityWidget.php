<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Operations\ActivityLog;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    use HasDashboardSize;

    protected static ?int $sort = 13;

    protected static ?string $heading = 'Recent Activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityLog::query()->latest()->limit($this->rowLimit()),
            )
            ->columns($this->columnSet())
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->url(route('filament.admin.resources.activity-logs.index'))
                    ->view('filament.actions.view-all-link'),
            ]);
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            WidgetSize::Large => 10,
        };
    }

    /** @return array<int, TextColumn> */
    private function columnSet(): array
    {
        // recent_activity is constrained to md/lg in WidgetMeta — at md show
        // when/user/action; lg adds the full description.
        $columns = [
            TextColumn::make('created_at')->label('When')->since()->sortable(),
            TextColumn::make('user_name')->label('User'),
            TextColumn::make('action')->badge(),
        ];

        if ($this->isSize('lg')) {
            $columns[] = TextColumn::make('description')->label('Description')->limit(60);
        }

        return $columns;
    }
}
