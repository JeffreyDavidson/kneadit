<?php

use App\Enums\Content\BlogPostCategory;
use App\Http\Requests\Api\IndexBlogPostsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('category is optional', function () {
    $validator = validator([], (new IndexBlogPostsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('"all" sentinel category is allowed', function () {
    $validator = validator(['category' => 'all'], (new IndexBlogPostsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('any defined enum case is allowed', function () {
    $first = BlogPostCategory::cases()[0]->value;

    $validator = validator(['category' => $first], (new IndexBlogPostsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('unknown category is rejected', function () {
    $validator = validator(['category' => 'made-up-category'], (new IndexBlogPostsRequest)->rules());

    expect($validator->errors()->has('category'))->toBeTrue();
});
