<?php

use App\Actions\Operations\RegenerateWebhookSecret;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('regenerate writes a fresh 40-char secret to settings and returns it', function () {
    settings(['webhook_secret' => 'old-secret']);

    $newSecret = resolve(RegenerateWebhookSecret::class)();

    expect(strlen($newSecret))->toBe(40)
        ->and($newSecret)->not->toBe('old-secret')
        ->and(settings('webhook_secret'))->toBe($newSecret);
});

test('successive regenerate calls produce different secrets', function () {
    $a = resolve(RegenerateWebhookSecret::class)();
    $b = resolve(RegenerateWebhookSecret::class)();

    expect($a)->not->toBe($b);
});
