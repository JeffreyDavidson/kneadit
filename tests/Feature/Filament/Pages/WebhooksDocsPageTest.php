<?php

use App\Filament\Pages\Operations\WebhooksDocs;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('webhook docs page renders for managers', function () {
    livewire(WebhooksDocs::class)
        ->assertOk();
});

test('docs page surfaces all four documented events', function () {
    livewire(WebhooksDocs::class)
        ->assertSee('order.created')
        ->assertSee('order.updated')
        ->assertSee('order.cancelled')
        ->assertSee('order.delivered');
});

test('docs page shows signature verification snippets', function () {
    livewire(WebhooksDocs::class)
        ->assertSee('hash_hmac')
        ->assertSee('createHmac')
        ->assertSee('hmac.new');
});

test('webhook docs page is closed to managers', function () {
    test()->actingAs(User::factory()->manager()->create());

    livewire(WebhooksDocs::class)
        ->assertForbidden();
});
