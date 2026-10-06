<?php

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('the bulk extend adds 30 days to the current trial end instead of resetting it', function () {
    Date::setTestNow('2026-10-06 09:00');
    $tenant = Tenant::factory()->create(['id' => 'long-trial', 'trial_ends_at' => now()->addDays(90)]);

    livewire(ListTenants::class)
        ->selectTableRecords([$tenant->id])
        ->callAction(TestAction::make('extend_trial')->table()->bulk());

    expect($tenant->refresh()->trial_ends_at->toDateString())->toBe('2027-02-03');
});

test('the bulk extend resumes a bakery that was paused when its trial ended', function () {
    Date::setTestNow('2026-10-06 09:00');
    $tenant = Tenant::factory()->create([
        'id' => 'lapsed',
        'trial_ends_at' => now()->subDays(4),
        'paused_at' => now()->subDays(3),
    ]);

    livewire(ListTenants::class)
        ->selectTableRecords([$tenant->id])
        ->callAction(TestAction::make('extend_trial')->table()->bulk());

    $tenant->refresh();

    expect($tenant->paused_at)->toBeNull()
        ->and($tenant->trial_ends_at->toDateString())->toBe('2026-11-05');
});

test('Pause is the only on/off switch: there are no account activate or deactivate actions', function () {
    $page = livewire(ListTenants::class);

    $page
        ->assertTableBulkActionDoesNotExist('activate')
        ->assertTableBulkActionDoesNotExist('deactivate')
        ->assertTableBulkActionExists('pause')
        ->assertTableBulkActionExists('resume')
        ->assertTableColumnDoesNotExist('is_active')
        ->assertTableColumnExists('is_paused');

    expect($page->instance()->getTable()->getFilter('is_active'))->toBeNull();
});
