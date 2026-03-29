<?php

use Illuminate\Console\Scheduling\Schedule;

test('all expected commands are scheduled', function () {
    $schedule = resolve(Schedule::class);
    $commands = collect($schedule->events())
        ->map(fn ($event) => $event->command)
        ->implode('|');

    $expectedCommands = [
        'paypal:check-payments',
        'birthday:send-discounts',
        'birthday:send-emails',
        'orders:send-repeat-reminders',
        'digest:weekly',
        'reviews:send-requests',
        'checkins:send',
        'churn:check',
        'backup:databases --keep=7',
        'health:check',
        'trial:check',
    ];

    foreach ($expectedCommands as $command) {
        expect($commands)->toContain($command);
    }
});
