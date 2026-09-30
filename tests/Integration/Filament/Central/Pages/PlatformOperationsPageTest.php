<?php

use App\Events\Platform\WeeklyDigestRequested;
use App\Filament\Central\Pages\PlatformOperations;
use App\Models\Platform\PlatformSetting;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('page renders', function () {
    livewire(PlatformOperations::class)->assertOk();
});

test('catalog includes the expected commands', function () {
    $keys = collect((new PlatformOperations)->getCommands())->pluck('key')->all();

    expect($keys)->toContain(
        'health:check',
        'trial:check',
        'churn:check',
        'checkins:send',
        'digest:weekly',
        'platform:audit-free-forever',
    );
});

test('run invokes artisan command and stamps last run', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('health:check', [])
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('all checks passed');

    livewire(PlatformOperations::class)
        ->call('run', 'health:check')
        ->assertOk();

    expect(PlatformSetting::query()->where('key', 'last_run_health:check')->value('value'))
        ->not->toBeNull()
        ->and((new PlatformOperations)->getTaskStatus('health:check'))
        ->toMatchArray(['status' => 'succeeded', 'exit_code' => 0]);
});

test('weekly digest button sends outside the bakery-local Monday 08:00', function () {
    Event::fake([WeeklyDigestRequested::class]);
    runCommandsAsOneTenant();
    createTenant(['id' => 'test-bakery']);
    User::factory()->owner()->create();
    settings(['timezone' => 'America/New_York']);
    Date::setTestNow('2026-10-07 19:00');

    livewire(PlatformOperations::class)
        ->call('run', 'digest:weekly')
        ->assertOk();

    Event::assertDispatchedTimes(WeeklyDigestRequested::class, 1);
});

test('run rejects unknown commands', function () {
    livewire(PlatformOperations::class)
        ->call('run', 'platform:rm-rf-slash')
        ->assertOk();

    expect(PlatformSetting::query()->where('key', 'last_run_platform:rm-rf-slash')->exists())
        ->toBeFalse();
});
