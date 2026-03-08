<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['instagram', 'facebook', 'tiktok']);
            $table->text('caption');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->dateTime('scheduled_for')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'posted'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
