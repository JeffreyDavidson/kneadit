<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_contact_name')->nullable()->after('delivery_address');
            $table->string('pickup_contact_phone')->nullable()->after('pickup_contact_name');
            $table->string('pickup_contact_email')->nullable()->after('pickup_contact_phone');
        });
    }
};
