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
        // Aquí definimos la Gate para gestionar usuarios
        Gate::define('manage-users', function (User $user) {
            return $user->is_admin === true || $user->profile?->nivel >= 2;
        });

        Gate::define('ver-informes', function ($user) {
    // Accedemos a la tabla extra a través de la relación
    return $user->profile?->seccion === 'info' && $user->profile?->nivel >= 2;
});

        Gate::define('RRHH', function($user){
        return $user->profile?->seccion === 'rh' && $user->profile?->nivel >= 1;
        });
        Gate::define('TOCO-CIR', function($user){
        return $user->profile?->seccion === 'TOCO-cir' && $user->profile?->nivel >= 1;
        });
        Gate::define('Quirofano', function($user){
        return $user->profile?->seccion === 'Quirofano' && $user->profile?->nivel >= 1;
        });
        Gate::define('ConExt', function($user){
        return $user->profile?->seccion === 'ConExt' && $user->profile?->nivel >= 1;
        });
        Gate::define('autoclaves', function($user){
        return $user->profile?->seccion === 'autoclaves' && $user->profile?->nivel >= 1;
        });

    }
}
