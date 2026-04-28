<?php

namespace App\Providers\Filament;

use App\Filament\Central\CentralThemes;
use App\Filament\Central\Pages\Appearance;
use App\Filament\Central\Pages\Dashboard;
use App\Filament\Central\Resources\PlatformSettings\PlatformSettingResource;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
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
            ->brandName('KneadIt')
            ->brandLogo(view('filament.central.brand-logo'))
            ->brandLogoHeight('36px')
            // Force dark mode and hide the user-menu toggle — central-admin.css
            // hard-codes dark surfaces everywhere, so the light/system options
            // were inert. Theme variants are picked under Settings → Appearance.
            ->darkMode(true, isForced: true)
            ->viteTheme('resources/css/filament/central/theme.css')
            ->navigationGroups([
                NavigationGroup::make('Platform'),
                NavigationGroup::make('Communication'),
                NavigationGroup::make('Insights'),
                NavigationGroup::make('Settings'),
            ])
            ->font('Inter')
            ->renderHook('panels::head.end', fn () => new HtmlString(
                // Inject the theme palette BEFORE the stylesheet so the CSS variables
                // are defined when central-admin.css references them.
                '<style>' . CentralThemes::rootCss() . '</style>'
                . '<link rel="stylesheet" href="' . asset('css/central-admin.css') . '?v=' . filemtime(public_path('css/central-admin.css')) . '">',
            ))
            ->renderHook('panels::body.end', fn () => new HtmlString('
                <script>
                    document.addEventListener("livewire:navigating", () => {
                        const nav = document.querySelector(".fi-sidebar-nav");
                        if (nav) sessionStorage.setItem("sidebar-scroll", nav.scrollTop);

                        document.querySelectorAll(".fi-dropdown").forEach(el => {
                            window.Alpine?.$data(el)?.close?.();
                        });
                    });
                    document.addEventListener("livewire:navigated", () => {
                        const nav = document.querySelector(".fi-sidebar-nav");
                        const pos = sessionStorage.getItem("sidebar-scroll");
                        if (nav && pos) requestAnimationFrame(() => nav.scrollTop = parseInt(pos));
                    });
                </script>
            '))
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_BEFORE,
                fn () => view('filament.central.user-menu-header'),
            )
            ->userMenuItems([
                Action::make('appearance')
                    ->label('Appearance')
                    ->url(fn (): string => Appearance::getUrl())
                    ->icon(Heroicon::Swatch),
                Action::make('platformSettings')
                    ->label('Platform Settings')
                    ->url(fn (): string => PlatformSettingResource::getUrl('index'))
                    ->icon(Heroicon::Cog6Tooth),
            ])
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
