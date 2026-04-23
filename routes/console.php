<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::withoutOverlapping()
    ->onOneServer()
    ->environments(['production'])
    ->runInBackground()
    ->group(function () {
        Schedule::command('paypal:check-payments')->hourly();
        Schedule::command('birthday:send-emails')->dailyAt('08:00');
        Schedule::command('orders:send-repeat-reminders')->dailyAt('10:00');
        Schedule::command('digest:weekly')->weeklyOn(1, '8:00');
        Schedule::command('reviews:send-requests')->hourly();
        Schedule::command('checkins:send')->dailyAt('09:00');
        Schedule::command('churn:check')->dailyAt('07:00');
        Schedule::command('backup:databases --keep=7')->twiceDaily(3, 15);
        Schedule::command('health:check')->everyThirtyMinutes();
        Schedule::command('trial:check')->dailyAt('10:00');
        Schedule::command('inventory:send-low-stock-alert')->dailyAt('07:00');
    });
