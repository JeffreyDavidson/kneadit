<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Orders;

/**
 * Saved order or cart lines brought up to date with the menu.
 */
final readonly class RefreshedOrderLines
{
    /**
     * @param  list<array{product_id: int, name: string, price: float, quantity: int}>  $items  lines that can still be ordered, at today's price
     * @param  list<string>  $removedNames  names of the lines that were dropped
     */
    public function __construct(
        public array $items,
        public array $removedNames,
    ) {}
}
