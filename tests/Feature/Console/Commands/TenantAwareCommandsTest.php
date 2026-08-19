<?php

use App\Console\Commands\Customers\SendBirthdayEmailsCommand;
use App\Console\Commands\Customers\SendRepeatOrderRemindersCommand;
use App\Console\Commands\PayPal\CheckPayPalPaymentsCommand;

beforeEach(function () {
    setUpCentralTest();
});

/** @param class-string $command */
function commandSource(string $command): string
{
    $filename = (new ReflectionClass($command))->getFileName();
    if ($filename === false) {
        throw new UnexpectedValueException("The command [{$command}] has no source file.");
    }

    $source = file_get_contents($filename);
    if ($source === false) {
        throw new UnexpectedValueException("The command source [{$filename}] could not be read.");
    }

    return $source;
}

test('check paypal payments command exists', function () {
    pendingArtisan('paypal:check-payments')
        ->assertSuccessful();
});

test('send birthday emails command exists', function () {
    pendingArtisan('birthday:send-emails')
        ->assertSuccessful();
});

test('send repeat order reminders command exists', function () {
    pendingArtisan('orders:send-repeat-reminders')
        ->assertSuccessful();
});

test('paypal command uses TenancyManager for tenant context', function () {
    $source = commandSource(CheckPayPalPaymentsCommand::class);

    expect($source)->toContain('withinTenant')->toContain('TenancyManager');
});

test('birthday command uses EngagementDispatcher for tenant context', function () {
    $source = commandSource(SendBirthdayEmailsCommand::class);

    expect($source)->toContain('EngagementDispatcher');
});

test('repeat reminders command uses EngagementDispatcher for tenant context', function () {
    $source = commandSource(SendRepeatOrderRemindersCommand::class);

    expect($source)->toContain('EngagementDispatcher');
});

test('commands handle empty tenant list', function () {
    pendingArtisan('paypal:check-payments')->assertSuccessful();
    pendingArtisan('birthday:send-emails')->assertSuccessful();
    pendingArtisan('orders:send-repeat-reminders')->assertSuccessful();
});
