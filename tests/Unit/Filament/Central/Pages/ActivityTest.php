<?php

use App\Filament\Central\Pages\Activity;
use Filament\Support\Icons\Heroicon;

test('previous page decrements but stays at 1', function () {
    $page = new Activity;
    $page->page = 1;
    $page->previousPage();

    expect($page->page)->toBe(1);
});

test('previous page decrements from higher page', function () {
    $page = new Activity;
    $page->page = 3;
    $page->previousPage();

    expect($page->page)->toBe(2);
});

test('next page increments', function () {
    $page = new Activity;
    $page->page = 1;
    $page->nextPage();

    expect($page->page)->toBe(2);
});

test('get event icon returns icon for all known events', function (string $event) {
    $result = Activity::getEventIcon($event);

    expect($result)->toBeInstanceOf(Heroicon::class);
})->with([
    'tenant_created',
    'tenant_deactivated',
    'plan_changed',
    'storefront_toggled',
    'trial_expired',
]);

test('get event color returns hex for all known events', function (string $event) {
    $result = Activity::getEventColor($event);

    expect($result)->toMatch('/^#[0-9a-f]{6}$/');
})->with([
    'tenant_created',
    'tenant_deactivated',
    'plan_changed',
    'storefront_toggled',
    'trial_expired',
]);

test('get action color returns hex for all known actions', function (string $action) {
    $result = Activity::getActionColor($action);

    expect($result)->toMatch('/^#[0-9a-f]{6}$/');
})->with([
    'created_tenant',
    'updated_tenant',
    'deleted_tenant',
    'activated',
    'deactivated',
    'changed_plan',
    'extended_trial',
    'sent_announcement',
    'sent_campaign',
    'sent_message',
    'impersonated',
    'exported_data',
    'toggled_maintenance',
]);

test('get action category returns category for all known actions', function (string $action, string $expected) {
    expect(Activity::getActionCategory($action))->toBe($expected);
})->with([
    ['created_tenant', 'Tenant'],
    ['updated_tenant', 'Tenant'],
    ['deleted_tenant', 'Tenant'],
    ['activated', 'Tenant'],
    ['deactivated', 'Tenant'],
    ['changed_plan', 'Billing'],
    ['extended_trial', 'Billing'],
    ['sent_announcement', 'Communication'],
    ['sent_campaign', 'Communication'],
    ['sent_message', 'Communication'],
    ['impersonated', 'Security'],
    ['exported_data', 'Operations'],
    ['toggled_maintenance', 'Operations'],
]);
