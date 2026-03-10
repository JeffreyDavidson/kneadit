<?php

namespace App\Filament\Central\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.central.widgets.quick-actions';
}
