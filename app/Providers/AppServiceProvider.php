<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Vite::prefetch(concurrency: 3);

        // Proteksi brute-force login API (FR API.1) — 5 percobaan/menit per
        // kombinasi email+IP, terpisah dari throttle:api umum.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower((string) $request->string('email'))).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Dokumentasi API (Scramble, /docs/api) hanya untuk Super Admin di luar
        // environment local (lihat config/scramble.php).
        Gate::define('viewApiDocs', fn (User $user) => $user->role === 'super_admin');
    }
}
