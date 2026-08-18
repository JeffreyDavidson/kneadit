<?php

return [
    'at_risk_threshold_days' => (int) env('AT_RISK_THRESHOLD_DAYS', 30),
    'recent_days' => (int) env('ANALYTICS_RECENT_DAYS', 30),
    'trend_days' => (int) env('ANALYTICS_TREND_DAYS', 30),
    'inventory_usage_window_days' => (int) env('INVENTORY_USAGE_WINDOW_DAYS', 30),
    'page_view_retention_days' => (int) env('PAGE_VIEW_RETENTION_DAYS', 90),
];
