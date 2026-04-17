<?php

namespace App\Actions\Analytics;

use App\Models\Engagement\PageView;

class RecordPageView
{
    /** @param array{page: string, session_id: string, ip_address: ?string, user_agent: ?string} $data */
    public function __invoke(array $data): void
    {
        PageView::query()->create([
            'page' => $data['page'],
            'session_id' => $data['session_id'],
            'ip_address' => $data['ip_address'],
            'user_agent' => substr($data['user_agent'] ?? '', 0, 255),
            'created_at' => now(),
        ]);
    }
}
