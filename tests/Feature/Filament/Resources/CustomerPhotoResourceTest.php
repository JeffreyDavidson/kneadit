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
