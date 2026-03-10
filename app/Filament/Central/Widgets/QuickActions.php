<?php

namespace App\Filament\Central\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.quick-actions';
}
