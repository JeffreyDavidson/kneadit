<?php

use App\Console\Commands\CheckPayPalPayments;
use App\Console\Commands\SendBirthdayDiscounts;
use App\Console\Commands\SendRepeatOrderReminders;

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
    $reflection = new ReflectionClass(CheckPayPalPayments::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->toContain('Tenant::cursor()');
    expect($source)->toContain('TenancyManager');
});

test('birthday command uses TenancyManager for tenant context', function () {
    $reflection = new ReflectionClass(SendBirthdayDiscounts::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->toContain('Tenant::cursor()');
    expect($source)->toContain('TenancyManager');
});

test('repeat reminders command uses TenancyManager for tenant context', function () {
    $reflection = new ReflectionClass(SendRepeatOrderReminders::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->toContain('Tenant::cursor()');
    expect($source)->toContain('TenancyManager');
});

test('commands handle empty tenant list', function () {
    $this->artisan('paypal:check-payments')->assertSuccessful();
    $this->artisan('birthday:send-discounts')->assertSuccessful();
    $this->artisan('orders:send-repeat-reminders')->assertSuccessful();
});
