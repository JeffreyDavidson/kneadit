<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule PayPal payment checks hourly
Schedule::command('paypal:check-payments')->hourly();

// Schedule birthday discount emails daily at 9 AM
Schedule::command('birthday:send-discounts')->dailyAt('09:00');

// Schedule repeat order reminders daily at 10 AM
Schedule::command('orders:send-repeat-reminders')->dailyAt('10:00');
