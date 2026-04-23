<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CspReportController extends Controller
{
    /**
     * Receive Content-Security-Policy violation reports from browsers and log
     * them. The header is currently in Report-Only mode (see SecurityHeaders),
     * so reports flow in but nothing is blocked. Once the log goes quiet the
     * policy can be tightened and switched to enforcement.
     */
    public function __invoke(Request $request): Response
    {
        // Browsers POST a JSON document like {"csp-report": {...}}; support
        // both the legacy report-uri shape and a bare body for safety.
        $report = $request->json('csp-report') ?? $request->json()->all();

        Log::channel(config('logging.csp_channel', 'stack'))
            ->warning('CSP violation report', $report ?: ['raw' => $request->getContent()]);

        return response()->noContent();
    }
}
