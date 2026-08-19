<?php

namespace App\Filament\Filters;

use App\ValueObjects\Money;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AmountRangeFilter extends Filter
{
    protected function setUp(): void
    {
        parent::setUp();

        $column = $this->getName();

        $this->schema([
            TextInput::make('min_amount')->numeric()->prefix('$'),
            TextInput::make('max_amount')->numeric()->prefix('$'),
        ]);

        $this->query(function (Builder $query, array $data) use ($column) {
            $minimum = filled($data['min_amount'] ?? null) ? Arr::float($data, 'min_amount') : 0.0;
            $maximum = filled($data['max_amount'] ?? null) ? Arr::float($data, 'max_amount') : 0.0;

            return $query
                ->when(
                    $minimum > 0,
                    fn (Builder $query) => $query->where($column, '>=', Money::fromDollars($minimum)->cents()),
                )
                ->when(
                    $maximum > 0,
                    fn (Builder $query) => $query->where($column, '<=', Money::fromDollars($maximum)->cents()),
                );
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];
            $minimum = filled($data['min_amount'] ?? null) ? Arr::float($data, 'min_amount') : 0.0;
            $maximum = filled($data['max_amount'] ?? null) ? Arr::float($data, 'max_amount') : 0.0;
            if ($minimum > 0) {
                $indicators[] = 'Min: $' . $minimum;
            }
            if ($maximum > 0) {
                $indicators[] = 'Max: $' . $maximum;
            }

            return $indicators;
        });
    }
}
