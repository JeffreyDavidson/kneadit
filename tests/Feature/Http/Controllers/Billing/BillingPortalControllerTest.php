<?php

use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('billing portal redirects authenticated user to stripe portal', function () {
    $user = Mockery::mock(User::factory()->owner()->create())->makePartial();
    $user->shouldReceive('redirectToBillingPortal')
        ->once()
        ->with(route('filament.admin.pages.dashboard'))
        ->andReturn(new RedirectResponse('https://billing.stripe.com/session/test'));

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');
});

test('billing portal requires authentication', function () {
    $this->get(route('billing.portal'))
        ->assertRedirect(route('login'));
});
