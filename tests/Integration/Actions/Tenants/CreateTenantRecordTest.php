<?php

use App\Actions\Tenants\CreateTenantRecord;
use App\Enums\Platform\SubscriptionTier;
use App\Exceptions\Platform\UserAlreadyHasBakeryException;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Exceptions\DomainOccupiedByOtherTenantException;

/** A user whose first bakery lookup reports none, as a read taken before a competing request committed. */
#[Table(name: 'users')]
class StaleBakeryUser extends User
{
    private bool $firstLookupDone = false;

    #[Override]
    public function tenants(): HasMany
    {
        $relation = $this->hasMany(Tenant::class, 'user_id');

        if ($this->firstLookupDone) {
            return $relation;
        }

        $this->firstLookupDone = true;

        return $relation->whereRaw('1 = 0');
    }
}

beforeEach(function () {
    setUpCentralTest();

    test()->user = User::factory()->owner()->create([
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
    ]);
});

afterEach(function () {
    @unlink(testTenantDatabaseFile('recordbakery'));
    @unlink(testTenantDatabaseFile('recordownsite'));
    @unlink(testTenantDatabaseFile('recordsecond'));
});

it('creates an active starter tenant with a trial, contact details and a matching domain', function () {
    Date::setTestNow('2026-09-30 10:00');

    $tenant = resolve(CreateTenantRecord::class)(
        test()->user,
        'Record Bakery',
        'recordbakery',
        true,
        null,
    );

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->id)->toBe('recordbakery')
        ->and($tenant->name)->toBe('Test Baker')
        ->and($tenant->email)->toBe('baker@test.com')
        ->and($tenant->plan)->toBe(SubscriptionTier::Starter)
        ->and($tenant->store_name)->toBe('Record Bakery')
        ->and($tenant->is_active)->toBeTrue()
        ->and($tenant->trial_ends_at->toDateString())->toBe(now()->addDays(config('kneadit.trial_days', 30))->toDateString())
        ->and($tenant->domains->pluck('domain')->all())->toBe(['recordbakery']);
});

it('drops the external website when the KneadIt storefront is used and keeps it otherwise', function (bool $useKneadItStorefront, ?string $expected) {
    $subdomain = $useKneadItStorefront ? 'recordbakery' : 'recordownsite';

    $tenant = resolve(CreateTenantRecord::class)(
        test()->user,
        'Record Bakery',
        $subdomain,
        $useKneadItStorefront,
        'https://my-bakery.example.com',
    );

    expect($tenant->storefront_enabled)->toBe($useKneadItStorefront)
        ->and($tenant->external_website)->toBe($expected);
})->with([
    'kneadit storefront' => [true, null],
    'own website' => [false, 'https://my-bakery.example.com'],
]);

it('rolls back the tenant row when the domain is already taken', function () {
    createTenantWithDomain('squatter', 'recordbakery');

    expect(fn () => resolve(CreateTenantRecord::class)(test()->user, 'Record Bakery', 'recordbakery', true, null))
        ->toThrow(DomainOccupiedByOtherTenantException::class)
        ->and(Tenant::query()->whereKey('recordbakery')->exists())->toBeFalse();
});

it('stores the owner in the user_id column rather than the data json', function () {
    $tenant = resolve(CreateTenantRecord::class)(test()->user, 'Record Bakery', 'recordbakery', true, null);

    expect(DB::table('tenants')->where('id', 'recordbakery')->value('user_id'))->toBe(test()->user->id)
        ->and($tenant->data)->not->toHaveKey('user_id')
        ->and(test()->user->tenants()->pluck('id')->all())->toBe(['recordbakery']);
});

it('refuses a second bakery for the same owner and creates nothing', function () {
    resolve(CreateTenantRecord::class)(test()->user, 'Record Bakery', 'recordbakery', true, null);

    expect(fn () => resolve(CreateTenantRecord::class)(test()->user, 'Second', 'recordsecond', true, null))
        ->toThrow(UserAlreadyHasBakeryException::class)
        ->and(Tenant::query()->count())->toBe(1)
        ->and(DB::table('domains')->count())->toBe(1);
});

it('treats the unique index as the owner already having a bakery when a competing request wins the race', function () {
    // The other request committed its bakery, but this request's "does the owner have one?" read was stale.
    createTenant(['id' => 'recordwinner', 'user_id' => test()->user->id]);
    $staleUser = (new StaleBakeryUser)->setRawAttributes(test()->user->getAttributes(), true);
    $staleUser->exists = true;

    expect(fn () => resolve(CreateTenantRecord::class)($staleUser, 'Second', 'recordsecond', true, null))
        ->toThrow(UserAlreadyHasBakeryException::class)
        ->and(Tenant::query()->pluck('id')->all())->toBe(['recordwinner'])
        ->and(DB::table('domains')->count())->toBe(0)
        ->and(file_exists(testTenantDatabaseFile('tenantrecordsecond')))->toBeFalse();
});

it('rethrows a unique violation that is not about the owner', function () {
    createTenantWithDomain('recordsecond');

    expect(fn () => resolve(CreateTenantRecord::class)(test()->user, 'Second', 'recordsecond', true, null))
        ->toThrow(UniqueConstraintViolationException::class);
});
