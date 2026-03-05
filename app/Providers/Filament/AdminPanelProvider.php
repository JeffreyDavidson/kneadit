<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->login()
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
            ->favicon(asset('favicon.ico'))
            ->renderHook('panels::head.end', fn () => new HtmlString(
                '<link rel="icon" type="image/png" sizes="32x32" href="' . asset('images/favicon-32x32.png') . '">'
                . '<link rel="icon" type="image/png" sizes="16x16" href="' . asset('images/favicon-16x16.png') . '">'
                . '<link rel="apple-touch-icon" sizes="180x180" href="' . asset('images/favicon-180x180.png') . '">'
                .
                '<link rel="stylesheet" href="' . asset('css/filament-custom.css') . '?v=' . filemtime(public_path('css/filament-custom.css')) . '">'
                . '<style>'
                . '.fi-fo-repeater .fi-fo-repeater-items .fi-fo-repeater-item{border-radius:0!important;box-shadow:none!important;background:transparent!important;outline:none!important;--tw-ring-shadow:0 0 0 0 transparent!important;--tw-shadow:0 0 0 0 transparent!important;--tw-inset-shadow:0 0 0 0 transparent!important;--tw-inset-ring-shadow:0 0 0 0 transparent!important;--tw-ring-offset-shadow:0 0 0 0 transparent!important;--tw-ring-color:transparent!important;border:none!important;border-bottom:1px solid #f3ebe0!important}'
                . '.fi-fo-repeater .fi-fo-repeater-items .fi-fo-repeater-item:last-child{border-bottom:none!important}'
                . '.fi-fo-repeater .fi-fo-repeater-items{gap:0!important}'
                . '.fi-input-wrp{box-shadow:0 0 0 1px #e8d0b0!important;border:none!important;border-radius:8px!important;outline:none!important}'
                . '.fi-input-wrp:focus-within{box-shadow:0 0 0 2px #8b5e3c!important}'
                . '</style>'
                . '<style>:root{'
                . '--brand-900:' . rescue(fn () => \App\Models\Setting::get('brand_color_900', '#3d2314'), '#3d2314', false) . ';'
                . '--brand-800:' . rescue(fn () => \App\Models\Setting::get('brand_color_800', '#4a3225'), '#4a3225', false) . ';'
                . '--brand-700:' . rescue(fn () => \App\Models\Setting::get('brand_color_700', '#6b4c3b'), '#6b4c3b', false) . ';'
                . '--brand-600:' . rescue(fn () => \App\Models\Setting::get('brand_color_600', '#8b5e3c'), '#8b5e3c', false) . ';'
                . '--brand-500:' . rescue(fn () => \App\Models\Setting::get('brand_color_500', '#a08060'), '#a08060', false) . ';'
                . '--brand-400:' . rescue(fn () => \App\Models\Setting::get('brand_color_400', '#c4a882'), '#c4a882', false) . ';'
                . '--brand-300:' . rescue(fn () => \App\Models\Setting::get('brand_color_300', '#d4a574'), '#d4a574', false) . ';'
                . '--brand-200:' . rescue(fn () => \App\Models\Setting::get('brand_color_200', '#e8d0b0'), '#e8d0b0', false) . ';'
                . '--brand-150:' . rescue(fn () => \App\Models\Setting::get('brand_color_150', '#f3ebe0'), '#f3ebe0', false) . ';'
                . '--brand-100:' . rescue(fn () => \App\Models\Setting::get('brand_color_100', '#f5e6d0'), '#f5e6d0', false) . ';'
                . '--brand-50:' . rescue(fn () => \App\Models\Setting::get('brand_color_50', '#fdf8f2'), '#fdf8f2', false) . ';'
                . '--accent-gold:' . rescue(fn () => \App\Models\Setting::get('brand_color_300', '#d4a574'), '#d4a574', false) . ';'
                . '}</style>'
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
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                InitializeTenancyByDomainOrSubdomain::class,
                PreventAccessFromCentralDomains::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureOnboardingComplete::class,
            ]);
    }
}
