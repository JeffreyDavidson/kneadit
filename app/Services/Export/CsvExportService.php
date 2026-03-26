<?php

namespace App\Services\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CsvExportService
{
    /**
     * Export type configurations: table, headers, columns, joins.
     *
     * @return array<string, array{table: string, headers: array<int, string>, columns: array<int, string|array<int, string>>, join?: array{table: string, first: string, second: string, select: array<int, string>}}>
     */
    private function configs(): array
    {
        return [
            'products' => [
                'table' => 'products',
                'headers' => ['ID', 'Name', 'Slug', 'Description', 'Price', 'Status', 'Created At', 'Updated At'],
                'columns' => ['id', 'name', 'slug', 'description', 'price', 'status', 'created_at', 'updated_at'],
            ],
            'categories' => [
                'table' => 'categories',
                'headers' => ['ID', 'Name', 'Slug', 'Description', 'Created At', 'Updated At'],
                'columns' => ['id', 'name', 'slug', 'description', 'created_at', 'updated_at'],
            ],
            'orders' => [
                'table' => 'orders',
                'headers' => ['Order ID', 'Customer ID', 'Status', 'Total', 'Item Product ID', 'Item Qty', 'Item Unit Price', 'Order Created At'],
                'columns' => ['id', 'user_id', 'status', 'total', 'item_product_id', 'item_qty', 'item_price', 'created_at'],
                'join' => [
                    'table' => 'order_items',
                    'first' => 'orders.id',
                    'second' => 'order_items.order_id',
                    'select' => [
                        'orders.*',
                        'order_items.product_id as item_product_id',
                        'order_items.quantity as item_qty',
                        'order_items.unit_price as item_price',
                    ],
                ],
            ],
            'customers' => [
                'table' => 'users',
                'headers' => ['ID', 'Name', 'Email', 'Created At', 'Updated At'],
                'columns' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ],
            'reviews' => [
                'table' => 'reviews',
                'headers' => ['ID', 'Product ID', 'User ID', 'Rating', 'Comment', 'Created At', 'Updated At'],
                'columns' => ['id', 'product_id', 'user_id', 'rating', ['comment', 'body'], 'created_at', 'updated_at'],
            ],
        ];
    }

    /** @return array<int, string> */
    public function validTypes(): array
    {
        return array_keys($this->configs());
    }

    /**
     * Write CSV data to a file handle.
     *
     * @param resource $handle
     */
    public function writeTo(mixed $handle, string $type): void
    {
        $config = $this->configs()[$type] ?? null;
        if (! $config) {
            return;
        }

        fputcsv($handle, $config['headers']);

        $query = DB::table($config['table']);

        if (isset($config['join'])) {
            $join = $config['join'];
            $query->leftJoin($join['table'], $join['first'], '=', $join['second'])
                ->select($join['select']);
        }

        $query->orderBy("{$config['table']}.id")
            ->chunk(500, function (Collection $rows) use ($handle, $config) {
                foreach ($rows as $row) {
                    $csvRow = [];
                    foreach ($config['columns'] as $column) {
                        if (is_array($column)) {
                            // Fallback columns: try first, then second
                            $csvRow[] = $row->{$column[0]} ?? $row->{$column[1]} ?? '';
                        } else {
                            $csvRow[] = $row->{$column} ?? '';
                        }
                    }
                    fputcsv($handle, $csvRow);
                }
            });
    }

    /**
     * Generate CSV as a string.
     */
    public function toString(string $type): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('Failed to open file handle');
        }

        $this->writeTo($handle, $type);

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
