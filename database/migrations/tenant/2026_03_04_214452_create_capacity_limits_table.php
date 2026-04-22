<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('capacity_limits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('max_orders');
            $table->timestamps();

            $table->unique('date');
        });
    }
};
