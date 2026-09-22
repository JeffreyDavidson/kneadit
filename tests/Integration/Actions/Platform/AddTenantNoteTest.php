<?php

use App\Actions\Platform\AddTenantNote;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;

beforeEach(fn () => setUpCentralTest());

test('adds a note to a tenant', function () {
    $tenant = Tenant::factory()->create();

    $note = resolve(AddTenantNote::class)($tenant, 'Needs follow-up', 'Platform Admin');

    expect($note)
        ->toBeInstanceOf(TenantNote::class)
        ->tenant_id->toBe($tenant->id)
        ->body->toBe('Needs follow-up')
        ->author->toBe('Platform Admin');
});
