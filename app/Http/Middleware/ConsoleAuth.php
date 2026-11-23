<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Inner wall: password + 2FA completed, account active, session not idle. */
class ConsoleAuth
{
    public function handle(Request $r, Closure $next)
    {
        $g = Auth::guard('superadmin');
        $idle = now()->timestamp - (int) session('console.last_activity', 0) > config('console.idle_minutes') * 60;

        if (!$g->check() || !$g->user()->is_active || !session('console.2fa_passed') || $idle) {
            $g->logout();
            $r->session()->invalidate();
            return redirect()->route('console.login');
        }
        session(['console.last_activity' => now()->timestamp]);
        return $next($r)->header('Cache-Control', 'no-store, private');
    }
}
