<?php

namespace App\DataTransferObjects\Financial;

use Livewire\Wireable;

final readonly class PricingRecommendation implements Wireable
{
    /**
     * @param array<int, array{qty: int, label: string, unit_price: float, total: float}> $bulkTiers
     */
    public function __construct(
        public float $ingredientCost,
        public float $laborCost,
        public float $overhead,
        public float $totalCost,
        public float $recommendedPrice,
        public float $minPrice,
        public float $maxPrice,
        public ?float $currentPrice,
        public float $profitPerUnit,
        public float $actualMarginPercent,
        public array $bulkTiers,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return [
            'ingredientCost' => $this->ingredientCost,
            'laborCost' => $this->laborCost,
            'overhead' => $this->overhead,
            'totalCost' => $this->totalCost,
            'recommendedPrice' => $this->recommendedPrice,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'currentPrice' => $this->currentPrice,
            'profitPerUnit' => $this->profitPerUnit,
            'actualMarginPercent' => $this->actualMarginPercent,
            'bulkTiers' => $this->bulkTiers,
        ];
    }

    public static function fromLivewire(mixed $value): self
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected the pricing recommendation payload to be an array.');
        }

        return new self(
            ingredientCost: self::floatValue($value['ingredientCost'] ?? null, 'ingredientCost'),
            laborCost: self::floatValue($value['laborCost'] ?? null, 'laborCost'),
            overhead: self::floatValue($value['overhead'] ?? null, 'overhead'),
            totalCost: self::floatValue($value['totalCost'] ?? null, 'totalCost'),
            recommendedPrice: self::floatValue($value['recommendedPrice'] ?? null, 'recommendedPrice'),
            minPrice: self::floatValue($value['minPrice'] ?? null, 'minPrice'),
            maxPrice: self::floatValue($value['maxPrice'] ?? null, 'maxPrice'),
            currentPrice: isset($value['currentPrice']) ? self::floatValue($value['currentPrice'], 'currentPrice') : null,
            profitPerUnit: self::floatValue($value['profitPerUnit'] ?? null, 'profitPerUnit'),
            actualMarginPercent: self::floatValue($value['actualMarginPercent'] ?? null, 'actualMarginPercent'),
            bulkTiers: self::bulkTiers($value['bulkTiers'] ?? null),
        );
    }

    private static function floatValue(mixed $value, string $key): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be numeric.");
        }

        return (float) $value;
    }

    private static function integerValue(mixed $value, string $key): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException("Expected {$key} to be an integer.");
        }

        return (int) $value;
    }

    private static function stringValue(mixed $value, string $key): string
    {
        if (! is_string($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be a string.");
        }

        return $value;
    }

    /** @return array<int, array{qty: int, label: string, unit_price: float, total: float}> */
    private static function bulkTiers(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected bulkTiers to be an array.');
        }

        $bulkTiers = [];

        foreach (array_values($value) as $index => $tier) {
            if (! is_array($tier)) {
                throw new \UnexpectedValueException("Expected bulkTiers.{$index} to be an array.");
            }

            $bulkTiers[] = [
                'qty' => self::integerValue($tier['qty'] ?? null, "bulkTiers.{$index}.qty"),
                'label' => self::stringValue($tier['label'] ?? null, "bulkTiers.{$index}.label"),
                'unit_price' => self::floatValue($tier['unit_price'] ?? null, "bulkTiers.{$index}.unit_price"),
                'total' => self::floatValue($tier['total'] ?? null, "bulkTiers.{$index}.total"),
            ];
        }

        return $bulkTiers;
    }
}
