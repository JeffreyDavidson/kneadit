<?php

use App\Filament\Pages\Operations\WebhooksDocs;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('webhook docs page renders for managers', function () {
    Livewire::test(WebhooksDocs::class)
        ->assertOk();
});

test('docs page surfaces all four documented events', function () {
    Livewire::test(WebhooksDocs::class)
        ->assertSee('order.created')
        ->assertSee('order.updated')
        ->assertSee('order.cancelled')
        ->assertSee('order.delivered');
});

test('docs page shows signature verification snippets', function () {
    Livewire::test(WebhooksDocs::class)
        ->assertSee('hash_hmac')
        ->assertSee('createHmac')
        ->assertSee('hmac.new');
});
