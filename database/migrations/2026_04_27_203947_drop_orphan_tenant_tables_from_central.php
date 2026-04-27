<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops tenant-only tables that ended up in the central database via
 * historical drift — duplicated migration files that landed in the
 * central directory by accident, plus residue from past failed
 * tenants:migrate runs that misrouted to the central connection.
 *
 * All tables listed here have no model or feature wired to them in the
 * central panel. The matching tables on tenant DBs are unaffected;
 * those have their own tenant migrations and continue to exist per
 * tenant.
 *
 * Companion change in this PR: removes five wrongly-duplicated migration
 * files (categories, order_items, recipes, reviews, settings) from
 * database/migrations/ so a fresh setup doesn't recreate them in central.
 */
return new class extends Migration {
    /** @var list<string> */
    private array $tables = [
        // Duplicated tenant migrations that mistakenly landed in central:
        'categories',
        'order_items',
        'recipes',
        'reviews',
        'settings',
        // Tables created by past misrouted tenants:migrate runs:
        'capacity_limits',
        'contact_messages',
        'coupons',
        'customer_favorites',
        'customer_notes',
        'customer_photos',
        'customer_profiles',
        'customer_reminders',
        'customers',
        'expenses',
        'gallery_photos',
        'incomes',
        'orders',
        'page_views',
        'products',
        'social_posts',
        'staff_invitations',
        'waitlist_entries',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::connection('central')->dropIfExists($table);
        }
    }
};
