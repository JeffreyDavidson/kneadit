<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capacity_limits', function (Blueprint $table) {
            $table->date('specific_date')->nullable()->after('date');
            $table->string('day_of_week')->nullable()->after('specific_date');
            $table->boolean('is_blocked')->default(false)->after('max_orders');
            $table->text('notes')->nullable()->after('is_blocked');
        });
    }
};
