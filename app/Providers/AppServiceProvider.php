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
        Gate::define('admin', function (User $user) {
            return $user->role_id == 1;
        });

        Gate::define('unit-kemahasiswaan', function (User $user) {
            return $user->role_id == 2;
        });

        Gate::define('dosen', function (User $user) {
            return $user->role_id == 3;
        });
        Gate::define('kaprodi', function (User $user) {
            return $user->role_id == 4;
        });
        Gate::define('minat-bakat', function (User $user) {
            return $user->role_id == 5;
        });
        Gate::define('layanan-mahasiswa', function (User $user) {
            return $user->role_id == 6;
        });
        Gate::define('wakil-rektor', function (User $user) {
            return $user->role_id == 7;
        });
        
        Gate::define('approval', function (User $user) {
            return in_array($user->role_id, [1, 3, 4, 5, 6, 7]);
        });
    }
}
