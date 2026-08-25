<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Role::saving(function (Role $role) {
            if (blank($role->guard_name)) {
                $role->guard_name = config('auth.defaults.guard', 'web');
            }
        });

        Role::deleting(function (Role $role) {
            if (blank($role->guard_name)) {
                $role->guard_name = config('auth.defaults.guard', 'web');
            }
        });
    }
}
