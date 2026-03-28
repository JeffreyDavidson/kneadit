<?php

use App\Models\Category;
use App\View\Components\Home\Categories;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads active categories sorted by sort order', function () {
    Category::factory()->count(3)->create();
    Category::factory()->inactive()->create();

    $component = new Categories;

    expect($component->categories)->toHaveCount(3);
});
