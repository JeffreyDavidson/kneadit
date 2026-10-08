<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A phone number with the right number of digits for its country. The phone
 * inputs send E.164 (`+19133877359`), which carries the country picked in the
 * country selector; a number typed without a country code is checked against
 * the bakery's country. Blank values are left to `nullable`/`required`.
 */
class PossiblePhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && PhoneNumber::isPossible($value)) {
            return;
        }

        $fail('Enter a complete phone number for the selected country.');
    }
}
