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
            $from = Arr::get($data, 'from');
            $until = Arr::get($data, 'until');
            $from = is_string($from) ? $from : '';
            $until = is_string($until) ? $until : '';

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
            $from = Arr::get($data, 'from');
            $until = Arr::get($data, 'until');
            $from = is_string($from) ? $from : '';
            $until = is_string($until) ? $until : '';
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
