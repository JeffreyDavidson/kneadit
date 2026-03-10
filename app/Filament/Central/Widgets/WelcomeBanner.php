<?php

namespace App\Filament\Central\Widgets;

use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.welcome-banner';
}
