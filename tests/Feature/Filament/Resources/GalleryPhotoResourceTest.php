<?php

use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Models\Content\GalleryPhoto;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list gallery photos in the table', function () {
    $photos = GalleryPhoto::factory()->count(3)->create();

    livewire(ListGalleryPhotos::class)
        ->assertCanSeeTableRecords($photos);
});

test('can render gallery photo table columns', function () {
    GalleryPhoto::factory()->create();

    livewire(ListGalleryPhotos::class)
        ->assertCanRenderTableColumn('title')
        ->assertCanRenderTableColumn('category')
        ->assertCanRenderTableColumn('sort_order');
});

test('can edit a gallery photo via table action', function () {
    $photo = GalleryPhoto::factory()->create();

    livewire(ListGalleryPhotos::class)
        ->callAction(TestAction::make('edit')->table($photo), data: [
            'title' => 'Updated Photo Title',
            'image_path' => [$photo->image_path],
            'sort_order' => $photo->sort_order,
        ])
        ->assertHasNoFormErrors();

    expect($photo->fresh()->title)->toBe('Updated Photo Title');
});

test('can search gallery photos by title', function () {
    $target = GalleryPhoto::factory()->create(['title' => 'Sourdough Display']);
    $other = GalleryPhoto::factory()->create(['title' => 'Cookie Arrangement']);

    livewire(ListGalleryPhotos::class)
        ->searchTable('Sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('edit gallery photo validates title is required', function () {
    $photo = GalleryPhoto::factory()->create();

    livewire(ListGalleryPhotos::class)
        ->callAction(TestAction::make('edit')->table($photo), data: [
            'title' => null,
            'image_path' => [$photo->image_path],
            'sort_order' => $photo->sort_order,
        ])
        ->assertHasFormErrors(['title' => 'required']);
});
