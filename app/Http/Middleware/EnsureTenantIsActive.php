<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('admin');

        if (! $guard->check()) {
            return $next($request);
        }

        $admin = $guard->user();

        if (! $admin->isActive()) {
            return $this->kickOut($request, 'Your account has been blocked. Please contact your company admin.', 'login');
        }

        if (! $admin->tenant?->isUsable()) {
            return $this->kickOut($request, 'Your company workspace is inactive. Please contact support.', 'workspace.inactive');
        }

        return $next($request);
    }

    private function kickOut(Request $request, string $message, string $routeName)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route($routeName)->with('auth_error', $message);
    }
}