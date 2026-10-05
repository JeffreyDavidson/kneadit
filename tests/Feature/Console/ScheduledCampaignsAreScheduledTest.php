<?php

use function Pest\Laravel\artisan;

test('the campaign senders are on the scheduler', function (string $command) {
    artisan('schedule:list')
        ->expectsOutputToContain($command)
        ->assertSuccessful();
})->with([
    'bakery campaigns' => 'campaigns:send-scheduled',
    'platform campaigns' => 'platform:send-scheduled-campaigns',
]);
