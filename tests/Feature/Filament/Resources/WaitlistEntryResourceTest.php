<?php

use App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use App\Models\Customers\WaitlistEntry;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list waitlist entries in the table', function () {
    $entries = WaitlistEntry::factory()->count(3)->create();

    livewire(ListWaitlistEntries::class)
        ->assertCanSeeTableRecords($entries);
});

test('can render waitlist entry table columns', function () {
    WaitlistEntry::factory()->create();

    livewire(ListWaitlistEntries::class)
        ->assertCanRenderTableColumn('customer_name')
        ->assertCanRenderTableColumn('customer_email')
        ->assertCanRenderTableColumn('requested_date')
        ->assertCanRenderTableColumn('status');
});

test('can search waitlist entries by customer name', function () {
    $target = WaitlistEntry::factory()->create(['customer_name' => 'Alice Baker']);
    $other = WaitlistEntry::factory()->create(['customer_name' => 'Bob Smith']);

    livewire(ListWaitlistEntries::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter waitlist entries by status', function () {
    $waiting = WaitlistEntry::factory()->waiting()->create();
    $notified = WaitlistEntry::factory()->notified()->create();

    livewire(ListWaitlistEntries::class)
        ->filterTable('status', App\Enums\Customers\WaitlistStatus::Waiting->value)
        ->assertCanSeeTableRecords(collect([$waiting]))
        ->assertCanNotSeeTableRecords(collect([$notified]));
});

test('can filter waitlist entries by requested date range', function () {
    $early = WaitlistEntry::factory()->create(['requested_date' => '2026-03-01']);
    $late = WaitlistEntry::factory()->create(['requested_date' => '2026-06-15']);

    livewire(ListWaitlistEntries::class)
        ->filterTable('requested_date', ['from' => '2026-06-01', 'until' => '2026-06-30'])
        ->assertCanSeeTableRecords(collect([$late]))
        ->assertCanNotSeeTableRecords(collect([$early]));
});

test('can create a waitlist entry via slide-over', function () {
    livewire(ListWaitlistEntries::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'requested_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => App\Enums\Customers\WaitlistStatus::Waiting->value,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(WaitlistEntry::class, [
        'customer_name' => 'Jane Doe',
    ]);
});

test('create waitlist entry validates required fields', function () {
    $cases = [
        [['customer_name' => null], ['customer_name' => 'required']],
        [['customer_email' => null], ['customer_email' => 'required']],
        [['requested_date' => null], ['requested_date' => 'required']],
        [['status' => null], ['status' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListWaitlistEntries::class)
            ->callAction(CreateAction::class, data: [
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'requested_date' => now()->addDay()->format('Y-m-d'),
                'status' => App\Enums\Customers\WaitlistStatus::Waiting->value,
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can sort waitlist entries by customer name', function () {
    $alice = WaitlistEntry::factory()->create(['customer_name' => 'Alice']);
    $zach = WaitlistEntry::factory()->create(['customer_name' => 'Zach']);

    livewire(ListWaitlistEntries::class)
        ->sortTable('customer_name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('customer_name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('navigation badge shows waiting entry count', function () {
    WaitlistEntry::factory()->waiting()->count(2)->create();
    WaitlistEntry::factory()->notified()->create();

    expect(App\Filament\Resources\WaitlistEntries\WaitlistEntryResource::getNavigationBadge())
        ->toBe('2');
});

test('navigation badge returns null when no waiting entries', function () {
    WaitlistEntry::factory()->notified()->create();

    expect(App\Filament\Resources\WaitlistEntries\WaitlistEntryResource::getNavigationBadge())
        ->toBeNull();
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\WaitlistEntries\WaitlistEntryResource::getGloballySearchableAttributes())
        ->toBe(['customer_email', 'customer_name']);
});

test('resource returns global search result title', function () {
    $entry = WaitlistEntry::factory()->create(['customer_name' => 'Alice Baker']);

    expect(App\Filament\Resources\WaitlistEntries\WaitlistEntryResource::getGlobalSearchResultTitle($entry))
        ->toBe('Alice Baker');
});

test('resource returns global search result details', function () {
    $entry = WaitlistEntry::factory()->create(['customer_email' => 'alice@example.com']);

    $details = App\Filament\Resources\WaitlistEntries\WaitlistEntryResource::getGlobalSearchResultDetails($entry);

    expect($details)
        ->toHaveKey('Email', 'alice@example.com')
        ->toHaveKey('Product');
});
