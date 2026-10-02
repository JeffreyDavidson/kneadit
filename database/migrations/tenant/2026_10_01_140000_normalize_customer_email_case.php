<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Customer emails are now stored lowercased and trimmed (see EmailAddressCast),
 * because SQLite compares text case-sensitively and `Bob@x.com` / `bob@x.com`
 * became two customers. This lowercases the emails already stored.
 *
 * Rows that would collide once lowercased are left exactly as they are and are
 * logged so the owner can merge them by hand: two customers whose emails differ
 * only by case (customers.email is unique), or two favorites of the same product.
 * Safe to run more than once: rows already normalized are skipped.
 */
return new class extends Migration
{
    /** Tables whose customer email column has no uniqueness to protect. */
    private const array PLAIN_COLUMNS = [
        'carts' => 'customer_email',
        'waitlist_entries' => 'customer_email',
        'product_waitlists' => 'customer_email',
        'customer_campaign_logs' => 'customer_email',
        'customer_photos' => 'customer_email',
        'reviews' => 'customer_email',
        'survey_responses' => 'customer_email',
        'catering_inquiries' => 'customer_email',
    ];

    public function up(): void
    {
        $this->normalizeCustomers();
        $this->normalizeFavorites();

        foreach (self::PLAIN_COLUMNS as $table => $column) {
            $this->normalizeColumn($table, $column);
        }
    }

    private function normalizeCustomers(): void
    {
        $customers = DB::table('customers')->orderBy('id')->get(['id', 'email']);

        $customers
            ->groupBy(fn (object $customer): string => $this->normalize($customer->email))
            ->each(function (Collection $group, string $normalized): void {
                if ($group->count() > 1) {
                    Log::warning('Customers whose emails differ only by case were left unchanged. Merge them by hand.', [
                        'tenant_id' => tenant('id'),
                        'customer_ids' => $group->pluck('id')->all(),
                        'emails' => $group->pluck('email')->all(),
                    ]);

                    return;
                }

                $customer = $group->first();

                if ($customer->email === $normalized) {
                    return;
                }

                DB::table('customers')->where('id', $customer->id)->update(['email' => $normalized]);
            });
    }

    private function normalizeFavorites(): void
    {
        $favorites = DB::table('customer_favorites')->orderBy('id')->get(['id', 'customer_email', 'product_id']);

        $favorites
            ->groupBy(fn (object $favorite): string => "{$favorite->product_id}|{$this->normalize($favorite->customer_email)}")
            ->each(function (Collection $group): void {
                if ($group->count() > 1) {
                    Log::warning('Favorites for the same product whose emails differ only by case were left unchanged. Merge them by hand.', [
                        'tenant_id' => tenant('id'),
                        'favorite_ids' => $group->pluck('id')->all(),
                        'emails' => $group->pluck('customer_email')->all(),
                    ]);

                    return;
                }

                $favorite = $group->first();
                $normalized = $this->normalize($favorite->customer_email);

                if ($favorite->customer_email === $normalized) {
                    return;
                }

                DB::table('customer_favorites')->where('id', $favorite->id)->update(['customer_email' => $normalized]);
            });
    }

    private function normalizeColumn(string $table, string $column): void
    {
        DB::table($table)
            ->whereNotNull($column)
            ->chunkById(500, function (Collection $rows) use ($table, $column): void {
                foreach ($rows as $row) {
                    $normalized = $this->normalize($row->{$column});

                    if ($row->{$column} === $normalized) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([$column => $normalized]);
                }
            });
    }

    private function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }
};
