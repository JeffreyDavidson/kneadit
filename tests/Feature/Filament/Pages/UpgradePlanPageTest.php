<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Pages\Platform\UpgradePlan;
use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Livewire\livewire;

/** Enter a bakery whose central owner is the given user, as the signed-in tenant user. */
function enterBakery(User $signedIn, ?int $ownerId): Tenant
{
    $tenant = Tenant::factory()->create([
        'id' => 'upgrade-test',
        'plan' => SubscriptionTier::Starter,
        'user_id' => $ownerId,
    ]);

    test()->actingAs($signedIn);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);

    return $tenant;
}

beforeEach(function () {
    setUpCentralTest();

    config(['app.url' => 'http://kneadit.test:8000', 'tenancy.tenant_domain' => 'kneadit.test']);
});

test('upgrade plan page renders for manager', function () {
    $manager = User::factory()->manager()->create();
    enterBakery($manager, User::factory()->owner()->create()->id);

    livewire(UpgradePlan::class)->assertOk();
});

test('the owner sees Manage billing and clicking it hands off to central billing with a hashed token', function () {
    $owner = User::factory()->owner()->create();
    enterBakery($owner, $owner->id);

    livewire(UpgradePlan::class)
        ->assertActionVisible('manageBilling')
        ->callAction('manageBilling')
        ->assertRedirectContains('http://kneadit.test:8000/billing/handoff/');

    $record = BillingHandoffToken::query()->sole();

    expect($record->tenant_id)->toBe('upgrade-test')
        ->and($record->user_id)->toBe($owner->id)
        ->and($record->consumed_at)->toBeNull()
        ->and($record->expires_at->isFuture())->toBeTrue();
});

test('the handoff token in the redirect is stored only as a hash', function () {
    $owner = User::factory()->owner()->create();
    enterBakery($owner, $owner->id);

    $component = livewire(UpgradePlan::class)->callAction('manageBilling');

    $token = basename((string) parse_url($component->effects['redirect'], PHP_URL_PATH));

    expect(BillingHandoffToken::query()->where('token_hash', hash('sha256', $token))->exists())->toBeTrue()
        ->and(BillingHandoffToken::query()->where('token_hash', $token)->exists())->toBeFalse();
});

test('a manager does not see Manage billing and cannot run it', function () {
    enterBakery(User::factory()->manager()->create(), User::factory()->owner()->create()->id);

    livewire(UpgradePlan::class)
        ->assertActionHidden('manageBilling')
        ->call('mountAction', 'manageBilling')
        ->assertNoRedirect();

    expect(BillingHandoffToken::query()->count())->toBe(0);
});

test('staff cannot open the upgrade page at all', function () {
    enterBakery(User::factory()->staff()->create(), User::factory()->owner()->create()->id);

    livewire(UpgradePlan::class)->assertForbidden();

    expect(BillingHandoffToken::query()->count())->toBe(0);
});

test('a bakery with no linked owner has no Manage billing button and creates no token', function () {
    $owner = User::factory()->owner()->create();
    enterBakery($owner, null);

    livewire(UpgradePlan::class)
        ->assertActionHidden('manageBilling')
        ->call('mountAction', 'manageBilling')
        ->assertNoRedirect();

    expect(BillingHandoffToken::query()->count())->toBe(0);
});
