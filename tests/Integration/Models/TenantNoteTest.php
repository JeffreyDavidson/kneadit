<?php

use App\Models\TenantNote;

beforeEach(fn () => setUpCentralTest());

test('can create note', function () {
    $note = TenantNote::query()->create([
        'tenant_id' => 'tenant-1',
        'body' => 'This tenant needs attention',
        'author' => 'Admin Joe',
    ]);

    $found = TenantNote::query()->where('tenant_id', 'tenant-1')->first();
    expect($found)->not->toBeNull()->and($found->body)->toBe('This tenant needs attention')->and($found->author)->toBe('Admin Joe');
});
