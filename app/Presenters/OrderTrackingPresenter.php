<?php

namespace App\Presenters;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;

final class OrderTrackingPresenter
{
    public readonly bool $isCancelled;

    public readonly int $currentStepIndex;

    /**
     * @param array<int, OrderStatus> $trackableStatuses
     */
    public function __construct(
        public readonly Order $order,
        private readonly array $trackableStatuses,
    ) {
        $this->isCancelled = $order->status === OrderStatus::Cancelled;

        $index = array_search($order->status, $trackableStatuses);
        $this->currentStepIndex = $index === false ? -1 : $index;
    }

    public static function for(Order $order): self
    {
        return new self($order, OrderStatus::trackableStatuses());
    }

    public function isStepCompleted(int $stepIndex): bool
    {
        return $stepIndex <= $this->currentStepIndex;
    }

    public function isCurrentStep(int $stepIndex): bool
    {
        return $stepIndex === $this->currentStepIndex;
    }

    public function placedAt(): string
    {
        return $this->order->created_at?->format('M j, Y \a\t g:i A') ?? '';
    }

    public function progressPercentage(): float
    {
        $totalSteps = count($this->trackableStatuses) - 1;

        if ($totalSteps <= 0 || $this->currentStepIndex <= 0) {
            return 0.0;
        }

        return ($this->currentStepIndex / $totalSteps) * 100;
    }
}
