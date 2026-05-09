<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Central\Pages\OnboardingTracker;
use App\Filament\Central\Resources\SupportTicketResource;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class NeedsAttention extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.needs-attention';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        $items = [];

        $openTickets = SupportTicket::query()->open()->count();
        if ($openTickets > 0) {
            $items[] = [
                'severity' => 'critical',
                'icon' => Heroicon::OutlinedInbox,
                'title' => $openTickets . ' open ' . str('ticket')->plural($openTickets) . ' awaiting reply',
                'subtitle' => 'Bakers are waiting on a response',
                'cta' => 'Open Inbox',
                'url' => SupportTicketResource::getUrl('index'),
            ];
        }

        $expiringTrials = Tenant::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->count();
        if ($expiringTrials > 0) {
            $items[] = [
                'severity' => 'warning',
                'icon' => Heroicon::OutlinedClock,
                'title' => $expiringTrials . ' ' . str('trial')->plural($expiringTrials) . ' ending this week',
                'subtitle' => 'Reach out before they convert or churn',
                'cta' => 'View Bakeries',
                'url' => TenantResource::getUrl('index'),
            ];
        }

        $stuckOnboarding = Tenant::query()
            ->where('created_at', '<=', now()->subDays(7))
            ->where(function (Builder $q): void {
                $q->whereNull('store_name')
                    ->orWhere('storefront_enabled', false);
            })
            ->count();
        if ($stuckOnboarding > 0) {
            $items[] = [
                'severity' => 'warning',
                'icon' => Heroicon::OutlinedClipboardDocumentCheck,
                'title' => $stuckOnboarding . ' ' . str('bakery')->plural($stuckOnboarding) . ' stuck in onboarding',
                'subtitle' => 'Signed up 7+ days ago without finishing setup',
                'cta' => 'Open Tracker',
                'url' => OnboardingTracker::getUrl(),
            ];
        }

        $deactivated = Tenant::query()->where('is_active', false)->count();
        if ($deactivated > 0) {
            $items[] = [
                'severity' => 'info',
                'icon' => Heroicon::OutlinedNoSymbol,
                'title' => $deactivated . ' deactivated ' . str('bakery')->plural($deactivated),
                'subtitle' => 'Currently blocked from accessing the platform',
                'cta' => 'Review',
                'url' => TenantResource::getUrl('index'),
            ];
        }

        return $items;
    }
}
