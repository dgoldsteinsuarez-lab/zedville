<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        });
        // NEW: Load bank.php with optional prefix and auth middleware
        Route::middleware(['web', 'auth'])
            ->prefix('bank-account')
            ->name('bank.')
            ->group(base_path('routes/bank.php'));
        // NEW: Load efd.php with optional prefix and auth middleware
        Route::middleware(['web', 'auth'])
            ->prefix('education')
            ->name('education.')
            ->group(base_path('routes/efd.php'));
        // Academy (Curriculum: Activities/Exams/Graphics per Grade Level,
        // + PISA Practice) - bugs.md, 2026-10-20.
        Route::middleware(['web', 'auth'])
            ->prefix('academy')
            ->name('academy.')
            ->group(base_path('routes/academy.php'));
        // NEW: Load supermarket.php with optional prefix and auth middleware
        Route::middleware(['web', 'auth'])
            ->prefix('supermarket')
            ->name('supermarket.')
            ->group(base_path('routes/supermarket.php'));
        Route::middleware(['web', 'auth'])
            ->prefix('spending-activities')
            ->name('spendingactivities.')
            ->group(base_path('routes/spendingactivities.php'));
        /*Route::middleware(['web', 'auth'])
            ->prefix('badges')
            ->name('badges')
            ->group(base_path('routes/badges.php'));*/
        Route::middleware(['web', 'auth'])->group(base_path('routes/badges.php'));
        // NEW: Load closet_routes.php
        Route::middleware(['web', 'auth'])
            ->prefix('closet')
            ->name('closet.')
            ->group(base_path('routes/closet_routes.php'));
        // NEW: Load citizenactivation.php
        Route::middleware(['web', 'auth'])
            ->prefix('citizen-activation')
            ->name('CitizenActivation.')
            ->group(base_path('routes/citizenactivation.php'));


    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
