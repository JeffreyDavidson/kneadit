<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class DateRangeFilter extends Filter
{
    protected function setUp(): void
    {
        parent::setUp();

        $column = $this->getName();

        $this->schema([
            DatePicker::make('from'),
            DatePicker::make('until'),
        ]);

        $this->query(function (Builder $query, array $data) use ($column) {
            return $query
                ->when(
                    $data['from'],
                    fn (Builder $query, string $date) => $query->whereDate($column, '>=', $date),
                )
                ->when(
                    $data['until'],
                    fn (Builder $query, string $date) => $query->whereDate($column, '<=', $date),
                );
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['from'] ?? null) {
                $indicators[] = 'From ' . Date::parse($data['from'])->toFormattedDateString();
            }
            if ($data['until'] ?? null) {
                $indicators[] = 'Until ' . Date::parse($data['until'])->toFormattedDateString();
            }

            return $indicators;
        });
    }
}
