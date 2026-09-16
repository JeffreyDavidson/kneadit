<?php

namespace App\Services\Tenants;

use App\Services\Tenants\Contracts\LegacyEngagementImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseLegacyEngagementImporter implements LegacyEngagementImporter
{
    /**
     * @param  array<int, array<string, mixed>>  $contactMessages
     * @param  array<int, array<string, mixed>>  $waitlistEntries
     * @param  array<int, array<string, mixed>>  $favorites
     * @param  array<int, int>  $productIds
     */
    public function import(array $contactMessages, array $waitlistEntries, array $favorites, array $productIds): void
    {
        foreach ($contactMessages as $message) {
            DB::table('contact_messages')->updateOrInsert(
                ['email' => $message['email'], 'message' => $message['message']],
                ['name' => $message['name'], 'subject' => $message['subject'] ?? 'Legacy contact message', 'is_read' => ($message['status'] ?? 'new') !== 'new', 'created_at' => $message['created_at'] ?? now(), 'updated_at' => $message['updated_at'] ?? now()],
            );
        }
        foreach ($waitlistEntries as $entry) {
            $notes = collect([$entry['product_interest'] ?? null, $entry['notes'] ?? null])->filter()->implode("\n\n");
            DB::table('waitlist_entries')->updateOrInsert(
                ['customer_email' => $entry['customer_email'], 'requested_date' => $entry['requested_date']],
                ['customer_name' => $entry['customer_name'], 'customer_phone' => $entry['customer_phone'] ?? null, 'product_id' => isset($entry['product_id']) ? ($productIds[$this->integer($entry['product_id'])] ?? null) : null, 'notes' => $notes ?: null, 'status' => $entry['status'] ?? 'waiting', 'created_at' => $entry['created_at'] ?? now(), 'updated_at' => $entry['updated_at'] ?? now()],
            );
        }
        foreach ($favorites as $favorite) {
            $productId = $this->integer($favorite['product_id']);
            DB::table('customer_favorites')->updateOrInsert(
                ['customer_email' => Str::lower($this->string($favorite['customer_email'])), 'product_id' => $productIds[$productId]],
                ['created_at' => $favorite['created_at'] ?? now(), 'updated_at' => $favorite['updated_at'] ?? now()],
            );
        }
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }
}
