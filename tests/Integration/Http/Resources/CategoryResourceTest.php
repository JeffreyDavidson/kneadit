<?php

use App\Http\Resources\CategoryResource;
use App\Models\Inventory\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('category resource exposes the expected JSON:API shape', function () {
    $category = Category::factory()->create();
    $resource = new CategoryResource($category);
    $request = request();

    expect($resource->toType($request))->toBe('categories')
        ->and($resource->toId($request))->toBe((string) $category->id);

    $attributes = $resource->toAttributes($request);

    expect($attributes)->toHaveKeys(['name', 'slug', 'description', 'sort_order']);
});
