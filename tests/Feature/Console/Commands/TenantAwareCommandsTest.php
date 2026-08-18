<?php

use App\Console\Commands\Customers\SendBirthdayEmailsCommand;
use App\Console\Commands\Customers\SendRepeatOrderRemindersCommand;
use App\Console\Commands\PayPal\CheckPayPalPaymentsCommand;

beforeEach(function () {
    setUpCentralTest();
});

test('check paypal payments command exists', function () {
    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('send birthday emails command exists', function () {
    $this->artisan('birthday:send-emails')
        ->assertSuccessful();
});

test('send repeat order reminders command exists', function () {
    $this->artisan('orders:send-repeat-reminders')
        ->assertSuccessful();
});

test('paypal command uses TenancyManager for tenant context', function () {
    $source = file_get_contents((new ReflectionClass(CheckPayPalPaymentsCommand::class))->getFileName());

    expect($source)->toContain('withinTenant')->toContain('TenancyManager');
});

test('birthday command uses EngagementDispatcher for tenant context', function () {
    $source = file_get_contents((new ReflectionClass(SendBirthdayEmailsCommand::class))->getFileName());

    expect($source)->toContain('EngagementDispatcher');
});

test('repeat reminders command uses EngagementDispatcher for tenant context', function () {
    $source = file_get_contents((new ReflectionClass(SendRepeatOrderRemindersCommand::class))->getFileName());

    expect($source)->toContain('EngagementDispatcher');
});

test('commands handle empty tenant list', function () {
    $this->artisan('paypal:check-payments')->assertSuccessful();
    $this->artisan('birthday:send-emails')->assertSuccessful();
    $this->artisan('orders:send-repeat-reminders')->assertSuccessful();
});
