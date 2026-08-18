<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy Mode
    |--------------------------------------------------------------------------
    |
    | Keep report-only enabled while reviewing violation reports. Set this to
    | "enforce" in staging first, then production once expected application
    | traffic no longer produces violations.
    |
    | Supported: "report-only", "enforce"
    |
    */

    'mode' => env('CSP_MODE', 'report-only'),

];
