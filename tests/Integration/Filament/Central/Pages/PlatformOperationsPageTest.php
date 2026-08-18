<?php

use App\Filament\Central\Pages\PlatformOperations;
use App\Models\Platform\PlatformSetting;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('page renders', function () {
    Livewire::test(PlatformOperations::class)->assertOk();
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
        ->with('health:check')
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('all checks passed');

    Livewire::test(PlatformOperations::class)
        ->call('run', 'health:check')
        ->assertOk();

    expect(PlatformSetting::query()->where('key', 'last_run_health:check')->value('value'))
        ->not->toBeNull();
});

test('run rejects unknown commands', function () {
    Livewire::test(PlatformOperations::class)
        ->call('run', 'platform:rm-rf-slash')
        ->assertOk();

    expect(PlatformSetting::query()->where('key', 'last_run_platform:rm-rf-slash')->exists())
        ->toBeFalse();
});
