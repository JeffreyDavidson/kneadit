<?php

/**
 * FilaCheck Pro Configuration overrides.
 *
 * Only rules we explicitly disable need to live here — every other Pro rule
 * inherits its default from vendor/laraveldaily/filacheck-pro/config/filacheck-pro.php.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Disabled UX suggestions
    |--------------------------------------------------------------------------
    |
    | These rules nudge toward search/filter UI on every Filament Table —
    | useful guidance for Resource list pages, but noise for dashboard
    | Widgets where the tables are fixed-snapshot summaries (Recent Orders,
    | Low Stock, At-Risk Customers, etc.). Filacheck does not support
    | per-path scoping, so the choice is project-wide. Re-enable if a
    | Resource list page slips through without filters/search.
    */
    'table-without-searchable-columns' => [
        'enabled' => false,
    ],

    'missing-table-filters' => [
        'enabled' => false,
    ],

    'flat-form-overload' => [
        'max_fields' => 12,
    ],
];
