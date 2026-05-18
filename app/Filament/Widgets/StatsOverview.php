<?php

namespace App\Filament\Widgets;

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
        $totalUsers    = User::count();
        $activeUsers   = User::where('active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        return [
            Stat::make(__('site.total_users'), $totalUsers)
                ->description(__('site.registered_users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('site.active_users'), $activeUsers)
                ->description($inactiveUsers . ' ' . __('site.inactive'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('site.roles'), Role::count())
                ->description(__('site.configured_permissions', ['count' => Permission::count()]))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }
}