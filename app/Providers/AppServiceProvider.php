<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('admin', function () {
            return in_array(1, session('user_roles', []));
        });
        Gate::define('unit-kemahasiswaan', function () {
            return in_array(2, session('user_roles', []));
        });
        Gate::define('verifikator', function () {
            return in_array(3, session('user_roles', []));
        });
    }
}
