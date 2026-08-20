<?php

namespace App\Queries\Dashboard;

use App\DataTransferObjects\Dashboard\NeedsAttentionCounts;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;

final class NeedsAttentionQuery
{
    public function get(): NeedsAttentionCounts
    {
        return new NeedsAttentionCounts(
            pendingOrders: Order::query()->where('status', OrderStatus::Pending)->count(),
            unreadMessages: ContactMessage::query()->where('is_read', false)->count(),
            newInquiries: CateringInquiry::query()->where('status', CateringInquiryStatus::Inquiry)->count(),
            lowStockIngredients: Ingredient::query()->lowStock()->count(),
        );
    }
}
