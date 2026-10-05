<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * A platform campaign now emails each owner address once, so the log table
 * refuses a second row for the same campaign and email. Adding a unique index
 * is done in place on SQLite (no table rebuild).
 *
 * Nothing wrote these rows before, so duplicates are not expected; if any exist,
 * each pair keeps its lowest id and the count is logged. Safe to run more than once.
 */
return new class extends Migration
{
    protected $connection = 'central';

    private const string TABLE = 'email_campaign_logs';

    private const array COLUMNS = ['campaign_id', 'email'];

    private const string INDEX = 'email_campaign_logs_campaign_email_unique';

    public function up(): void
    {
        $schema = Schema::connection('central');

        if ($schema->hasIndex(self::TABLE, self::COLUMNS, 'unique')) {
            return;
        }

        $removed = $this->removeDuplicates();

        if ($removed > 0) {
            Log::warning('Removed duplicate email campaign log rows before adding the unique index.', [
                'removed' => $removed,
            ]);
        }

        $schema->table(self::TABLE, function (Blueprint $table): void {
            $table->unique(self::COLUMNS, self::INDEX);
        });
    }

    private function removeDuplicates(): int
    {
        $removed = 0;

        DB::connection('central')->table(self::TABLE)
            ->select('campaign_id', 'email')
            ->selectRaw('MIN(id) as keep_id')
            ->groupBy('campaign_id', 'email')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $pair) use (&$removed): void {
                $removed += DB::connection('central')->table(self::TABLE)
                    ->where('campaign_id', $pair->campaign_id)
                    ->where('email', $pair->email)
                    ->where('id', '>', $pair->keep_id)
                    ->delete();
            });

        return $removed;
    }
};
