<?php

use App\Filament\Widgets\InboxWidget;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('inbox widget renders successfully', function () {
    Livewire::test(InboxWidget::class)
        ->assertOk();
});

test('inbox widget get unread count returns integer', function () {
    $widget = new InboxWidget;

    // Without a tenant in Filament context, returns 0
    expect($widget->getUnreadCount())->toBeInt();
});

test('inbox widget get messages url returns string', function () {
    $widget = new InboxWidget;

    $url = $widget->getMessagesUrl();

    expect($url)->toBeString()->toContain('messages');
});
