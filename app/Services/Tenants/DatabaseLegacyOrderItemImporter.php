<?php

namespace App\Services\Tenants;

use App\Services\Tenants\Contracts\LegacyOrderItemImporter;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

class DatabaseLegacyOrderItemImporter implements LegacyOrderItemImporter
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, int>  $orderIds
     * @param  array<int, int>  $productIds
     */
    public function import(array $items, array $orderIds, array $productIds): void
    {
        DB::table('order_items')->whereIn('order_id', array_values($orderIds))->delete();

        foreach ($items as $item) {
            DB::table('order_items')->insert([
                'order_id' => $orderIds[$this->integer($item['order_id'])],
                'name' => $item['product_name'],
                'product_id' => isset($item['product_id']) ? ($productIds[$this->integer($item['product_id'])] ?? null) : null,
                'quantity' => $item['quantity'],
                'unit_price' => $this->cents($item['unit_price'] ?? 0),
                'special_instructions' => isset($item['selections']) ? json_encode($item['selections'], JSON_THROW_ON_ERROR) : null,
                'created_at' => $item['created_at'] ?? now(),
                'updated_at' => $item['updated_at'] ?? now(),
            ]);
        }
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function cents(mixed $dollars): int
    {
        if (is_int($dollars) || is_float($dollars)) {
            return (int) round($dollars * 100);
        }
        if (is_string($dollars) && is_numeric($dollars)) {
            return (int) round((float) $dollars * 100);
        }
        throw new UnexpectedValueException('Expected a numeric legacy money value.');
    }
}
