<?php

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
