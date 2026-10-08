<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An order has one review (CreateReview updates it when the form is sent again),
 * so reviews.order_id gets a unique index. Reviews with no order (NULL) are not
 * affected: a SQLite unique index allows any number of NULLs.
 *
 * Before one-review-per-order, sending the form again added another review for
 * the same order. No review is deleted: the newest one (highest id) stays linked
 * to the order and older ones are unlinked (order_id set to NULL), keeping their
 * name, rating, comment, photo and approval.
 *
 * The index is added in place; the table is not rebuilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('reviews', 'reviews_order_id_unique')) {
            return;
        }

        DB::statement(
            'UPDATE reviews SET order_id = NULL WHERE order_id IS NOT NULL '
            .'AND id < (SELECT MAX(newer.id) FROM reviews AS newer WHERE newer.order_id = reviews.order_id)',
        );

        Schema::table('reviews', function (Blueprint $table): void {
            $table->unique('order_id');
        });
    }
};
