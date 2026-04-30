<?php

use App\Filament\Actions\AuthorizedDeleteBulkAction;

test('AuthorizedDeleteBulkAction authorizes individual records via the delete ability', function () {
    $action = AuthorizedDeleteBulkAction::make('delete');

    $reflection = new ReflectionClass($action);
    $property = $reflection->getProperty('authorizeIndividualRecords');
    $property->setAccessible(true);

    expect($property->getValue($action))->toBe('delete');
});
