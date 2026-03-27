<?php

use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Models\GalleryPhoto;
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

test('can list gallery photos in the table', function () {
    $photos = GalleryPhoto::factory()->count(3)->create();

    Livewire::test(ListGalleryPhotos::class)
        ->assertCanSeeTableRecords($photos);
});
