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
        Gate::define('dosen', function () {
            return in_array(3, session('user_roles', []));
        });
        Gate::define('kaprodi', function () {
            return in_array(4, session('user_roles', []));
        });
        Gate::define('minat-bakat', function () {
            return in_array(5, session('user_roles', []));
        });
        Gate::define('layanan-mahasiswa', function () {
            return in_array(6, session('user_roles', []));
        });
        Gate::define('wakil-rektor', function () {
            return in_array(7, session('user_roles', []));
        });
        Gate::define('approval', function () {
            return (bool) array_intersect([1, 3, 4, 5, 6, 7], session('user_roles', []));
        });
    }
}
