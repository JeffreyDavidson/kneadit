<?php

namespace App\ValueObjects;

use JsonSerializable;
use Livewire\Wireable;
use Stringable;

/**
 * Represents a percentage value, internally stored as basis points (1/100th of a percent)
 * for precision. 100% = 10000 basis points.
 */
final readonly class Percentage implements JsonSerializable, Stringable, Wireable
{
    private function __construct(
        private int $basisPoints,
    ) {}

    public function jsonSerialize(): float
    {
        return $this->value();
    }

    /** @return array{basisPoints: int} */
    public function toLivewire(): array
    {
        return ['basisPoints' => $this->basisPoints];
    }

    /** @param array{basisPoints: int} $value */
    public static function fromLivewire(mixed $value): self
    {
        return new self((int) $value['basisPoints']);
    }

    /** Construct from a 0–100 value (e.g. 25 means 25%). */
    public static function fromInt(int $percent): self
    {
        return new self($percent * 100);
    }

    /** Construct from a 0–100 (or fractional) value (e.g. 12.5 means 12.5%). */
    public static function fromFloat(float|string $percent): self
    {
        return new self((int) round((float) $percent * 100));
    }

    /** Construct from a 0.0–1.0 decimal (e.g. 0.25 means 25%). */
    public static function fromDecimal(float $decimal): self
    {
        return new self((int) round($decimal * 10000));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /** Returns the 0–100 percent value (e.g. 25.0 for 25%). */
    public function value(): float
    {
        return $this->basisPoints / 100;
    }

    /** Returns the 0.0–1.0 decimal (e.g. 0.25 for 25%). */
    public function decimal(): float
    {
        return $this->basisPoints / 10000;
    }

    public function formatted(int $precision = 0): string
    {
        return number_format($this->value(), $precision) . '%';
    }

    /** Apply this percentage to a Money amount and return the resulting Money. */
    public function applyTo(Money $amount): Money
    {
        return $amount->multiply($this->decimal());
    }

    /** Apply this percentage to a float (e.g. for raw arithmetic) and return the result. */
    public function of(float $amount): float
    {
        return $amount * $this->decimal();
    }

    public function isZero(): bool
    {
        return $this->basisPoints === 0;
    }

    public function isPositive(): bool
    {
        return $this->basisPoints > 0;
    }

    public function equals(self $other): bool
    {
        return $this->basisPoints === $other->basisPoints;
    }

    public function greaterThan(self $other): bool
    {
        return $this->basisPoints > $other->basisPoints;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
