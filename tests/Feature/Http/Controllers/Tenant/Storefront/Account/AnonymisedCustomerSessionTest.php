<?php

use App\Actions\Customers\AnonymiseCustomer;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('a session signed in before the customer was anonymised is signed out afterwards', function () {
    $customer = Customer::factory()->verified()->withPassword('old-password-1')->create(['email' => 'jane@example.com']);

    withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => 'jane@example.com',
            'password' => 'old-password-1',
            'remember' => '1',
        ])
        ->assertRedirect(route('account.dashboard', [], false));
    $session = session()->all();
    test()->flushSession();
    auth()->forgetGuards();

    resolve(AnonymiseCustomer::class)($customer->fresh());

    withoutMiddleware(tenantMiddleware())
        ->withSession($session)
        ->get(route('account.dashboard', [], false))
        ->assertRedirect(route('account.login.show', [], false));
});
