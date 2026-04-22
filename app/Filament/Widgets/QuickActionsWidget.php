<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.quick-actions';

    /** @return array<int, array<string, string|Heroicon>> */
    public function getQuickActions(): array
    {
        return [
            [
                'label' => 'Quick Order',
                'icon' => Heroicon::OutlinedPlusCircle,
                'url' => route('filament.admin.pages.quick-order'),
                'textClass' => 'text-brand-600',
                'bgClass' => 'bg-orange-50',
            ],
            [
                'label' => 'Products',
                'icon' => Heroicon::OutlinedCake,
                'url' => route('filament.admin.resources.products.index'),
                'textClass' => 'text-brand-300',
                'bgClass' => 'bg-amber-50',
            ],
            [
                'label' => 'Customers',
                'icon' => Heroicon::OutlinedUserGroup,
                'url' => route('filament.admin.resources.customers.index'),
                'textClass' => 'text-indigo-500',
                'bgClass' => 'bg-indigo-50',
            ],
            [
                'label' => 'Reviews',
                'icon' => Heroicon::OutlinedStar,
                'url' => route('filament.admin.resources.reviews.index'),
                'textClass' => 'text-amber-500',
                'bgClass' => 'bg-amber-50',
            ],
            [
                'label' => 'Messages',
                'icon' => Heroicon::OutlinedEnvelope,
                'url' => route('filament.admin.resources.contact-messages.index'),
                'textClass' => 'text-emerald-500',
                'bgClass' => 'bg-emerald-50',
            ],
            [
                'label' => 'Analytics',
                'icon' => Heroicon::OutlinedChartBar,
                'url' => '/admin/storefront-analytics',
                'textClass' => 'text-pink-500',
                'bgClass' => 'bg-pink-50',
            ],
        ];
    }
}
