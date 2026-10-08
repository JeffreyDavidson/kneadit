<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\QueryException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\ValidationResult;
use Propaganistas\LaravelPhone\PhoneNumber as LibPhoneNumber;

/**
 * The one definition of how a phone number is stored, checked and shown.
 *
 * Numbers are stored in E.164 (`+19133877359`) so a bakery outside the US and
 * its customers abroad keep their country code. A number typed without a
 * country code is read as a number from the bakery's country, which is the
 * country of its store phone (the US until it has one).
 *
 * Shown in the national format when the number is from the bakery's country
 * (`(913) 387-7359`), in the international format otherwise
 * (`+44 20 7946 0958`). A value that can't be read as a phone number is kept
 * and shown exactly as it was typed, never thrown away.
 */
final class PhoneNumber
{
    public const string DEFAULT_COUNTRY = 'US';

    /**
     * The E.164 form of the number, or the trimmed value unchanged when it
     * can't be read as a complete number. Blank becomes null.
     */
    public static function normalize(?string $value, ?string $country = null): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return self::parse($value, $country ?? self::homeCountry())?->formatE164() ?? $value;
    }

    /** Whether the value is a complete phone number for its country (or the given one). */
    public static function isPossible(?string $value, ?string $country = null): bool
    {
        return self::parse(trim((string) $value), $country ?? self::homeCountry()) instanceof LibPhoneNumber;
    }

    /**
     * How a stored number reads to a person. Only E.164 values are reformatted;
     * anything else is a value that couldn't be read and comes back as stored.
     */
    public static function display(?string $value, ?string $homeCountry = null): string
    {
        $value = trim((string) $value);
        $phone = str_starts_with($value, '+') ? self::parse($value, null) : null;

        if (! $phone instanceof LibPhoneNumber) {
            return $value;
        }

        return self::regionOf($phone) === ($homeCountry ?? self::homeCountry())
            ? $phone->formatNational()
            : $phone->formatInternational();
    }

    /** A `tel:` link target: E.164 when the number can be read, digits otherwise. */
    public static function telUri(?string $value): string
    {
        $value = trim((string) $value);
        $e164 = self::parse($value, self::homeCountry())?->formatE164();

        return 'tel:'.($e164 ?? preg_replace('/[^0-9+]/', '', $value));
    }

    /** The two-letter country of a stored number, or null when it has none. */
    public static function country(?string $value): ?string
    {
        $value = trim((string) $value);

        if (! str_starts_with($value, '+')) {
            return null;
        }

        $phone = self::parse($value, null);

        return $phone instanceof LibPhoneNumber ? self::regionOf($phone) : null;
    }

    /**
     * The calling code and national number, the way payment providers ask
     * for them (`['1', '9133877359']`). Null when the number can't be read.
     *
     * @return array{0: string, 1: string}|null
     */
    public static function callingCodeAndNationalNumber(?string $value): ?array
    {
        $phone = self::parse(trim((string) $value), self::homeCountry());

        if (! $phone instanceof LibPhoneNumber) {
            return null;
        }

        $libPhone = $phone->toLibPhoneObject();

        return [
            (string) $libPhone->getCountryCode(),
            PhoneNumberUtil::getInstance()->getNationalSignificantNumber($libPhone),
        ];
    }

    /**
     * The bakery's country: the country of its store phone, or the US when it
     * has none (or when there is no bakery database, as in the platform admin,
     * which has no settings table).
     */
    public static function homeCountry(): string
    {
        try {
            $storePhone = settings('store_phone');
        } catch (QueryException) {
            return self::DEFAULT_COUNTRY;
        }

        return self::country(is_string($storePhone) ? $storePhone : null) ?? self::DEFAULT_COUNTRY;
    }

    /**
     * The number's country. A number that is possible but not assigned (such
     * as a 555 number) has none, so it takes the main country of its calling
     * code: the US for +1.
     */
    private static function regionOf(LibPhoneNumber $phone): string
    {
        $util = PhoneNumberUtil::getInstance();
        $libPhone = $phone->toLibPhoneObject();

        return $util->getRegionCodeForNumber($libPhone)
            ?? $util->getRegionCodeForCountryCode((int) $libPhone->getCountryCode());
    }

    private static function parse(string $value, ?string $country): ?LibPhoneNumber
    {
        if ($value === '') {
            return null;
        }

        $phone = new LibPhoneNumber($value, $country)->lenient();

        try {
            $libPhone = $phone->toLibPhoneObject();
        } catch (NumberParseException) {
            return null;
        }

        // E.164 has no room for an extension, so a number with one is kept as typed.
        if ($libPhone->hasExtension()) {
            return null;
        }

        return PhoneNumberUtil::getInstance()->isPossibleNumberWithReason($libPhone) === ValidationResult::IS_POSSIBLE
            ? $phone
            : null;
    }
}
