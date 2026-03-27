<?php

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can render customers list page', function () {
    Livewire::test(ListCustomers::class)
        ->assertOk();
});

test('can create a customer via slide-over', function () {
    Livewire::test(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Customer::class, [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('create customer validates required fields', function (array $data, array $errors) {
    Livewire::test(ListCustomers::class)
        ->callAction(CreateAction::class, data: $data)
        ->assertHasActionErrors($errors);
})->with([
    'name is required' => [['name' => null, 'email' => 'test@test.com'], ['name' => 'required']],
    'email is required' => [['name' => 'Test', 'email' => null], ['email' => 'required']],
    'email must be valid' => [['name' => 'Test', 'email' => 'not-email'], ['email' => 'email']],
]);

test('can edit a customer via table action', function () {
    $customer = Customer::factory()->create();

    Livewire::test(ListCustomers::class)
        ->callTableAction('edit', $customer, data: [
            'name' => 'Updated Name',
            'email' => $customer->email,
        ])
        ->assertHasNoTableActionErrors();

    expect($customer->fresh()->name)->toBe('Updated Name');
});

test('can search customers by name', function () {
    $alice = Customer::factory()->create(['name' => 'Alice Baker']);
    $bob = Customer::factory()->create(['name' => 'Bob Smith']);

    Livewire::test(ListCustomers::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$alice]))
        ->assertCanNotSeeTableRecords(collect([$bob]));
});
