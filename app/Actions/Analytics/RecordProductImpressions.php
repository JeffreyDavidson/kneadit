<?php

namespace App\Actions\Analytics;

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;

class RecordProductImpressions
{
    /** @param array{page: string, session_id: string} $data */
    public function __invoke(array $data): void
    {
        $productIds = Product::query()->active()->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        $timestamp = now();

        $records = $productIds->map(fn (mixed $productId) => [
            'page' => $data['page'],
            'product_id' => $this->productId($productId),
            'session_id' => $data['session_id'],
            'created_at' => $timestamp,
        ])->all();

        PageView::query()->insert($records);
    }

    private function productId(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected a numeric product ID.');
        }

        return (int) $value;
    }
}
