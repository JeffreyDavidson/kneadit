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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 8, 2);
            $table->enum('category', [
                'supplies',
                'ingredients',
                'packaging',
                'booth_fees',
                'delivery',
                'marketing',
                'insurance',
                'education',
                'equipment',
                'other',
            ]);
            $table->date('date');
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->integer('business_percentage')->default(100);
            $table->decimal('deductible_amount', 8, 2)->nullable();
            $table->timestamps();
        });
    }
};
