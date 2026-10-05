<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * A campaign now emails each recipient once, so the log table refuses a second
 * row for the same campaign and email. Adding a unique index is done in place on
 * SQLite (no table rebuild), so the table's other constraints are untouched.
 *
 * Duplicate rows from earlier retried sends would fail the index, so each pair
 * keeps its lowest id first. These are send logs: a removed duplicate only loses
 * an extra tracking token. The count is logged. Safe to run more than once.
 */
return new class extends Migration
{
    private const string TABLE = 'customer_campaign_logs';

    private const array COLUMNS = ['customer_campaign_id', 'customer_email'];

    private const string INDEX = 'customer_campaign_logs_campaign_email_unique';

    public function up(): void
    {
        if (Schema::hasIndex(self::TABLE, self::COLUMNS, 'unique')) {
            return;
        }

        $removed = $this->removeDuplicates();

        if ($removed > 0) {
            Log::warning('Removed duplicate customer campaign log rows before adding the unique index.', [
                'tenant_id' => tenant('id'),
                'removed' => $removed,
            ]);
        }

        Schema::table(self::TABLE, function (Blueprint $table): void {
            $table->unique(self::COLUMNS, self::INDEX);
        });
    }

    private function removeDuplicates(): int
    {
        $removed = 0;

        DB::table(self::TABLE)
            ->select('customer_campaign_id', 'customer_email')
            ->selectRaw('MIN(id) as keep_id')
            ->groupBy('customer_campaign_id', 'customer_email')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $pair) use (&$removed): void {
                $removed += DB::table(self::TABLE)
                    ->where('customer_campaign_id', $pair->customer_campaign_id)
                    ->where('customer_email', $pair->customer_email)
                    ->where('id', '>', $pair->keep_id)
                    ->delete();
            });

        return $removed;
    }
};
