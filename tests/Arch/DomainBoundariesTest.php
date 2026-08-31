<?php

declare(strict_types=1);

arch('reports stay independent of delivery adapters')
    ->expect(['App\Http', 'App\Filament', 'App\Console'])
    ->not->toBeUsedIn('App\Reports');

arch('domain actions stay independent of delivery adapters')
    ->expect(['App\Http', 'App\Filament', 'App\Console'])
    ->not->toBeUsedIn('App\Actions');

arch('domain services stay independent of console commands')
    ->expect('App\Console')
    ->not->toBeUsedIn('App\Services');
