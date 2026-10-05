<?php

use App\Actions\Platform\CreateBillingHandoffToken;
use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config([
        'tenancy.central_domains' => ['localhost', 'kneadit.test'],
        'tenancy.tenant_domain' => 'kneadit.test',
        'app.url' => 'http://localhost',
    ]);

    test()->owner = User::factory()->owner()->create();
    test()->tenant = Tenant::factory()->create([
        'id' => 'sunrise',
        'name' => 'Sunrise Bakery',
        'store_name' => 'Sunrise Bakery',
        'user_id' => test()->owner->id,
    ]);
});

test('a handoff link signs the bakery owner in on the central site and lands on the plans page', function () {
    $url = resolve(CreateBillingHandoffToken::class)(test()->tenant);

    get($url)->assertRedirect(route('billing.plans'));

    assertAuthenticatedAs(test()->owner);

    get(route('billing.plans'))
        ->assertOk()
        ->assertSee('Sunrise Bakery');
});

test('the same link cannot be used twice', function () {
    $url = resolve(CreateBillingHandoffToken::class)(test()->tenant);

    get($url)->assertRedirect(route('billing.plans'));
    auth()->logout();

    get($url)->assertForbidden();

    assertGuest();
});

test('an expired link is refused', function () {
    BillingHandoffToken::factory()
        ->forToken('expired-token')
        ->expired()
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    get(route('billing.handoff', 'expired-token'))->assertForbidden();

    assertGuest();
});

test('a link for one bakery signs in that bakery owner, never another', function () {
    $otherOwner = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'moonrise', 'user_id' => $otherOwner->id]);

    get(resolve(CreateBillingHandoffToken::class)(test()->tenant));

    assertAuthenticatedAs(test()->owner);
});

test('a refused link for a known bakery offers a way back to its admin', function () {
    BillingHandoffToken::factory()
        ->forToken('used-token')
        ->consumed()
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    get(route('billing.handoff', 'used-token'))->assertForbidden()->assertSeeHtml('http://sunrise.kneadit.test/admin')
        ->assertSee('Back to your bakery');
});

test('a refused link for an unknown token still renders a page', function () {
    get(route('billing.handoff', 'never-issued'))
        ->assertForbidden()
        ->assertDontSee('Back to your bakery');
});

test('a link for a bakery that has no linked owner is refused', function () {
    test()->tenant->update(['user_id' => null]);

    BillingHandoffToken::factory()
        ->forToken('orphaned')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    get(route('billing.handoff', 'orphaned'))->assertForbidden();

    assertGuest();
});

test('the handoff route is throttled', function () {
    foreach (range(1, 5) as $attempt) {
        get(route('billing.handoff', "guess-{$attempt}"))->assertForbidden();
    }

    get(route('billing.handoff', 'guess-6'))->assertTooManyRequests();
});

test('the handoff route does not exist on a bakery host', function () {
    test()->tenant->createDomain(['domain' => 'sunrise']);
    $url = resolve(CreateBillingHandoffToken::class)(test()->tenant);

    get('http://sunrise.kneadit.test'.parse_url($url, PHP_URL_PATH))->assertNotFound();

    assertGuest();
});
