<?php

namespace App\Filament\Actions;

use Filament\Actions\EditAction;

/**
 * Tenant-admin edit action that opens in a medium slide-over by default.
 *
 * Used across tenant resource tables in place of repeating
 * `EditAction::make()->slideOver()->modalWidth('md')`. Override the
 * width at the call site with `->modalWidth('lg')` etc. when needed.
 *
 * Filament v5 actions have NO automatic policy authorization
 * (see vendor CanBeAuthorized trait header), so the `update` ability
 * is wired explicitly here. The Gate is checked against the row record
 * via the policy registered for the table model.
 */
class SlideOverEditAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->slideOver();
        $this->modalWidth('md');
        $this->authorize('update');
    }
}
