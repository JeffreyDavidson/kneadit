<?php

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('category resource returns correct structure', function () {
    $category = Category::factory()->create();
    $resource = new CategoryResource($category);
    $data = $resource->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'slug', 'description', 'sort_order']);
});
