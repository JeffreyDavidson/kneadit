<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catering_inquiries', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('deposit_reference');
        });
    }
};
