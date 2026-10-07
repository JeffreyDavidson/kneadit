<?php

use App\Enums\Platform\AnnouncementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    /**
     * The original table only allowed four types, so saving a "holiday" announcement
     * failed the CHECK constraint. SQLite cannot change a CHECK in place, so this
     * rebuilds the table deliberately with the full definition (the CHECK now lists
     * every AnnouncementType case) and copies the rows across. The table has no
     * indexes beyond its primary key and nothing references it.
     */
    public function up(): void
    {
        $schema = Schema::connection('central');

        $schema->dropIfExists('platform_announcements_rebuilt');

        $schema->create('platform_announcements_rebuilt', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('type', array_column(AnnouncementType::cases(), 'value'))->default(AnnouncementType::Info->value);
            $table->json('target_plans')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_dismissable')->default(true);
            $table->timestamps();
        });

        DB::connection('central')->statement(
            'INSERT INTO platform_announcements_rebuilt (id, title, body, type, target_plans, is_active, starts_at, ends_at, is_dismissable, created_at, updated_at) '
            .'SELECT id, title, body, type, target_plans, is_active, starts_at, ends_at, is_dismissable, created_at, updated_at FROM platform_announcements',
        );

        $schema->drop('platform_announcements');
        $schema->rename('platform_announcements_rebuilt', 'platform_announcements');
    }
};
