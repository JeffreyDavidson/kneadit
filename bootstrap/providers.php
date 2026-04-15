<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CentralPanelProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    AdminPanelProvider::class,
    CentralPanelProvider::class,
    TenancyServiceProvider::class,
];
