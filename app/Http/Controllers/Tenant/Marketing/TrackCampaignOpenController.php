<?php

namespace App\Http\Controllers\Tenant\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Engagement\CustomerCampaignLog;
use Illuminate\Http\Response;

class TrackCampaignOpenController extends Controller
{
    private const string TRANSPARENT_GIF_BASE64 = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

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
            ->update([
                'opened_at' => now(),
            ]);

        return response(base64_decode(self::TRANSPARENT_GIF_BASE64), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
