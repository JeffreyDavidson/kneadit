<?php

use App\Models\Engagement\Review;
use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

const REVIEW_MIGRATIONS = [
    '2026_10_07_120000_add_unique_order_index_to_reviews_table.php',
    '2026_10_07_120100_restore_rating_check_on_reviews_table.php',
    '2026_10_07_120200_add_low_rating_alerted_at_to_reviews_table.php',
];

function runReviewMigration(string $file): void
{
    $migration = require database_path("migrations/tenant/{$file}");

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function runReviewMigrations(): void
{
    foreach (REVIEW_MIGRATIONS as $file) {
        runReviewMigration($file);
    }
}

/**
 * Puts reviews back the way every bakery had it before these migrations: no
 * rating CHECK, no unique order index and no alert column.
 */
function restoreLegacyReviewsTable(): void
{
    DB::statement('DROP TABLE "reviews"');
    DB::statement(
        'CREATE TABLE "reviews" ("id" integer primary key autoincrement not null, "customer_name" varchar not null, '
        .'"customer_email" varchar not null, "product_id" integer, "rating" integer not null, "comment" text, '
        .'"is_approved" tinyint(1) not null default (\'0\'), "is_featured" tinyint(1) not null default (\'0\'), '
        .'"created_at" datetime, "updated_at" datetime, "order_id" integer, "photo_path" varchar, '
        .'foreign key("product_id") references products("id") on delete cascade on update no action, '
        .'foreign key("order_id") references "orders"("id") on delete set null)',
    );
    DB::statement('CREATE INDEX "reviews_is_approved_index" on "reviews" ("is_approved")');
    DB::statement('CREATE INDEX "reviews_rating_index" on "reviews" ("rating")');
    DB::statement('CREATE INDEX "reviews_product_id_index" on "reviews" ("product_id")');
}

/** @return array<string, string> */
function reviewsSchemaSnapshot(): array
{
    return DB::table('sqlite_master')
        ->whereNotNull('sql')
        ->orderBy('name')
        ->pluck('sql', 'name')
        ->all();
}

/** @return list<array<string, mixed>> */
function reviewRows(): array
{
    return DB::table('reviews')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all();
}

/** @param array<string, mixed> $attributes */
function insertLegacyReview(array $attributes): int
{
    return DB::table('reviews')->insertGetId([
        'customer_name' => 'Alice',
        'customer_email' => 'alice@example.com',
        'rating' => 4,
        'comment' => 'Lovely',
        'created_at' => '2026-09-01 10:00:00',
        'updated_at' => '2026-09-01 10:00:00',
        ...$attributes,
    ]);
}

test('a migrated bakery database enforces the rating range, one review per order and has the alert column', function () {
    $order = Order::factory()->create();
    Review::factory()->for($order)->create(['product_id' => null]);

    expect(fn () => Review::factory()->for($order)->create(['product_id' => null]))->toThrow(QueryException::class)
        ->and(fn () => Review::factory()->create(['rating' => 6]))->toThrow(QueryException::class)
        ->and(fn () => Review::factory()->create(['rating' => 0]))->toThrow(QueryException::class)
        ->and(Schema::hasColumn('reviews', 'low_rating_alerted_at'))->toBeTrue();
});

test('duplicate reviews of an order keep the newest linked and unlink the older ones without deleting any', function () {
    restoreLegacyReviewsTable();
    $order = Order::factory()->create();
    $otherOrder = Order::factory()->create();
    $oldest = insertLegacyReview(['order_id' => $order->id, 'rating' => 1]);
    $older = insertLegacyReview(['order_id' => $order->id, 'rating' => 2]);
    $newest = insertLegacyReview(['order_id' => $order->id, 'rating' => 5]);
    $single = insertLegacyReview(['order_id' => $otherOrder->id]);
    insertLegacyReview(['order_id' => null]);
    insertLegacyReview(['order_id' => null]);
    $before = reviewRows();

    runReviewMigration(REVIEW_MIGRATIONS[0]);

    $after = collect(reviewRows())->keyBy('id');

    expect($after)->toHaveCount(6)
        ->and($after[$newest]['order_id'])->toBe($order->id)
        ->and($after[$single]['order_id'])->toBe($otherOrder->id)
        ->and($after[$oldest]['order_id'])->toBeNull()
        ->and($after[$older]['order_id'])->toBeNull()
        ->and(collect($before)->map(fn (array $row): array => [...$row, 'order_id' => null])->all())
        ->toEqual($after->map(fn (array $row): array => [...$row, 'order_id' => null])->values()->all())
        ->and(Schema::hasIndex('reviews', ['order_id'], 'unique'))->toBeTrue();
});

test('reviews with no order are not limited by the unique index', function () {
    restoreLegacyReviewsTable();
    runReviewMigrations();

    insertLegacyReview(['order_id' => null]);
    insertLegacyReview(['order_id' => null]);

    expect(DB::table('reviews')->whereNull('order_id')->count())->toBe(2);
});

test('a second review for the same order is rejected after the migration', function () {
    restoreLegacyReviewsTable();
    $order = Order::factory()->create();
    insertLegacyReview(['order_id' => $order->id]);

    runReviewMigrations();

    expect(fn () => insertLegacyReview(['order_id' => $order->id]))->toThrow(QueryException::class);
});

dataset('ratingsOutsideTheRange', [
    'zero' => [0, 1],
    'negative' => [-3, 1],
    'six' => [6, 5],
    'far too high' => [50, 5],
    'in range' => [3, 3],
]);

test('ratings outside 1 to 5 are clamped, not dropped', function (int $stored, int $clamped) {
    restoreLegacyReviewsTable();
    $id = insertLegacyReview(['rating' => $stored]);

    runReviewMigrations();

    expect(DB::table('reviews')->where('id', $id)->value('rating'))->toBe($clamped)
        ->and(DB::table('reviews')->count())->toBe(1);
})->with('ratingsOutsideTheRange');

dataset('invalidRatings', [0, 6, -1]);

test('the restored check rejects ratings outside 1 to 5', function (int $rating) {
    restoreLegacyReviewsTable();
    runReviewMigrations();

    expect(fn () => insertLegacyReview(['rating' => $rating]))->toThrow(QueryException::class);
})->with('invalidRatings');

test('rebuilding reviews keeps every row, column, index, foreign key and other table definition', function () {
    restoreLegacyReviewsTable();
    $order = Order::factory()->create();
    insertLegacyReview(['order_id' => $order->id, 'photo_path' => 'review-photos/a.jpg', 'is_approved' => true, 'is_featured' => true]);
    insertLegacyReview(['order_id' => null, 'comment' => null]);
    $rowsBefore = reviewRows();
    $foreignKeysBefore = Schema::getForeignKeys('reviews');
    runReviewMigration(REVIEW_MIGRATIONS[0]);
    $before = reviewsSchemaSnapshot();

    runReviewMigration(REVIEW_MIGRATIONS[1]);

    $after = reviewsSchemaSnapshot();

    expect(array_diff_key($after, ['reviews' => true]))->toEqual(array_diff_key($before, ['reviews' => true]))
        ->and($after['reviews'])->toBe(str_replace(
            '"rating" integer not null,',
            '"rating" integer not null check ("rating" between 1 and 5),',
            $before['reviews'],
        ))
        ->and(Schema::getForeignKeys('reviews'))->toEqual($foreignKeysBefore)
        ->and(reviewRows())->toEqual($rowsBefore);
});

test('running the migrations again is harmless', function () {
    $before = reviewsSchemaSnapshot();

    runReviewMigrations();

    expect(reviewsSchemaSnapshot())->toEqual($before);
});
