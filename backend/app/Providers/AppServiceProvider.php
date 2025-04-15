<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleCloudStorageService;
use \Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleCloudStorageService::class, function ($app) {
            return new GoogleCloudStorageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;
                
                // Format the SQL query with bindings for better readability
                $formattedSql = $sql;
                foreach ($bindings as $i => $binding) {
                    $value = is_numeric($binding) ? $binding : "'{$binding}'";
                    $formattedSql = preg_replace('/\?/', $value, $formattedSql, 1);
                }
                
                Log::debug('Database query executed:', [
                    'query' => $formattedSql,
                    'execution_time_ms' => $time,
                    'bindings_count' => count($bindings)
                ]);
            });
        }
    }
}
