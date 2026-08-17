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

        $records = $productIds->map(fn (int $productId) => [
            'page' => $data['page'],
            'product_id' => $productId,
            'session_id' => $data['session_id'],
            'created_at' => $timestamp,
        ])->all();

        PageView::query()->insert($records);
    }
}
