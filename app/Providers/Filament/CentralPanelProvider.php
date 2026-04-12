<?php

namespace App\Providers\Filament;

use App\Filament\Central\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CentralPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('central')
            ->path('admin')
            ->domains(config('tenancy.central_domains'))
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
            ->brandLogo(view('filament.central.brand-logo'))
            ->brandLogoHeight('50px')
            ->darkMode(true)
            ->navigationGroups([
                NavigationGroup::make('Platform'),
                NavigationGroup::make('Settings'),
            ])
            ->font('Inter')
            ->renderHook('panels::head.end', fn () => new HtmlString(
                '<link rel="stylesheet" href="' . asset('css/central-admin.css') . '?v=' . time() . '">',
            ))
            ->renderHook('panels::body.end', fn () => new HtmlString('
                <script>
                    document.addEventListener("livewire:navigating", () => {
                        const nav = document.querySelector(".fi-sidebar-nav");
                        if (nav) sessionStorage.setItem("sidebar-scroll", nav.scrollTop);
                    });
                    document.addEventListener("livewire:navigated", () => {
                        const nav = document.querySelector(".fi-sidebar-nav");
                        const pos = sessionStorage.getItem("sidebar-scroll");
                        if (nav && pos) requestAnimationFrame(() => nav.scrollTop = parseInt(pos));
                    });
                </script>
            '))
            ->discoverResources(in: app_path('Filament/Central/Resources'), for: 'App\Filament\Central\Resources')
            ->discoverPages(in: app_path('Filament/Central/Pages'), for: 'App\Filament\Central\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Central/Widgets'), for: 'App\Filament\Central\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
