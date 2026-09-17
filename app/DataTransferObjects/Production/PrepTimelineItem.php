<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Production;

final readonly class PrepTimelineItem
{
    public function __construct(
        public string $time,
        public string $task,
        public int $duration,
        public string $order,
        public string $deliveryTime,
    ) {}

    /** @return array{time: string, task: string, duration: int, order: string, delivery_time: string} */
    public function toArray(): array
    {
        return ['time' => $this->time, 'task' => $this->task, 'duration' => $this->duration, 'order' => $this->order, 'delivery_time' => $this->deliveryTime];
    }
}
