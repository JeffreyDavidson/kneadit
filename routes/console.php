<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule PayPal payment checks hourly
Schedule::command('paypal:check-payments')->hourly()->withoutOverlapping();

// Schedule birthday discount emails daily at 9 AM
Schedule::command('birthday:send-discounts')->dailyAt('09:00')->withoutOverlapping();

// Schedule happy birthday emails daily at 8 AM
Schedule::command('birthday:send-emails')->dailyAt('08:00')->withoutOverlapping();

// Schedule repeat order reminders daily at 10 AM
Schedule::command('orders:send-repeat-reminders')->dailyAt('10:00')->withoutOverlapping();

// Weekly digest email every Monday at 8 AM
Schedule::command('digest:weekly')->weeklyOn(1, '8:00')->withoutOverlapping();

// Review request emails every hour
Schedule::command('reviews:send-requests')->hourly()->withoutOverlapping();

// Scheduled check-in emails daily at 9 AM
Schedule::command('checkins:send')->dailyAt('09:00')->withoutOverlapping();

// Churn alert checks daily at 7 AM
Schedule::command('churn:check')->dailyAt('07:00')->withoutOverlapping();

// Database backups twice daily, keep 7 days
Schedule::command('backup:databases --keep=7')->twiceDaily(3, 15)->withoutOverlapping();

// Health check every 30 minutes — alerts on failure
Schedule::command('health:check')->everyThirtyMinutes()->withoutOverlapping();

// Trial expiration checks daily at 10 AM
Schedule::command('trial:check')->dailyAt('10:00')->withoutOverlapping();
