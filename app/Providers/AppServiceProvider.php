<?php

namespace App\Providers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Routing\UrlGenerator;
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
    public function boot(UrlGenerator $url): void
    {
        Gate::define('edit-plan', function (User $user, Plan $plan) {
            return $plan->users()->wherePivotNotNull('accepted_at')->wherePivot('user_id', $user->id)->count() > 0;
        });
        Gate::define('invite-delete-plan', function (User $user, Plan $plan) {
            return $plan->users()->wherePivotNotNull('accepted_at')->wherePivot('role', 'Owner')->wherePivot('user_id', auth()->id())->count() > 0;
        });

        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
