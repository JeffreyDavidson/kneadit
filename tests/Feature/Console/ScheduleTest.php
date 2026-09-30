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

dataset('bakery_local_commands', [
    'birthday:send-emails',
    'orders:send-repeat-reminders',
    'digest:weekly',
    'inventory:send-low-stock-alert',
]);

test('commands that send at a bakery-local hour are scheduled hourly', function (string $command) {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn ($event) => str_ends_with((string) $event->command, $command));

    expect($event->expression)->toBe('0 * * * *');
})->with('bakery_local_commands');
