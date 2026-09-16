<?php

namespace App\Filament\Central\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    #[\Override]
    protected static ?int $sort = -2;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.central.widgets.quick-actions';
}
