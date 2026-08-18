<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy Mode
    |--------------------------------------------------------------------------
    |
    | Enforcement is the secure default. Report-only remains available as an
    | explicit, temporary rollback mode while investigating a deployment.
    |
    | Supported: "report-only", "enforce"
    |
    */

    'mode' => env('CSP_MODE', 'enforce'),

    'max_report_bytes' => (int) env('CSP_MAX_REPORT_BYTES', 16_384),

    'report_fields' => [
        'blocked-uri',
        'column-number',
        'disposition',
        'document-uri',
        'effective-directive',
        'line-number',
        'referrer',
        'script-sample',
        'source-file',
        'status-code',
        'violated-directive',
    ],

];
