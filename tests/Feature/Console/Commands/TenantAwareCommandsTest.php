<?php

use App\Console\Commands\Customers\SendBirthdayEmailsCommand;
use App\Console\Commands\Customers\SendRepeatOrderRemindersCommand;
use App\Console\Commands\PayPal\CheckPayPalPaymentsCommand;

beforeEach(function () {
    setUpCentralTest();
});

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
    $filename = (new ReflectionClass(CheckPayPalPaymentsCommand::class))->getFileName();

    throw_unless(is_string($filename), UnexpectedValueException::class, 'Expected a command source filename.');

    $source = file_get_contents($filename);

    expect($source)->toContain('withinTenant')->toContain('TenancyManager');
});

test('birthday command uses EngagementDispatcher for tenant context', function () {
    $filename = (new ReflectionClass(SendBirthdayEmailsCommand::class))->getFileName();

    throw_unless(is_string($filename), UnexpectedValueException::class, 'Expected a command source filename.');

    $source = file_get_contents($filename);

    expect($source)->toContain('EngagementDispatcher');
});

test('repeat reminders command uses EngagementDispatcher for tenant context', function () {
    $filename = (new ReflectionClass(SendRepeatOrderRemindersCommand::class))->getFileName();

    throw_unless(is_string($filename), UnexpectedValueException::class, 'Expected a command source filename.');

    $source = file_get_contents($filename);

    expect($source)->toContain('EngagementDispatcher');
});

test('commands handle empty tenant list', function () {
    pendingArtisan('paypal:check-payments')->assertSuccessful();
    pendingArtisan('birthday:send-emails')->assertSuccessful();
    pendingArtisan('orders:send-repeat-reminders')->assertSuccessful();
});
