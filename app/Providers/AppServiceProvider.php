<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('manage-catalog', fn (User $user): bool => $user->is_admin);

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(8)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(12)->by($request->ip()));
    }
}
