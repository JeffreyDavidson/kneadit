<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Capacity limits are matched by specific_date or day_of_week; the old
     * unique `date` column made every weekday limit collide on its creation day.
     */
    public function up(): void
    {
        Schema::table('capacity_limits', function (Blueprint $table) {
            $table->dropUnique(['date']);
            $table->dropColumn('date');
        });
    }
};
