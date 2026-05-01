<?php

namespace App\Filament\Actions;

use Filament\Actions\DeleteBulkAction;

/**
 * DeleteBulkAction with per-record authorization wired in.
 *
 * Filament v5 actions have NO automatic policy authorization
 * (see vendor CanBeAuthorized trait header), so a vanilla
 * `DeleteBulkAction::make()` runs for any user that reaches the
 * page. Used everywhere in place of `DeleteBulkAction::make()`
 * so each selected row is gated through the model's `delete`
 * policy ability before the destroy SQL fires.
 */
class AuthorizedDeleteBulkAction extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeIndividualRecords('delete');
    }
}
