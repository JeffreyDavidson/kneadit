<?php

namespace App\DataTransferObjects\Orders;

use Illuminate\Support\Carbon;

final readonly class OrderCalendarDay
{
    public function __construct(
        public Carbon $date,
        public int $displayMonth,
        public int $orderCount,
    ) {}

    public function colorClass(): string
    {
        return match (true) {
            $this->orderCount === 0 => 'bg-gray-100 hover:bg-gray-200',
            $this->orderCount <= 5 => 'bg-green-100 hover:bg-green-200 text-green-800',
            $this->orderCount <= 10 => 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800',
            default => 'bg-red-100 hover:bg-red-200 text-red-800',
        };
    }

    /** @return array{date: Carbon, dateString: string, isCurrentMonth: bool, isToday: bool, orderCount: int, colorClass: string} */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'dateString' => $this->date->format('Y-m-d'),
            'isCurrentMonth' => $this->date->month === $this->displayMonth,
            'isToday' => $this->date->isToday(),
            'orderCount' => $this->orderCount,
            'colorClass' => $this->colorClass(),
        ];
    }
}
