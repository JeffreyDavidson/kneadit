<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->index(['product_id', 'created_at']);
        });
    }
};
