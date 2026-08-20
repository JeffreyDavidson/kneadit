<?php

use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use JMac\Testing\Double;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('billing portal redirects authenticated user to stripe portal', function () {
    $realUser = User::factory()->owner()->create();
    $user = Double::for(User::class);
    $user->allows('getAuthIdentifier')->returns($realUser->getAuthIdentifier());
    $user->allows('getAuthIdentifierName')->returns($realUser->getAuthIdentifierName());
    $user->allows('getAuthPassword')->returns($realUser->getAuthPassword());
    $user->allows('getAuthPasswordName')->returns($realUser->getAuthPasswordName());
    $user->allows('getRememberToken')->returns($realUser->getRememberToken());
    $user->expects('redirectToBillingPortal')
        ->with(route('filament.admin.pages.dashboard'))
        ->returns(new RedirectResponse('https://billing.stripe.com/session/test'));

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');
});

test('billing portal requires authentication', function () {
    $this->get(route('billing.portal'))
        ->assertRedirect(route('login'));
});
