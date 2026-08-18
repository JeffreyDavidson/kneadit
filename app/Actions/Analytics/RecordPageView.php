<?php

namespace App\Actions\Analytics;

use App\Models\Engagement\PageView;

class RecordPageView
{
    /** @param array{page: string, session_id: string} $data */
    public function __invoke(array $data): void
    {
        PageView::query()->create([
            'page' => $data['page'],
            'session_id' => $data['session_id'],
            'created_at' => now(),
        ]);
    }
}
