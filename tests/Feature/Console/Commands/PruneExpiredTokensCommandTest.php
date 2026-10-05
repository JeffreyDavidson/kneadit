<?php

use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\ImpersonationToken;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
    Date::setTestNow('2026-10-04 12:00:00');
    createTenant(['id' => 'token-bakery', 'email' => 'tokens@example.com']);
});

test('it deletes tokens that expired more than seven days ago, consumed or not', function () {
    $staleImpersonation = ImpersonationToken::factory()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(8)]);
    $staleConsumedImpersonation = ImpersonationToken::factory()->consumed()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(8)]);
    $staleHandoff = BillingHandoffToken::factory()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(8)]);
    $staleConsumedHandoff = BillingHandoffToken::factory()->consumed()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(8)]);

    artisan('platform:prune-expired-tokens')->assertSuccessful();

    expect(ImpersonationToken::query()->whereKey([$staleImpersonation->id, $staleConsumedImpersonation->id])->exists())->toBeFalse()
        ->and(BillingHandoffToken::query()->whereKey([$staleHandoff->id, $staleConsumedHandoff->id])->exists())->toBeFalse();
});

test('it keeps tokens that expired recently or have not expired yet', function () {
    $recentImpersonation = ImpersonationToken::factory()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(6)]);
    $liveImpersonation = ImpersonationToken::factory()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->addHour()]);
    $recentHandoff = BillingHandoffToken::factory()->consumed()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->subDays(6)]);
    $liveHandoff = BillingHandoffToken::factory()->create(['tenant_id' => 'token-bakery', 'expires_at' => now()->addMinutes(2)]);

    artisan('platform:prune-expired-tokens')->assertSuccessful();

    expect(ImpersonationToken::query()->whereKey([$recentImpersonation->id, $liveImpersonation->id])->count())->toBe(2)
        ->and(BillingHandoffToken::query()->whereKey([$recentHandoff->id, $liveHandoff->id])->count())->toBe(2);
});

test('it is scheduled daily', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command, 'platform:prune-expired-tokens'));

    expect($event)->toBeInstanceOf(Event::class)
        ->and($event->expression)->toBe('30 4 * * *');
});
