<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\PreventAccessFromTenantDomains;

class CentralPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('central')
            ->path('admin')
            ->login()
            ->spa()
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->brandName('KneadIt Platform')
            ->darkMode(true)
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Platform'),
                \Filament\Navigation\NavigationGroup::make('Settings'),
            ])
            ->font('Inter')
            ->renderHook('panels::head.end', fn () => new HtmlString(
                '<style>'
                .'.fi-sidebar { background: #1a1a2e !important; }'
                .'.fi-sidebar-item-label { color: #e2e8f0 !important; }'
                .'.fi-sidebar-group-label { color: #94a3b8 !important; }'
                .'</style>'
            ))
            ->discoverResources(in: app_path('Filament/Central/Resources'), for: 'App\Filament\Central\Resources')
            ->discoverPages(in: app_path('Filament/Central/Pages'), for: 'App\Filament\Central\Pages')
            ->pages([
                \App\Filament\Central\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Central/Widgets'), for: 'App\Filament\Central\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                PreventAccessFromTenantDomains::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
