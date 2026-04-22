<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredient_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('minimum_order', 10, 2)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->string('sku')->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'supplier_id']);
        });
    }
};
