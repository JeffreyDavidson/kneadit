<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard\Dashboard;
use App\Filament\Pages\Platform\Messages;
use App\Filament\Shared\DesignSystem;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\InitializeTenancyIfNeeded;
use App\Http\Middleware\SecurityHeaders;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return DesignSystem::lightByDefault(DesignSystem::configure($panel))
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->passwordReset()
            ->spa()
            ->maxContentWidth('full')
            ->brandLogo(view('filament.brand-logo'))
            ->brandLogoHeight('36px')
            ->navigationGroups([
                NavigationGroup::make('Shop'),
                NavigationGroup::make('Settings'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Admin'),
                NavigationGroup::make('Tools'),
                NavigationGroup::make('Finance'),
                NavigationGroup::make('Communication'),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Upgrade Plan')
                    ->url(fn (): string => route('filament.admin.pages.upgrade-plan'))
                    ->icon('heroicon-o-arrow-up-circle'),
                MenuItem::make()
                    ->label('Messages')
                    ->url(fn (): string => route('filament.admin.pages.messages'))
                    ->visible(fn (): bool => Messages::canAccess())
                    ->icon('heroicon-o-envelope'),
                MenuItem::make()
                    ->label('Help')
                    ->url(fn (): string => route('filament.admin.pages.help-center'))
                    ->icon('heroicon-o-question-mark-circle'),
            ])
            ->databaseNotifications()
            ->favicon(asset('images/logo-icon.png'))
            ->renderHook('panels::head.end', fn (): HtmlString => new HtmlString(
                '<link rel="icon" type="image/png" sizes="32x32" href="'.asset('images/favicons/favicon-32x32.png').'">'
                .'<link rel="icon" type="image/png" sizes="16x16" href="'.asset('images/favicons/favicon-16x16.png').'">'
                .'<link rel="apple-touch-icon" sizes="180x180" href="'.asset('images/favicons/favicon-180x180.png').'">',
            ))
            ->renderHook('panels::global-search.after', fn (): View => view('filament.render-hooks.dashboard-settings-link', [
                'url' => route('filament.admin.pages.dashboard-config'),
            ]))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            // Widget discovery disabled — Dashboard page controls which widgets appear
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                SecurityHeaders::withoutCsp(),
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                PreventAccessFromCentralDomains::class,
                InitializeTenancyIfNeeded::class,
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
                EnsureOnboardingComplete::class,
            ]);
    }
}
