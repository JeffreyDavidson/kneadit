<?php

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list reviews in the table', function () {
    $reviews = Review::factory()->count(3)->create();

    Livewire::test(ListReviews::class)
        ->assertCanSeeTableRecords($reviews);
});

test('can create a review via slide-over', function () {
    Livewire::test(ListReviews::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Happy Customer',
            'customer_email' => 'happy@example.com',
            'rating' => 5,
            'comment' => 'Best bread ever!',
            'is_approved' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Review::class, [
        'customer_name' => 'Happy Customer',
        'rating' => 5,
    ]);
});
