<?php

use App\Actions\Platform\CreateBillingHandoffToken;
use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    setUpCentralTest();

    config(['app.url' => 'http://kneadit.test:8000', 'tenancy.tenant_domain' => 'kneadit.test']);
});

test('creates a hashed single-use token for the bakery and its owner', function () {
    Date::setTestNow('2026-10-05 12:00');
    $owner = User::factory()->owner()->create();
    $tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $owner->id]);

    $url = resolve(CreateBillingHandoffToken::class)($tenant);

    $token = basename(parse_url($url, PHP_URL_PATH));
    $record = BillingHandoffToken::query()->sole();

    expect($url)->toStartWith('http://kneadit.test:8000/billing/handoff/')
        ->and($token)->toHaveLength(64)
        ->and($record->token_hash)->toBe(hash('sha256', $token))
        ->and($record->token_hash)->not->toBe($token)
        ->and($record->tenant_id)->toBe('sunrise')
        ->and($record->user_id)->toBe($owner->id)
        ->and($record->consumed_at)->toBeNull()
        ->and($record->expires_at->toDateTimeString())->toBe('2026-10-05 12:02:00');
});

test('refuses a bakery with no linked owner and stores nothing', function () {
    $tenant = Tenant::factory()->create(['user_id' => null]);

    expect(fn () => resolve(CreateBillingHandoffToken::class)($tenant))->toThrow(HttpException::class)
        ->and(BillingHandoffToken::query()->count())->toBe(0);
});
