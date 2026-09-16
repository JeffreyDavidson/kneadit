<?php

namespace Tests\Support\Filament;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use RuntimeException;

final class FailingWidgetPreview extends Component
{
    public function render(): View
    {
        throw new RuntimeException('database password leaked');
    }
}
