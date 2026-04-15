<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Churn Detection Thresholds
    |--------------------------------------------------------------------------
    */
    'churn_no_login_days' => (int) env('CHURN_NO_LOGIN_DAYS', 7),
    'churn_no_orders_days' => (int) env('CHURN_NO_ORDERS_DAYS', 30),
    'churn_min_tenant_age_days' => (int) env('CHURN_MIN_TENANT_AGE_DAYS', 14),
    'churn_trial_alert_hours' => (int) env('CHURN_TRIAL_ALERT_HOURS', 48),
    'churn_low_setup_threshold' => (int) env('CHURN_LOW_SETUP_THRESHOLD', 15),
    'churn_low_health_threshold' => (int) env('CHURN_LOW_HEALTH_THRESHOLD', 40),
];
