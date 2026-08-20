<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
            $from = Arr::string($data, 'from', '');
            $until = Arr::string($data, 'until', '');

            return $query
                ->when(
                    $from !== '',
                    fn (Builder $query) => $query->whereDate($column, '>=', $from),
                )
                ->when(
                    $until !== '',
                    fn (Builder $query) => $query->whereDate($column, '<=', $until),
                );
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];
            $from = Arr::string($data, 'from', '');
            $until = Arr::string($data, 'until', '');
            if ($from !== '') {
                $indicators[] = 'From ' . Date::parse($from)->toFormattedDateString();
            }
            if ($until !== '') {
                $indicators[] = 'Until ' . Date::parse($until)->toFormattedDateString();
            }

            return $indicators;
        });
    }
}
