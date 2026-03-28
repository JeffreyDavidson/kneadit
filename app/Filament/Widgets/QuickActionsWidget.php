<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.quick-actions';

    /** @return array<int, array<string, string>> */
    public function getActions(): array
    {
        return [
            [
                'label' => 'Quick Order',
                'icon' => 'heroicon-o-plus-circle',
                'url' => route('filament.admin.pages.quick-order'),
                'color' => '#8B5E3C',
                'bg' => '#FDF2E9',
            ],
            [
                'label' => 'Products',
                'icon' => 'heroicon-o-cake',
                'url' => route('filament.admin.resources.products.index'),
                'color' => '#D4A574',
                'bg' => '#FEF9F3',
            ],
            [
                'label' => 'Customers',
                'icon' => 'heroicon-o-user-group',
                'url' => route('filament.admin.resources.customers.index'),
                'color' => '#6366F1',
                'bg' => '#EEF2FF',
            ],
            [
                'label' => 'Reviews',
                'icon' => 'heroicon-o-star',
                'url' => route('filament.admin.resources.reviews.index'),
                'color' => '#F59E0B',
                'bg' => '#FFFBEB',
            ],
            [
                'label' => 'Messages',
                'icon' => 'heroicon-o-envelope',
                'url' => route('filament.admin.resources.contact-messages.index'),
                'color' => '#10B981',
                'bg' => '#ECFDF5',
            ],
            [
                'label' => 'Analytics',
                'icon' => 'heroicon-o-chart-bar',
                'url' => '/admin/storefront-analytics',
                'color' => '#EC4899',
                'bg' => '#FDF2F8',
            ],
        ];
    }
}
