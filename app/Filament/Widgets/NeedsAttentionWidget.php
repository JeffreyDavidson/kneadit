<?php

namespace App\Filament\Widgets;

use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Surfaces the few operational urgencies a baker should act on right
 * now — pending orders waiting on confirmation, unread customer
 * messages, low-stock ingredients, catering quote requests. Each row
 * links straight to the relevant resource. Hidden entirely when the
 * counts are all zero so it doesn't take dashboard real estate when
 * there's nothing to do.
 */
class NeedsAttentionWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = -10;

    protected string $view = 'filament.widgets.needs-attention';

    public static function canView(): bool
    {
        return Order::query()->where('status', OrderStatus::Pending)->exists()
            || ContactMessage::query()->where('is_read', false)->exists()
            || CateringInquiry::query()->where('status', CateringInquiryStatus::Inquiry)->exists()
            || Ingredient::query()
                ->where(function (Builder $q): void {
                    $q->where('current_stock', '<=', 0)
                        ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
                })
                ->exists();
    }

    /** @return array<int, array<string, mixed>> */
    public function getItems(): array
    {
        return $this->cached('items', [60, 120], fn (): array => $this->buildItems());
    }

    /** @return array<int, array<string, mixed>> */
    private function buildItems(): array
    {
        $items = [];

        $pending = Order::query()->where('status', OrderStatus::Pending)->count();
        if ($pending > 0) {
            $items[] = [
                'severity' => $pending > 5 ? 'critical' : 'warning',
                'icon' => Heroicon::OutlinedClipboardDocumentCheck,
                'title' => $pending . ' pending ' . str('order')->plural($pending) . ' awaiting confirmation',
                'subtitle' => 'Customers are waiting to hear back',
                'url' => OrderResource::getUrl('index'),
                'cta' => 'Open Orders',
            ];
        }

        $unreadMessages = ContactMessage::query()->where('is_read', false)->count();
        if ($unreadMessages > 0) {
            $items[] = [
                'severity' => 'warning',
                'icon' => Heroicon::OutlinedInbox,
                'title' => $unreadMessages . ' unread customer ' . str('message')->plural($unreadMessages),
                'subtitle' => 'Customers have reached out and are awaiting a reply',
                'url' => ContactMessageResource::getUrl('index'),
                'cta' => 'Open Inbox',
            ];
        }

        $newInquiries = CateringInquiry::query()->where('status', CateringInquiryStatus::Inquiry)->count();
        if ($newInquiries > 0) {
            $items[] = [
                'severity' => 'warning',
                'icon' => Heroicon::OutlinedCake,
                'title' => $newInquiries . ' new catering ' . str('inquiry')->plural($newInquiries) . ' awaiting a quote',
                'subtitle' => 'Send a quote before the customer drops off',
                'url' => CateringInquiryResource::getUrl('index'),
                'cta' => 'View Inquiries',
            ];
        }

        $lowStock = Ingredient::query()
            ->where(function (Builder $q): void {
                $q->where('current_stock', '<=', 0)
                    ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
            })
            ->count();
        if ($lowStock > 0) {
            $items[] = [
                'severity' => 'info',
                'icon' => Heroicon::OutlinedArchiveBoxXMark,
                'title' => $lowStock . ' ' . str('ingredient')->plural($lowStock) . ' running low',
                'subtitle' => 'Restock before production stalls',
                'url' => IngredientResource::getUrl('index'),
                'cta' => 'View Ingredients',
            ];
        }

        return $items;
    }

    protected function cachePrefix(): string
    {
        return 'needs_attention';
    }
}
