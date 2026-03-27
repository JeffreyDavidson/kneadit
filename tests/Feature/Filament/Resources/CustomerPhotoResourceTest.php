<?php

use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Models\CustomerPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list customer photos in the table', function () {
    $photos = CustomerPhoto::factory()->count(3)->create();

    Livewire::test(ListCustomerPhotos::class)
        ->assertCanSeeTableRecords($photos);
});

test('can render customer photo table columns', function (string $column) {
    CustomerPhoto::factory()->create();

    Livewire::test(ListCustomerPhotos::class)
        ->assertCanRenderTableColumn($column);
})->with(['customer_name', 'caption']);

test('can search customer photos by customer name', function () {
    $target = CustomerPhoto::factory()->create(['customer_name' => 'Alice Baker']);
    $other = CustomerPhoto::factory()->create(['customer_name' => 'Bob Smith']);

    Livewire::test(ListCustomerPhotos::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});
