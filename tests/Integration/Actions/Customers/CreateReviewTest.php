<?php

use App\Actions\Customers\CreateReview;
use App\Models\Engagement\Review;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates review from order with rating and comment', function () {
    $order = Order::factory()->create();

    $review = resolve(CreateReview::class)($order, 5, 'Amazing bread!');

    expect($review)->toBeInstanceOf(Review::class)
        ->and($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Amazing bread!')
        ->and($review->order_id)->toBe($order->id)
        ->and($review->is_approved)->toBeFalse();
});

test('stores the review photo without its GPS metadata', function () {
    Storage::fake('public');
    $order = Order::factory()->create();

    $review = resolve(CreateReview::class)($order, 5, photo: UploadedFile::fake()->createWithContent('loaf.jpg', jpegWithGpsExif()));

    $stored = Storage::disk('public')->path($review->photo_path);
    expect(exif_read_data($stored) ?: [])->not->toHaveKey('GPSLatitudeRef');
});

test('a second review for the same order updates the first instead of adding another', function () {
    $order = Order::factory()->create();
    $first = resolve(CreateReview::class)($order, 5, 'Amazing bread!');
    $first->forceFill(['is_approved' => true])->save();

    $second = resolve(CreateReview::class)($order, 3, 'Good, a bit dry.');

    expect(Review::query()->count())->toBe(1)
        ->and($second->is($first))->toBeTrue()
        ->and($second->rating)->toBe(3)
        ->and($second->comment)->toBe('Good, a bit dry.')
        ->and($second->is_approved)->toBeFalse();
});

test('updating a review keeps the existing photo unless a new one is sent', function () {
    Storage::fake('public');
    $order = Order::factory()->create();
    $first = resolve(CreateReview::class)($order, 5, photo: UploadedFile::fake()->image('one.jpg'));
    $firstPath = $first->photo_path;

    $kept = resolve(CreateReview::class)($order, 4);
    $replaced = resolve(CreateReview::class)($order, 4, photo: UploadedFile::fake()->image('two.jpg'));

    expect($kept->photo_path)->toBe($firstPath)
        ->and($replaced->photo_path)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($replaced->photo_path);
});

test('reviews for different orders stay separate', function () {
    resolve(CreateReview::class)(Order::factory()->create(), 5);
    resolve(CreateReview::class)(Order::factory()->create(), 4);

    expect(Review::query()->count())->toBe(2);
});
