<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Http\RedirectResponse;

/**
 * Stands in for the signed-in user so the controller's Stripe calls are answered
 * locally: it reports whether a Stripe customer exists and records the return URL
 * the portal was asked for, without reaching Stripe.
 */
#[Table(name: 'users')]
final class BillingPortalUser extends User
{
    public bool $fakeHasStripeId = true;

    public ?string $portalReturnUrl = null;

    #[Override]
    public function getForeignKey(): string
    {
        return 'user_id';
    }

    #[Override]
    public function hasStripeId(): bool
    {
        return $this->fakeHasStripeId;
    }

    /** @param  array<string, mixed>  $options */
    #[Override]
    public function redirectToBillingPortal(?string $returnUrl = null, array $options = []): RedirectResponse
    {
        $this->portalReturnUrl = $returnUrl;

        return new RedirectResponse('https://billing.stripe.com/session/test');
    }
}

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('billing portal sends the owner to stripe and back to their bakery admin', function () {
    config(['tenancy.tenant_domain' => 'kneadit.test', 'app.url' => 'http://localhost']);
    $owner = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $owner->id]);
    $user = BillingPortalUser::query()->findOrFail($owner->id);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');

    expect($user->portalReturnUrl)->toBe('http://sunrise.kneadit.test/admin');
});

test('billing portal returns to the plans page when the user owns no bakery', function () {
    $user = BillingPortalUser::query()->findOrFail(User::factory()->owner()->create()->id);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://billing.stripe.com/session/test');

    expect($user->portalReturnUrl)->toBe(route('billing.plans'));
});

test('billing portal redirects users without a Stripe customer to plans', function () {
    $user = BillingPortalUser::query()->findOrFail(User::factory()->owner()->create()->id);
    $user->fakeHasStripeId = false;

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect(route('billing.plans'))
        ->assertSessionHas('error', 'No billing account found. Choose a plan to start billing.');

    expect($user->portalReturnUrl)->toBeNull();
});

test('billing portal requires authentication', function () {
    $this->get(route('billing.portal'))
        ->assertRedirect(route('login'));
});
