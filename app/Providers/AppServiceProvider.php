<?php

namespace App\Providers;

use App\Policies\RegistrationPolicy;
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
        // registrations have no model, so their policy is registered ability by ability
        foreach (['viewAny', 'view', 'create', 'cancel'] as $ability) {
            Gate::define('registration.'.$ability, [RegistrationPolicy::class, $ability]);
        }
    }
}
