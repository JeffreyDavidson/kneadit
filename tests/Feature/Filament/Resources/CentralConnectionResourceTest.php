<?php

use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Models\Staff\User;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('BlogPosts list page can render', function () {
    livewire(ListBlogPosts::class)
        ->assertOk();
});
