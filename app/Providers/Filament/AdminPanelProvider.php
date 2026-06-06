<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->revealablePasswords()

            // ── Marca visual ────────────────────────────────────────────────
            ->colors(['primary' => Color::Indigo])
            ->brandName('GestorApp')

            // ── Descubrimiento automático de recursos, páginas y widgets ───
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([Pages\Dashboard::class])
            ->widgets([StatsOverview::class])

            // ── Navegación ──────────────────────────────────────────────────
            ->navigationGroups([
                'Administración',
            ])

            // ── Middleware estándar de Filament ─────────────────────────────
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
            ])
            ->authMiddleware([Authenticate::class])
            ->renderHook(
                'panels::body.start',
                fn () => request()->routeIs('filament.admin.auth.login')
                    ? view('campus.partials.admin-login-nav')
                    : '',
            )
            ->renderHook(
                'panels::body.end',
                fn () => request()->routeIs('filament.admin.auth.login')
                    ? view('campus.partials.footer')
                    : '',
            );
    }
}