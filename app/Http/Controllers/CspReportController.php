<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CspReportController extends Controller
{
    /**
     * Receive Content-Security-Policy violation reports from browsers and log
     * them. Reports are emitted in both report-only and enforcement modes (see
     * SecurityHeaders), providing visibility during staged CSP rollout.
     */
    public function __invoke(Request $request): Response
    {
        abort_if(
            strlen($request->getContent()) > Config::integer('csp.max_report_bytes'),
            Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        );

        // Browsers POST a JSON document like {"csp-report": {...}}; support
        // both the legacy report-uri shape and a bare body for safety.
        $report = $request->json('csp-report') ?? $request->json()->all();
        $context = $this->safeContext($report);

        Log::channel(Config::string('logging.csp_channel', 'stack'))
            ->warning('CSP violation report', $context);

        return response()->noContent();
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    private function safeContext(mixed $report): array
    {
        if (! is_array($report)) {
            return ['malformed' => true];
        }

        return collect(Arr::only($report, Config::array('csp.report_fields')))
            ->filter(fn (mixed $value): bool => is_scalar($value) || $value === null)
            ->map(fn (mixed $value): bool|int|string|null => is_string($value)
                ? Str::limit($value, 2_048, '')
                : $this->scalarValue($value))
            ->all();
    }

    private function scalarValue(mixed $value): bool|int|string|null
    {
        if (is_bool($value) || is_int($value) || is_string($value) || $value === null) {
            return $value;
        }

        if (is_float($value)) {
            return (string) $value;
        }

        return null;
    }
}
