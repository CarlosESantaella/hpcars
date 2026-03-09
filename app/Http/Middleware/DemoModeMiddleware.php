<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Auto-authenticate a demo user when DEMO_MODE is enabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Auth routes that should remain accessible without auto-login in demo mode.
     */
    protected array $authRoutes = [
        'login',
        'register',
        'password.request',
        'password.reset',
        'two-factor.login',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.demo_mode')) {
            // En páginas de auth, hacer logout para poder ver los diseños
            if ($this->isAuthRoute($request)) {
                if (Auth::check()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return $next($request);
            }

            // En el resto de páginas, auto-login
            if (! Auth::check()) {
                $demoUser = User::firstOrCreate(
                    ['email' => 'demo@hpcars.test'],
                    [
                        'name' => 'Usuario Demo',
                        'password' => Hash::make('demo-password-not-for-production'),
                        'email_verified_at' => now(),
                    ]
                );

                Auth::login($demoUser);
            }
        }

        return $next($request);
    }

    /**
     * Check if the current request is for an auth route.
     */
    protected function isAuthRoute(Request $request): bool
    {
        foreach ($this->authRoutes as $routeName) {
            if ($request->routeIs($routeName)) {
                return true;
            }
        }

        return false;
    }
}
