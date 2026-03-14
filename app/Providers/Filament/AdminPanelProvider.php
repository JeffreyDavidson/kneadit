<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
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
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset()
            ->spa()
            ->maxContentWidth('full')
            ->colors([
                'primary' => [
                    50 => '253, 248, 242',
                    100 => '245, 230, 208',
                    200 => '232, 208, 176',
                    300 => '212, 165, 116',
                    400 => '193, 127, 78',
                    500 => '139, 94, 60',
                    600 => '107, 76, 59',
                    700 => '90, 61, 46',
                    800 => '74, 50, 37',
                    900 => '61, 35, 20',
                    950 => '42, 26, 14',
                ],
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->brandLogo(view('filament.brand-logo'))
            ->brandLogoHeight('80px')
            ->darkMode(false)
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Shop'),
                \Filament\Navigation\NavigationGroup::make('Settings'),
                \Filament\Navigation\NavigationGroup::make('Content'),
                \Filament\Navigation\NavigationGroup::make('Admin'),
                \Filament\Navigation\NavigationGroup::make('Tools'),
                \Filament\Navigation\NavigationGroup::make('Finance'),
                \Filament\Navigation\NavigationGroup::make('Communication'),
            ])
            ->databaseNotifications()
            ->font('Inter')
            ->favicon(asset('images/logo-icon.png'))
            ->renderHook('panels::head.end', fn () => new HtmlString(
                '<link rel="icon" type="image/png" sizes="32x32" href="'.asset('images/favicon-32x32.png').'">'
                .'<link rel="icon" type="image/png" sizes="16x16" href="'.asset('images/favicon-16x16.png').'">'
                .'<link rel="apple-touch-icon" sizes="180x180" href="'.asset('images/favicon-180x180.png').'">'
                .
                '<link rel="stylesheet" href="'.asset('css/filament-custom.css').'?v='.filemtime(public_path('css/filament-custom.css')).'">'
                .'<style>:root{'
                .'--brand-900:'.rescue(fn () => \App\Models\Setting::get('brand_color_900', '#3d2314'), '#3d2314', false).';'
                .'--brand-800:'.rescue(fn () => \App\Models\Setting::get('brand_color_800', '#4a3225'), '#4a3225', false).';'
                .'--brand-700:'.rescue(fn () => \App\Models\Setting::get('brand_color_700', '#6b4c3b'), '#6b4c3b', false).';'
                .'--brand-600:'.rescue(fn () => \App\Models\Setting::get('brand_color_600', '#8b5e3c'), '#8b5e3c', false).';'
                .'--brand-500:'.rescue(fn () => \App\Models\Setting::get('brand_color_500', '#a08060'), '#a08060', false).';'
                .'--brand-400:'.rescue(fn () => \App\Models\Setting::get('brand_color_400', '#c4a882'), '#c4a882', false).';'
                .'--brand-300:'.rescue(fn () => \App\Models\Setting::get('brand_color_300', '#d4a574'), '#d4a574', false).';'
                .'--brand-200:'.rescue(fn () => \App\Models\Setting::get('brand_color_200', '#e8d0b0'), '#e8d0b0', false).';'
                .'--brand-150:'.rescue(fn () => \App\Models\Setting::get('brand_color_150', '#f3ebe0'), '#f3ebe0', false).';'
                .'--brand-100:'.rescue(fn () => \App\Models\Setting::get('brand_color_100', '#f5e6d0'), '#f5e6d0', false).';'
                .'--brand-50:'.rescue(fn () => \App\Models\Setting::get('brand_color_50', '#fdf8f2'), '#fdf8f2', false).';'
                .'--accent-gold:'.rescue(fn () => \App\Models\Setting::get('brand_color_300', '#d4a574'), '#d4a574', false).';'
                .'}</style>'
            ))
            ->renderHook('panels::global-search.after', fn () => new HtmlString(
                '<a href="'.route('filament.admin.pages.dashboard-config').'" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:#8b6844;transition:background 0.15s;" title="Customize Dashboard" onmouseover="this.style.background=\'rgba(212,165,116,0.12)\'" onmouseout="this.style.background=\'transparent\'">'
                .'<svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" viewBox="0 0 20 20" fill="currentColor">'
                .'<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />'
                .'</svg></a>'
            ))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                PreventAccessFromCentralDomains::class,
                InitializeTenancyByDomainOrSubdomain::class,
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
                \App\Http\Middleware\EnsureOnboardingComplete::class,
            ]);
    }
}
