<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            // 'all' or one of the RfmSegment values (champions/loyal/at_risk/new/hibernating)
            $table->string('target_segment')->default('all');
            $table->string('status')->default('draft'); // draft | sending | sent
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();

            $table->index(['status']);
        });
    }
};
