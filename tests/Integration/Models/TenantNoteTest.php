<?php

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;

beforeEach(fn () => setUpCentralTest());

test('can create note', function () {
    $note = TenantNote::factory()->create([
        'tenant_id' => 'tenant-1',
        'body' => 'This tenant needs attention',
        'author' => 'Admin Joe',
    ]);

    expect($note)->not->toBeNull()->and($note->body)->toBe('This tenant needs attention')->and($note->author)->toBe('Admin Joe');
});

test('note belongs to tenant', function () {
    $tenantRow = createTenant(['id' => 'note-tenant', 'email' => 'note@test.com']);

    $note = TenantNote::factory()->create([
        'tenant_id' => 'note-tenant',
        'body' => 'Test note',
        'author' => 'Admin',
    ]);

    expect($note->tenant)->toBeInstanceOf(Tenant::class)
        ->and($note->tenant->id)->toBe('note-tenant');
});
