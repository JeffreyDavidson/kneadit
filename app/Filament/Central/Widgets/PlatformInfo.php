<?php

namespace App\Filament\Central\Widgets;

use Filament\Widgets\Widget;

class PlatformInfo extends Widget
{
    protected static ?int $sort = 999;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.welcome-banner';
}
