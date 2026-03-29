<?php

namespace App\ValueObjects;

use Illuminate\Support\Number;

final readonly class Money
{
    private function __construct(
        private int $amountInCents,
    ) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromDollars(float|string $dollars): self
    {
        return new self((int) round((float) $dollars * 100));
    }

    public function cents(): int
    {
        return $this->amountInCents;
    }

    public function dollars(): float
    {
        return $this->amountInCents / 100;
    }

    public function formatted(): string
    {
        return (string) Number::currency($this->dollars());
    }

    public function add(self $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    public function subtract(self $other): self
    {
        return new self($this->amountInCents - $other->amountInCents);
    }

    public function multiply(int|float $factor): self
    {
        return new self((int) round($this->amountInCents * $factor));
    }

    public function isZero(): bool
    {
        return $this->amountInCents === 0;
    }

    public function isPositive(): bool
    {
        return $this->amountInCents > 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->amountInCents > $other->amountInCents;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
