<?php

namespace App\Http\Controllers;

use App\Services\Security\CspReportContextBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class CspReportController extends Controller
{
    /**
     * Receive Content-Security-Policy violation reports from browsers and log
     * them. Reports are emitted in both report-only and enforcement modes (see
     * SecurityHeaders), providing visibility during staged CSP rollout.
     */
    public function __invoke(Request $request, CspReportContextBuilder $contextBuilder): Response
    {
        abort_if(
            strlen($request->getContent()) > Config::integer('csp.max_report_bytes'),
            Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        );

        // Browsers POST a JSON document like {"csp-report": {...}}; support
        // both the legacy report-uri shape and a bare body for safety.
        $report = $request->json('csp-report') ?? $request->json()->all();
        $context = $contextBuilder->build($report);

        Log::channel(Config::string('logging.csp_channel', 'stack'))
            ->warning('CSP violation report', $context);

        return response()->noContent();
    }
}
