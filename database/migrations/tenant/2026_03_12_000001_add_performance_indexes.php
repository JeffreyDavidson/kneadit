<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders — frequently queried by status, payment, date, customer
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('delivery_date');
            $table->index('customer_id');
            $table->index('created_at');
        });

        // Products — filtered by active status and category
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('category_id');
        });

        // Reviews — sorted/filtered by rating and approval
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('is_approved');
            $table->index('rating');
        });

        // Customers — searched by email, sorted by created
        Schema::table('customers', function (Blueprint $table) {
            $table->index('email');
            $table->index('created_at');
        });

        // Expenses — filtered by date and category for finance reports
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->index('date');
                $table->index('category');
            });
        }

        // Income — filtered by date
        if (Schema::hasTable('incomes')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->index('date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['delivery_date']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['rating']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropIndex(['date']);
                $table->dropIndex(['category']);
            });
        }

        if (Schema::hasTable('incomes')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropIndex(['date']);
            });
        }
    }
};
