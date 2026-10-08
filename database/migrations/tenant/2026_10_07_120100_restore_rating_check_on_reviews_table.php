<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * reviews.rating must be 1 to 5. The create migration asked for a CHECK with
 * ->check(), which Laravel's SQLite grammar ignores, so the database never
 * enforced it. SQLite can't add a CHECK to an existing table, so the table is
 * rebuilt by hand: same columns, defaults and foreign keys, plus the CHECK.
 *
 * Ratings outside 1 to 5 are clamped first (0 becomes 1, 6 becomes 5); no row is
 * dropped. Every index on the table is created again under its old name.
 * Nothing references reviews, so dropping the old table cascades nothing.
 * Compared via sqlite_master in ReviewsIntegrityMigrationsTest.
 */
return new class extends Migration
{
    public function up(): void
    {
        $definition = DB::table('sqlite_master')->where('type', 'table')->where('name', 'reviews')->value('sql');

        if (is_string($definition) && str_contains(strtolower($definition), 'check')) {
            return;
        }

        DB::table('reviews')->where('rating', '<', 1)->update(['rating' => 1]);
        DB::table('reviews')->where('rating', '>', 5)->update(['rating' => 5]);

        DB::statement(
            'CREATE TABLE "__temp__reviews" ("id" integer primary key autoincrement not null, "customer_name" varchar not null, '
            .'"customer_email" varchar not null, "product_id" integer, "rating" integer not null check ("rating" between 1 and 5), '
            .'"comment" text, "is_approved" tinyint(1) not null default (\'0\'), "is_featured" tinyint(1) not null default (\'0\'), '
            .'"created_at" datetime, "updated_at" datetime, "order_id" integer, "photo_path" varchar, '
            .'foreign key("product_id") references products("id") on delete cascade on update no action, '
            .'foreign key("order_id") references "orders"("id") on delete set null)',
        );
        DB::statement(
            'INSERT INTO "__temp__reviews" ("id", "customer_name", "customer_email", "product_id", "rating", "comment", '
            .'"is_approved", "is_featured", "created_at", "updated_at", "order_id", "photo_path") '
            .'SELECT "id", "customer_name", "customer_email", "product_id", "rating", "comment", '
            .'"is_approved", "is_featured", "created_at", "updated_at", "order_id", "photo_path" FROM "reviews"',
        );
        DB::statement('DROP TABLE "reviews"');
        DB::statement('ALTER TABLE "__temp__reviews" RENAME TO "reviews"');

        DB::statement('CREATE INDEX "reviews_is_approved_index" on "reviews" ("is_approved")');
        DB::statement('CREATE INDEX "reviews_rating_index" on "reviews" ("rating")');
        DB::statement('CREATE INDEX "reviews_product_id_index" on "reviews" ("product_id")');
        DB::statement('CREATE UNIQUE INDEX "reviews_order_id_unique" on "reviews" ("order_id")');
    }
};
