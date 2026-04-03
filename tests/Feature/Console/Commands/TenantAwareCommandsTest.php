<?php

use App\Console\Commands\Customers\SendBirthdayDiscountsCommand;
use App\Console\Commands\Customers\SendRepeatOrderRemindersCommand;
use App\Console\Commands\Stripe\CheckPayPalPaymentsCommand;

beforeEach(function () {
    setUpCentralTest();
});

test('check paypal payments command exists', function () {
    $this->artisan('paypal:check-payments')
        ->assertSuccessful();
});

test('send birthday discounts command exists', function () {
    $this->artisan('birthday:send-discounts')
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
    $source = file_get_contents((new ReflectionClass(SendBirthdayDiscountsCommand::class))->getFileName());

    expect($source)->toContain('EngagementDispatcher');
});

test('repeat reminders command uses EngagementDispatcher for tenant context', function () {
    $source = file_get_contents((new ReflectionClass(SendRepeatOrderRemindersCommand::class))->getFileName());

    expect($source)->toContain('EngagementDispatcher');
});

test('commands handle empty tenant list', function () {
    $this->artisan('paypal:check-payments')->assertSuccessful();
    $this->artisan('birthday:send-discounts')->assertSuccessful();
    $this->artisan('orders:send-repeat-reminders')->assertSuccessful();
});
