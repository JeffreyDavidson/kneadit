<?php

namespace App\Actions\Analytics;

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;

class RecordProductImpressions
{
    /** @param array{page: string, session_id: string, ip_address: ?string, user_agent: ?string} $data */
    public function __invoke(array $data): void
    {
        $productIds = Product::query()->active()->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        $userAgent = substr($data['user_agent'] ?? '', 0, 255);
        $timestamp = now();

        $records = $productIds->map(fn (int $productId) => [
            'page' => $data['page'],
            'product_id' => $productId,
            'session_id' => $data['session_id'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $userAgent,
            'created_at' => $timestamp,
        ])->all();

        PageView::query()->insert($records);
    }
}
