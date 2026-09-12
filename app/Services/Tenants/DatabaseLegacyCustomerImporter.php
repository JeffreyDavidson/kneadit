<?php

namespace App\Services\Tenants;

use App\Contracts\Tenants\LegacyCustomerImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnexpectedValueException;

final class DatabaseLegacyCustomerImporter implements LegacyCustomerImporter
{
    /**
     * @param array<int, array<string, mixed>> $orders
     * @return array<string, int>
     */
    public function import(array $orders): array
    {
        $ids = [];

        foreach ($orders as $order) {
            $email = Str::lower(trim($this->stringValue($order['customer_email'])));
            DB::table('customers')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $order['customer_name'],
                    'phone' => $order['customer_phone'] ?? null,
                    'address' => $order['delivery_address'] ?? null,
                    'zip' => $order['delivery_zip'] ?? null,
                    'created_at' => $order['created_at'] ?? now(),
                    'updated_at' => $order['updated_at'] ?? now(),
                ],
            );
            $ids[$email] = $this->parseLegacyInteger(DB::table('customers')->where('email', $email)->value('id'));
        }

        return $ids;
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new UnexpectedValueException('Expected a string-compatible legacy value.');
        }

        return (string) $value;
    }

    private function parseLegacyInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }
}
