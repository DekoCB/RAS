<?php

namespace App\Providers;

use App\Models\Nota;
use App\Policies\NotaPolicy;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();
        Gate::policy(Nota::class, NotaPolicy::class);

        // SuperAdmin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('SuperAdmin')) {
                return true;
            }
        });
    }
}
