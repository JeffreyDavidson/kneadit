<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\Filament\WidgetSize;

/**
 * Pipe the dashboard's chosen t-shirt size (sm/md/lg) into a widget so
 * its render() can vary content density. The tenant Dashboard sets
 * this on each widget via Widget::make(['dashboardSize' => 'sm']);
 * Livewire then sets it as a public property when mounting the
 * component.
 *
 * Including the trait also overrides getColumnSpan() so a widget's
 * width on the dashboard grid follows its t-shirt size automatically:
 * sm = 1 column, md = 2, lg = 3 (full row at xl breakpoint).
 */
trait HasDashboardSize
{
    public string $dashboardSize = 'lg';

    /**
     * Map the t-shirt size to a Filament column-span. Filament's grid
     * is responsive; we keep the mobile single-column collapse that
     * Filament defaults to and only specify the spans at md and xl.
     *
     * @return array<string, ?int>
     */
    public function getColumnSpan(): array
    {
        $size = $this->size();

        return [
            'md' => min($size->columns(), 2),
            'xl' => $size->columns(),
        ];
    }

    protected function size(): WidgetSize
    {
        return WidgetSize::tryFrom($this->dashboardSize) ?? WidgetSize::Large;
    }

    protected function isSize(string $size): bool
    {
        return $this->dashboardSize === $size;
    }
}
