<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use JMac\Testing\Double;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('billing portal sends the owner to stripe and back to their bakery admin', function () {
    config(['tenancy.tenant_domain' => 'kneadit.test', 'app.url' => 'http://localhost']);

    $owner = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $owner->id]);

    $user = Double::for($owner)->passthru();
    $user->expects('redirectToBillingPortal')
        ->with('http://sunrise.kneadit.test/admin')
        ->returns(new RedirectResponse('https://billing.stripe.com/session/test'));
    $user->expects('hasStripeId')->returns(true);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');
});

test('billing portal returns to the plans page when the user owns no bakery', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('redirectToBillingPortal')
        ->with(route('billing.plans'))
        ->returns(new RedirectResponse('https://billing.stripe.com/session/test'));
    $user->expects('hasStripeId')->returns(true);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');
});

test('billing portal redirects users without a Stripe customer to plans', function () {
    $user = Double::for(User::factory()->owner()->create())->passthru();
    $user->expects('hasStripeId')->returns(false);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect(route('billing.plans'))
        ->assertSessionHas('error', 'No billing account found. Choose a plan to start billing.');
});

test('billing portal requires authentication', function () {
    $this->get(route('billing.portal'))
        ->assertRedirect(route('login'));
});
