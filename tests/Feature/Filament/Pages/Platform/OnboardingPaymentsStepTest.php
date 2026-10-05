<?php

use App\Filament\Pages\Platform\Onboarding;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();

    // Persist the central tenant without provisioning a separate database for each test.
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create());
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);

    settings([
        'payment_methods' => json_encode(['stripe', 'paypal']),
        'paypal_client_id' => 'stored-client-id',
        'paypal_client_secret' => 'stored-paypal-secret',
    ]);
});

test('an owner sees the payment section and never receives the stored secret', function () {
    test()->actingAs(User::factory()->owner()->create());

    $component = livewire(Onboarding::class)
        ->assertSuccessful()
        ->assertSee('Payment Collection')
        ->assertSee('Connect with Stripe')
        ->assertSee('PayPal Client Secret')
        ->assertSee('Set — enter a new value to replace it')
        ->assertSet('payments.paypal_client_secret', '');

    expect($component->html())->not->toContain('stored-paypal-secret')
        ->and(json_encode($component->snapshot))->not->toContain('stored-paypal-secret');
});

test('a manager gets no payment section and no credentials', function () {
    test()->actingAs(User::factory()->manager()->create());

    $component = livewire(Onboarding::class)
        ->assertSuccessful()
        ->assertSee('Only the bakery owner can set up payments')
        ->assertDontSee('Payment Collection')
        ->assertDontSee('Connect with Stripe')
        ->assertDontSee('PayPal Client Secret')
        ->assertSet('payments.paypal_client_id', '')
        ->assertSet('payments.paypal_client_secret', '');

    expect($component->html())->not->toContain('stored-paypal-secret')
        ->and($component->html())->not->toContain('stored-client-id')
        ->and(json_encode($component->snapshot))->not->toContain('stored-paypal-secret')
        ->and(json_encode($component->snapshot))->not->toContain('stored-client-id');
});
