<?php

use App\Actions\Platform\CreateImpersonationToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use JMac\Testing\Double;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('platform admin can impersonate a tenant', function () {
    $user = User::factory()->platformAdmin()->create();
    $tenant = Tenant::factory()->create();

    $action = Double::for(CreateImpersonationToken::class);
    $action->expects('__invoke')
        ->returns('https://test-bakery.kneadit.test/impersonate/token123');

    app()->instance(CreateImpersonationToken::class, $action);

    $this->actingAs($user)
        ->get(URL::signedRoute('tenant.impersonate', ['tenant' => $tenant->id]))
        ->assertRedirect('https://test-bakery.kneadit.test/impersonate/token123');
});

test('non-admin user is forbidden from impersonating', function () {
    $user = User::factory()->owner()->create();
    $tenant = Tenant::factory()->create();

    $this->actingAs($user)
        ->get(URL::signedRoute('tenant.impersonate', ['tenant' => $tenant->id]))
        ->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    $tenant = Tenant::factory()->create();

    $this->get(URL::signedRoute('tenant.impersonate', ['tenant' => $tenant->id]))
        ->assertRedirect('/login');
});
