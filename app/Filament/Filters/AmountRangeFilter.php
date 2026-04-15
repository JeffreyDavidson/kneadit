<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

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
            return $query
                ->when(
                    $data['min_amount'],
                    fn (Builder $query, string $amount) => $query->where($column, '>=', $amount),
                )
                ->when(
                    $data['max_amount'],
                    fn (Builder $query, string $amount) => $query->where($column, '<=', $amount),
                );
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['min_amount'] ?? null) {
                $indicators[] = 'Min: $' . $data['min_amount'];
            }
            if ($data['max_amount'] ?? null) {
                $indicators[] = 'Max: $' . $data['max_amount'];
            }

            return $indicators;
        });
    }
}
