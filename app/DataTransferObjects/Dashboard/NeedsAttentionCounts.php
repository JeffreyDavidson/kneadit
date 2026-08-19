<?php

namespace App\DataTransferObjects\Dashboard;

final readonly class NeedsAttentionCounts
{
    public function __construct(
        public int $pendingOrders,
        public int $unreadMessages,
        public int $newInquiries,
        public int $lowStockIngredients,
    ) {}

    public function hasItems(): bool
    {
        return $this->pendingOrders > 0
            || $this->unreadMessages > 0
            || $this->newInquiries > 0
            || $this->lowStockIngredients > 0;
    }
}
