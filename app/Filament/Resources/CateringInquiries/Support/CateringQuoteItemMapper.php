<?php

namespace App\Filament\Resources\CateringInquiries\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ValidatedInput;

class CateringQuoteItemMapper
{
    /** @return list<array{id: int|null, name: string, quantity: int, unit_price: float, special_instructions: string|null}> */
    public function map(mixed $value): array
    {
        $items = Validator::make(['items' => $value], [
            'items' => ['required', 'array'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['nullable', 'numeric', 'multiple_of:1', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'multiple_of:1', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.special_instructions' => ['nullable', 'string'],
        ])->safe()->array('items');

        $rows = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                throw new \LogicException('Validated quote item must be an array.');
            }

            $item = new ValidatedInput($row);
            $rows[] = [
                'id' => $item->filled('id') ? $item->integer('id') : null,
                'name' => $item->string('name')->toString(),
                'quantity' => $item->integer('quantity'),
                'unit_price' => $item->float('unit_price'),
                'special_instructions' => $item->filled('special_instructions')
                    ? $item->string('special_instructions')->toString()
                    : null,
            ];
        }

        return $rows;
    }
}
