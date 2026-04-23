<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Engagement\CustomerCampaignLog;
use Illuminate\Http\Response;

class TrackCampaignOpenController extends Controller
{
    /**
     * Stamps opened_at on the matching log row (idempotent — only the
     * first hit wins) and returns a 1x1 transparent GIF. Always returns
     * the GIF, even on miss/error, so customer mail clients never show
     * a broken image.
     */
    public function __invoke(string $token): Response
    {
        CustomerCampaignLog::query()
            ->where('tracking_token', $token)
            ->whereNull('opened_at')
            ->update(['opened_at' => now()]);

        return response($this->transparentGif(), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function transparentGif(): string
    {
        // 1x1 transparent GIF89a, 43 bytes.
        return base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }
}
