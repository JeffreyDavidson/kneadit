<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\BladeServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CentralPanelProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    ApplicationBindingsServiceProvider::class,
    AuthServiceProvider::class,
    BladeServiceProvider::class,
    EventServiceProvider::class,
    InfrastructureServiceProvider::class,
    RateLimitServiceProvider::class,
    AdminPanelProvider::class,
    CentralPanelProvider::class,
    TenancyServiceProvider::class,
];
