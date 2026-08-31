<?php

namespace App\Filament\Widgets;

use App\Models\AssociatMember;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // Cap d'aquestes consultes passa pel query builder automàtic de
        // Resource de Filament (que sí que filtra sol) — un widget és codi
        // nostre, cal filtrar-lo pel tenant actiu explícitament.
        $tenantId = Filament::getTenant()?->id;

        $activeSeason  = CampusSeason::where('tenant_id', $tenantId)->where('status', 'active')->first();
        $totalCourses  = CampusCourse::where('tenant_id', $tenantId)->count();
        $activeCourses = CampusCourse::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $totalTeachers = CampusTeacher::whereHas('tenants', fn ($q) => $q->where('tenants.id', $tenantId))->where('status', 'active')->count();
        $totalUsers    = User::where('tenant_id', $tenantId)->count();
        $activeUsers   = User::where('tenant_id', $tenantId)->where('active', true)->count();

        $stats = [
            Stat::make(__('site.courses'), $totalCourses)
                ->description($activeCourses . ' ' . mb_strtolower(__('site.course_statuses.active') ?? 'actius'))
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make(__('site.teachers'), $totalTeachers)
                ->description($activeSeason
                    ? $activeSeason->name
                    : 'Cap període actiu')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make(__('site.users'), $totalUsers)
                ->description($activeUsers . ' ' . mb_strtolower(__('site.active')))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make(__('site.roles'), Role::count())
                ->description(Permission::count() . ' ' . mb_strtolower(__('site.permissions')))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('gray'),
        ];

        // ── Mòdul LMS ────────────────────────────────────────────────────────
        if (setting('lms_enabled')) {
            // lms_lessons no té tenant_id propi: es filtra via el curs (que sí en té).
            $lmsQuery = LmsLesson::whereHas('course', fn ($q) => $q->where('tenant_id', $tenantId));
            $totalSessions     = (clone $lmsQuery)->count();
            $publishedSessions = (clone $lmsQuery)->where('status', 'published')->count();

            $stats[] = Stat::make('Sessions LMS', $totalSessions)
                ->description($publishedSessions . ' publicades')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('indigo');
        }

        // ── Mòdul Associats ──────────────────────────────────────────────────
        if (setting('associats_enabled')) {
            $totalMembers  = AssociatMember::where('tenant_id', $tenantId)->count();
            $activeMembers = AssociatMember::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $orgName       = setting('associats_org_name', 'Associats');

            $stats[] = Stat::make($orgName, $totalMembers)
                ->description($activeMembers . ' ' . mb_strtolower(__('site.active')))
                ->descriptionIcon('heroicon-m-identification')
                ->color('teal');
        }

        // ── Mòdul Tresoreria / Inscripcions ──────────────────────────────────
        if (setting('tresoreria_enabled') && setting('tresoreria_inscripcions_enabled')) {
            $totalEnrollments   = CampusEnrollment::where('tenant_id', $tenantId)->count();
            $pendingEnrollments = CampusEnrollment::where('tenant_id', $tenantId)->where('status', 'pending')->count();

            $stats[] = Stat::make('Inscripcions', $totalEnrollments)
                ->description($pendingEnrollments . ' pendents')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('amber');
        }

        return $stats;
    }
}
