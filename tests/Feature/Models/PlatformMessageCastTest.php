<?php

use App\Enums\PlatformSenderType;
use App\Models\PlatformMessage;

beforeEach(fn () => setUpCentralTest());

it('casts sender_type to PlatformSenderType enum', function () {
    $message = PlatformMessage::query()->create([
        'tenant_id' => 't1',
        'sender_type' => 'admin',
        'subject' => 'Test',
        'body' => 'Body',
    ]);

    $message->refresh();

    expect($message->sender_type)->toBeInstanceOf(PlatformSenderType::class)
        ->and($message->sender_type)->toBe(PlatformSenderType::Admin);
});

it('scopes fromAdmin using the enum value', function () {
    PlatformMessage::query()->create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::query()->create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);

    $adminMessages = PlatformMessage::query()->fromAdmin()->get();

    expect($adminMessages)->toHaveCount(1)
        ->and($adminMessages->first()->sender_type)->toBe(PlatformSenderType::Admin);
});

it('scopes fromTenant using the enum value', function () {
    PlatformMessage::query()->create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
    PlatformMessage::query()->create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);

    $tenantMessages = PlatformMessage::query()->fromTenant()->get();

    expect($tenantMessages)->toHaveCount(1)
        ->and($tenantMessages->first()->sender_type)->toBe(PlatformSenderType::Tenant);
});
