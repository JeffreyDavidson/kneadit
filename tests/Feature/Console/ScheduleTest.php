<?php

use Illuminate\Console\Scheduling\Schedule;

dataset('scheduled_commands', [
    'paypal:check-payments',
    'birthday:send-emails',
    'orders:send-repeat-reminders',
    'digest:weekly',
    'reviews:send-requests',
    'checkins:send',
    'churn:check',
    'backup:databases --keep=7',
    'health:check',
    'trial:check',
]);

test('expected command is scheduled', function (string $command) {
    $schedule = resolve(Schedule::class);
    $commands = collect($schedule->events())
        ->map(fn ($event) => $event->command)
        ->implode('|');

    expect($commands)->toContain($command);
})->with('scheduled_commands');
