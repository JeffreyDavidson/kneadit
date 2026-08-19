<?php

namespace App\Filament\Widgets;

use App\DataTransferObjects\Dashboard\NeedsAttentionCounts;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Queries\Dashboard\NeedsAttentionQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

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
        return resolve(NeedsAttentionQuery::class)->get()->hasItems();
    }

    /** @return array<int, array<string, mixed>> */
    public function getItems(): array
    {
        return $this->cached('items', [60, 120], fn (): array => $this->buildItems(
            resolve(NeedsAttentionQuery::class)->get(),
        ));
    }

    /** @return array<int, array<string, mixed>> */
    private function buildItems(NeedsAttentionCounts $counts): array
    {
        $items = [];

        $pending = $counts->pendingOrders;
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

        $unreadMessages = $counts->unreadMessages;
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

        $newInquiries = $counts->newInquiries;
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

        $lowStock = $counts->lowStockIngredients;
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
