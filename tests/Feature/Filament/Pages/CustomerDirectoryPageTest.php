<?php

use App\Filament\Pages\CustomerDirectory;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('customer directory renders with customer data', function () {
    Customer::factory()->count(3)->create();

    Livewire::test(CustomerDirectory::class)
        ->assertOk()
        ->assertSee('Customer');
});
