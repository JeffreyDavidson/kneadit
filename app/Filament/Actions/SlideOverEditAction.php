<?php

namespace App\Filament\Actions;

use Filament\Actions\EditAction;

/**
 * Tenant-admin edit action that opens in a medium slide-over by default.
 *
 * Used across tenant resource tables in place of repeating
 * `EditAction::make()->slideOver()->modalWidth('md')`. Override the
 * width at the call site with `->modalWidth('lg')` etc. when needed.
 */
class SlideOverEditAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->slideOver();
        $this->modalWidth('md');
    }
}
