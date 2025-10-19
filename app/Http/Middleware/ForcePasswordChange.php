<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if this is already the password change route
        if ($request->routeIs('filament.admin.auth.password-reset.*') || 
            $request->routeIs('password.*') ||
            $request->routeIs('filament.admin.pages.change-password') ||
            $request->is('admin/change-password*')) {
            return $next($request);
        }

        // Skip for API routes and AJAX requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        // Check if user needs to change password
        if ($user->needsPasswordChange()) {
            // Redirect to password change page
            return redirect()->to('/admin/change-password')
                ->with('warning', 'Por segurança, você deve alterar sua senha temporária antes de continuar.');
        }

        return $next($request);
    }
}