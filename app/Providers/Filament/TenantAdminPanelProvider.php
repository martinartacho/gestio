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

/**
 * Mateix panell que AdminPanelProvider (mateixos Resources/Pages, es
 * descobreixen als mateixos directoris), però muntat a /{tenant}/admin en
 * lloc de /admin. ResolveTenant, el primer middleware, canvia la connexió
 * de BD abans que res més s'executi — per això cada tenant veu les seves
 * pròpies dades encara que el codi de l'admin sigui idèntic.
 */
class TenantAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant-admin')
            ->path('{tenant}/admin')
            ->login()
            ->revealablePasswords()

            ->colors(['primary' => Color::Indigo])
            ->brandName('GestorApp')

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

            ->navigationGroups([
                'Administración',
            ])

            ->middleware([
                \App\Http\Middleware\ResolveTenant::class,
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
            ->authMiddleware([Authenticate::class]);
    }
}
