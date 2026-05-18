<?php

namespace App\Filament\Widgets;

use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeSeason  = CampusSeason::where('is_active', true)->first();
        $totalCourses  = CampusCourse::where('is_active', true)->count();
        $activeCourses = CampusCourse::where('is_active', true)
                                     ->where('status', 'active')->count();
        $totalTeachers = CampusTeacher::where('status', 'active')->count();
        $totalUsers    = User::count();
        $activeUsers   = User::where('active', true)->count();

        return [
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
    }
}
