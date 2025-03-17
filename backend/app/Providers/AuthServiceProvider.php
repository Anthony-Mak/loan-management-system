<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Register your model policies here
        // Example: App\Models\Model::class => App\Policies\ModelPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register all policies
        $this->registerPolicies();

        // Define abilities based on roles
        Gate::define('manage-system', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-hr', function ($user) {
            return in_array($user->role, ['admin', 'hr']);
        });

        Gate::define('apply-loan', function ($user) {
            return in_array($user->role, ['admin', 'hr', 'employee']);
        });

        // Add additional permissions as needed
        Gate::define('view-dashboard', function ($user) {
            return true; // All authenticated users can view dashboard
        });

        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['admin', 'hr']);
        });
    }
}