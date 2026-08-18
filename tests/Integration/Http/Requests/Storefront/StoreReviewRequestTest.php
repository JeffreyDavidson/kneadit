<?php

use App\Http\Requests\Storefront\StoreReviewRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('rating is required', function () {
    $validator = validator([], (new StoreReviewRequest)->rules());

    expect($validator->errors()->has('rating'))->toBeTrue();
});

test('rating must be between 1 and 5', function (int $rating) {
    $validator = validator(['rating' => $rating], (new StoreReviewRequest)->rules());

    expect($validator->errors()->has('rating'))->toBeTrue();
})->with([0, 6, -1, 100]);

test('valid rating with no comment passes', function () {
    $validator = validator(['rating' => 4], (new StoreReviewRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('comment over 2000 chars fails', function () {
    $validator = validator([
        'rating' => 5,
        'comment' => str_repeat('a', 2001),
    ], (new StoreReviewRequest)->rules());

    expect($validator->errors()->has('comment'))->toBeTrue();
});

test('photo must be an allowed image mime', function () {
    $validator = validator([
        'rating' => 5,
        'photo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ], (new StoreReviewRequest)->rules());

    expect($validator->errors()->has('photo'))->toBeTrue();
});

test('photo over 5MB fails', function () {
    $validator = validator([
        'rating' => 5,
        'photo' => UploadedFile::fake()->image('big.jpg')->size(5121),
    ], (new StoreReviewRequest)->rules());

    expect($validator->errors()->has('photo'))->toBeTrue();
});
