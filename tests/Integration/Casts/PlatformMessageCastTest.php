<?php

use App\Enums\Platform\PlatformSenderType;
use App\Models\Platform\PlatformMessage;

beforeEach(fn () => setUpCentralTest());

it('casts sender_type to PlatformSenderType enum', function () {
    $message = PlatformMessage::factory()->fromAdmin()->create([
        'tenant_id' => 't1',
    ]);

    $message->refresh();

    expect($message->sender_type)->toBeInstanceOf(PlatformSenderType::class)
        ->and($message->sender_type)->toBe(PlatformSenderType::Admin);
});

it('scopes fromAdmin using the enum value', function () {
    PlatformMessage::factory()->fromAdmin()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1']);

    $adminMessages = PlatformMessage::query()->fromAdmin()->get();

    expect($adminMessages)->toHaveCount(1)
        ->and($adminMessages->firstOrFail()->sender_type)->toBe(PlatformSenderType::Admin);
});

it('scopes fromTenant using the enum value', function () {
    PlatformMessage::factory()->fromAdmin()->create(['tenant_id' => 't1']);
    PlatformMessage::factory()->fromTenant()->create(['tenant_id' => 't1']);

    $tenantMessages = PlatformMessage::query()->fromTenant()->get();

    expect($tenantMessages)->toHaveCount(1)
        ->and($tenantMessages->firstOrFail()->sender_type)->toBe(PlatformSenderType::Tenant);
});
