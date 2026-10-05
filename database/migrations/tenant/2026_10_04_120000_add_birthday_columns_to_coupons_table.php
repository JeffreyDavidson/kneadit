<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plain nullable columns and an index are added in place on SQLite, so the
     * coupons table (and the CHECK on its type column) is not rebuilt. There is
     * deliberately no foreign key: adding one would rebuild the table.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedSmallInteger('birthday_year')->nullable();
            $table->unique(['customer_id', 'birthday_year']);
        });
    }
};
