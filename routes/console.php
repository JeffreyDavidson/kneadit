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
        Schedule::command('paypal:check-payments')->hourly()->name('paypal:check-payments');
        Schedule::command('birthday:send-emails')->dailyAt('08:00')->name('birthday:send-emails');
        Schedule::command('orders:send-repeat-reminders')->dailyAt('10:00')->name('orders:send-repeat-reminders');
        Schedule::command('digest:weekly')->weeklyOn(1, '8:00')->name('digest:weekly');
        Schedule::command('reviews:send-requests')->hourly()->name('reviews:send-requests');
        Schedule::command('checkins:send')->dailyAt('09:00')->name('checkins:send');
        Schedule::command('churn:check')->dailyAt('07:00')->name('churn:check');
        Schedule::command('backup:databases --keep=7')->twiceDaily(3, 15)->name('backup:databases');
        Schedule::command('health:check')->everyThirtyMinutes()->name('health:check');
        Schedule::command('trial:check')->dailyAt('10:00')->name('trial:check');
        Schedule::command('inventory:send-low-stock-alert')->dailyAt('07:00')->name('inventory:send-low-stock-alert');
        Schedule::command('carts:send-abandonment-emails')->hourly()->name('carts:send-abandonment-emails');
        Schedule::command('platform:audit-free-forever')->dailyAt('06:00')->name('platform:audit-free-forever');
        Schedule::command('webhooks:prune')->dailyAt('04:00')->name('webhooks:prune');
        Schedule::command('tenants:sync-onboarding-metrics')->everyFifteenMinutes()->name('tenants:sync-onboarding-metrics');
    });
