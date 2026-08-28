<?php

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customers\Customer;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can render customers list page', function () {
    livewire(ListCustomers::class)
        ->assertOk();
});

test('can list customers in the table', function () {
    $customers = Customer::factory()->count(3)->create();

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers);
});

test('can create a customer via slide-over', function () {
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Customer::class, [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('create customer validates required fields', function () {
    $cases = [
        [['name' => null, 'email' => 'test@test.com'], ['name' => 'required']],
        [['name' => 'Test', 'email' => null], ['email' => 'required']],
        [['name' => 'Test', 'email' => 'not-email'], ['email' => 'email']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListCustomers::class)
            ->callAction(CreateAction::class, data: $data)
            ->assertHasFormErrors($errors);
    }
});

test('can edit a customer via table action', function () {
    $customer = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->callAction(TestAction::make('edit')->table($customer), data: [
            'name' => 'Updated Name',
            'email' => $customer->email,
        ])
        ->assertHasNoFormErrors();

    expect($customer->fresh()->name)->toBe('Updated Name');
});

test('can search customers by name', function () {
    $alice = Customer::factory()->create(['name' => 'Alice Baker']);
    $bob = Customer::factory()->create(['name' => 'Bob Smith']);

    livewire(ListCustomers::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$alice]))
        ->assertCanNotSeeTableRecords(collect([$bob]));
});

test('can render customer table columns', function () {
    Customer::factory()->create();

    livewire(ListCustomers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('phone')
        ->assertCanRenderTableColumn('orders_count');
});

test('can sort customers by name', function () {
    $alice = Customer::factory()->create(['name' => 'Alice']);
    $zach = Customer::factory()->create(['name' => 'Zach']);

    livewire(ListCustomers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('can filter customers with birthday this month', function () {
    $birthday = Customer::factory()->create(['birthday' => now()->format('Y-m-d')]);
    $noBirthday = Customer::factory()->create(['birthday' => null]);

    livewire(ListCustomers::class)
        ->filterTable('has_birthday_this_month')
        ->assertCanSeeTableRecords(collect([$birthday]))
        ->assertCanNotSeeTableRecords(collect([$noBirthday]));
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Customers\CustomerResource::getGloballySearchableAttributes())
        ->toBe(['name', 'email', 'phone']);
});

test('resource returns global search result title', function () {
    $customer = Customer::factory()->create(['name' => 'Alice Baker']);

    expect(App\Filament\Resources\Customers\CustomerResource::getGlobalSearchResultTitle($customer))
        ->toBe('Alice Baker');
});

test('resource returns global search result details', function () {
    $customer = Customer::factory()->create([
        'email' => 'alice@example.com',
        'phone' => '5550100',
    ]);

    $details = App\Filament\Resources\Customers\CustomerResource::getGlobalSearchResultDetails($customer);

    expect($details)
        ->toHaveKey('Email', 'alice@example.com')
        ->toHaveKey('Phone');
});

test('owner can bulk-delete selected customers via the AuthorizedDeleteBulkAction', function () {
    $kept = Customer::factory()->create();
    $doomed = Customer::factory()->count(2)->create();

    livewire(ListCustomers::class)
        ->selectTableRecords($doomed)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Customer::query()->count())->toBe(1)
        ->and(Customer::query()->find($kept->id))->not->toBeNull()
        ->and(Customer::query()->find($doomed->first()->id))->toBeNull();
});
