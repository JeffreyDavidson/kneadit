<?php

use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phone numbers are now stored in E.164 (`+19133877359`, see PhoneNumberCast).
 * Until now the cast kept only the digits (`9133877359`) and the store phone
 * and pickup contact phone were stored as typed (`(913) 387-7359`).
 *
 * This rewrites every stored phone number in E.164, reading a number without
 * a country code as a US number. A value that can't be read as a complete
 * number (too short, letters, an extension) is left exactly as it is and its
 * row is logged (ids only, no numbers) so the bakery can fix it by hand.
 * Nothing is deleted. Safe to run more than once: E.164 values are skipped.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const array COLUMNS = [
        'customers' => 'phone',
        'suppliers' => 'phone',
        'waitlist_entries' => 'customer_phone',
        'catering_inquiries' => 'customer_phone',
        'orders' => 'pickup_contact_phone',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            $this->normalizeColumn($table, $column);
        }

        $this->normalizeStorePhone();
    }

    private function normalizeColumn(string $table, string $column): void
    {
        $unreadable = [];

        DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->chunkById(500, function (Collection $rows) use ($table, $column, &$unreadable): void {
                foreach ($rows as $row) {
                    $value = $row->{$column};

                    if (! is_string($value) || ! PhoneNumber::isPossible($value, PhoneNumber::DEFAULT_COUNTRY)) {
                        $unreadable[] = $row->id;

                        continue;
                    }

                    $normalized = PhoneNumber::normalize($value, PhoneNumber::DEFAULT_COUNTRY);

                    if ($normalized === $value) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([$column => $normalized]);
                }
            });

        $this->logUnreadable($table, $column, $unreadable);
    }

    private function normalizeStorePhone(): void
    {
        $setting = DB::table('settings')->where('key', 'store_phone')->first(['id', 'value']);

        if ($setting === null || ! is_string($setting->value) || trim($setting->value) === '') {
            return;
        }

        if (! PhoneNumber::isPossible($setting->value, PhoneNumber::DEFAULT_COUNTRY)) {
            $this->logUnreadable('settings', 'store_phone', [$setting->id]);

            return;
        }

        $normalized = PhoneNumber::normalize($setting->value, PhoneNumber::DEFAULT_COUNTRY);

        if ($normalized === $setting->value) {
            return;
        }

        DB::table('settings')->where('id', $setting->id)->update(['value' => $normalized]);
    }

    /** @param list<mixed> $ids Row ids, as the query builder returns them. */
    private function logUnreadable(string $table, string $column, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        Log::warning('Phone numbers that could not be read were left unchanged. Fix them by hand.', [
            'tenant_id' => tenant('id'),
            'table' => $table,
            'column' => $column,
            'ids' => $ids,
        ]);
    }
};
