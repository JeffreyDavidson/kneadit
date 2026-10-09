<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The contact form's phone number was never stored because the column did not
 * exist. A plain nullable column is added in place on SQLite; the table is not
 * rebuilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('contact_messages', 'phone')) {
            return;
        }

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
        });
    }
};
