<?php

use App\Actions\Platform\DeleteTenantNote;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;

beforeEach(fn () => setUpCentralTest());

test('deletes only the requested note belonging to the tenant', function () {
    $tenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();
    $note = TenantNote::factory()->create(['tenant_id' => $tenant->id]);
    $otherNote = TenantNote::factory()->create(['tenant_id' => $otherTenant->id]);

    resolve(DeleteTenantNote::class)($tenant, $note->id);

    expect(TenantNote::query()->find($note->id))->toBeNull()
        ->and(TenantNote::query()->find($otherNote->id))->not->toBeNull();
});
