<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Support\PhoneNumber;
use Filament\Forms\Components\Concerns\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained as CanBeLengthConstrainedContract;
use Filament\Forms\Components\Field;

/**
 * The one address field for the Filament admin. With a Google Maps browser key
 * (`services.google_maps.browser_key`) it suggests addresses from Google Places
 * as the person types, limited to the bakery's country; picking one fills the
 * field and, when told where (`fills()`), the city, state and ZIP fields
 * beside it. Without a key it is a plain text box. Typing by hand always
 * works. The browser side is the shared `addressInput` Alpine component in
 * resources/js/address-input.js, the same one the storefront uses.
 */
class AddressInput extends Field implements CanBeLengthConstrainedContract
{
    use CanBeLengthConstrained;
    use HasPlaceholder;

    #[\Override]
    protected string $view = 'filament.forms.components.address-input';

    /** @var array{city?: string, state?: string, zip?: string} */
    protected array $parts = [];

    protected ?int $rows = null;

    /**
     * The sibling fields a picked suggestion fills, by their names in this
     * form. With them, this field gets only the street line; without them,
     * it gets the whole formatted address.
     */
    public function fills(?string $city = null, ?string $state = null, ?string $zip = null): static
    {
        $this->parts = array_filter(['city' => $city, 'state' => $state, 'zip' => $zip]);

        return $this;
    }

    /** Show a textarea of this many rows instead of a one-line box. */
    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function getBrowserKey(): ?string
    {
        $key = config('services.google_maps.browser_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    /** The country suggestions are limited to, as Google's region code (`us`). */
    public function getCountry(): string
    {
        return strtolower(PhoneNumber::homeCountry());
    }

    /**
     * The Livewire paths of the fields a suggestion fills, by part.
     *
     * @return array<string, string>
     */
    public function getPartStatePaths(): array
    {
        return array_map(fn (string $name): string => $this->resolveRelativeStatePath($name), $this->parts);
    }
}
