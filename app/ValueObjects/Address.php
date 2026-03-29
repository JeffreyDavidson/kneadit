<?php

namespace App\ValueObjects;

final readonly class Address
{
    public function __construct(
        public ?string $street = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip = null,
    ) {}

    /**
     * Format as a single-line address string.
     */
    public function formatted(): string
    {
        $parts = collect([$this->street, $this->city, $this->state])
            ->filter()
            ->implode(', ');

        if ($this->zip) {
            $parts .= " {$this->zip}";
        }

        return $parts;
    }

    /**
     * Check if the address has any data.
     */
    public function isEmpty(): bool
    {
        return ! $this->street && ! $this->city && ! $this->state && ! $this->zip;
    }

    /**
     * Create from an array of address components.
     *
     * @param array<string, string|null> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['address'] ?? $data['street'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? null,
        );
    }

    /**
     * Convert to array for Eloquent storage.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'address' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
        ];
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
