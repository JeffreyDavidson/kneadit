<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Rules\PossiblePhoneNumber;
use App\Support\PhoneNumber;
use Filament\Forms\Components\Field;

/**
 * The one phone field for the Filament admin: a country selector (flag and
 * dial code, the bakery's country by default) and a number that formats as
 * it is typed. It holds E.164 (`+19133877359`), so the value saved is the
 * value stored. The browser side is the shared `phoneInput` Alpine component
 * in resources/js/phone-input.js, the same one the storefront uses.
 */
class PhoneInput extends Field
{
    #[\Override]
    protected string $view = 'filament.forms.components.phone-input';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(new PossiblePhoneNumber);
        $this->dehydrateStateUsing(fn (mixed $state): ?string => is_string($state) ? PhoneNumber::normalize($state) : null);
    }

    /** The country the selector starts on, as intl-tel-input expects it (`us`). */
    public function getDefaultCountry(): string
    {
        return strtolower(PhoneNumber::homeCountry());
    }
}
