<?php

namespace App\Services\Tenants;

use App\Contracts\Tenants\LegacyCouponImporter;
use App\Enums\Financial\CouponType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use UnexpectedValueException;

final class DatabaseLegacyCouponImporter implements LegacyCouponImporter
{
    /**
     * @param array<int, array<string, mixed>> $coupons
     * @return array<int, int>
     */
    public function import(array $coupons): array
    {
        $ids = [];

        foreach ($coupons as $coupon) {
            $type = $this->couponType($coupon['type']);
            $code = Str::upper($this->stringValue($coupon['code']));

            DB::table('coupons')->updateOrInsert(
                ['code' => $code],
                [
                    'type' => $type,
                    'fixed_amount' => $type === CouponType::Fixed->value ? $this->cents($coupon['value']) : null,
                    'percentage' => $type === CouponType::Percentage->value ? $coupon['value'] : null,
                    'min_order_amount' => isset($coupon['minimum_order']) ? $this->cents($coupon['minimum_order']) : null,
                    'max_uses' => $coupon['max_uses'] ?? null,
                    'used_count' => $coupon['times_used'] ?? 0,
                    'starts_at' => $coupon['starts_at'] ?? null,
                    'expires_at' => $coupon['expires_at'] ?? null,
                    'is_active' => $coupon['is_active'] ?? true,
                    'created_at' => $coupon['created_at'] ?? now(),
                    'updated_at' => $coupon['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($coupon['id'])] = $this->parseLegacyInteger(DB::table('coupons')->where('code', $code)->value('id'));
        }

        return $ids;
    }

    private function couponType(mixed $value): string
    {
        $normalized = Str::lower($this->stringValue($value));
        $normalized = $normalized === 'fixed_amount' ? CouponType::Fixed->value : $normalized;

        $type = CouponType::tryFrom($normalized);
        throw_if($type === null, InvalidArgumentException::class, "Unsupported coupon type [{$normalized}].");

        return $type->value;
    }

    private function cents(mixed $dollars): int
    {
        return (int) round($this->floatValue($dollars) * 100);
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

    private function floatValue(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new UnexpectedValueException('Expected a numeric legacy value.');
        }

        return (float) $value;
    }
}
