<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Http, RateLimiter};
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;   // per email+IP in 15 min, and per account before lock
    private const LOCK_MINUTES = 30;

    public function showLogin() { return view('superadminconsole.pages.console.login'); }

    /** Step 1: email + password (+ Turnstile). Does NOT sign in yet. */
    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email:strict|max:150', 'password' => 'required|string|max:200']);
        $key = 'console-login|' . strtolower($d['email']) . '|' . $r->ip();
        $this->throttle($key);
        $this->verifyTurnstile($r);

        $admin = SuperAdmin::where('email', $d['email'])->first();
        // One hash operation either way, so response time doesn't reveal valid emails.
        $valid = $admin ? Hash::check($d['password'], $admin->password) : (Hash::make($d['password']) && false);
        $locked = $admin && $admin->locked_until?->isFuture();

        if (!$valid || $locked || !$admin->is_active || !$admin->two_factor_confirmed_at) {
            RateLimiter::hit($key, 900);
            if ($admin && !$locked) {
                $admin->increment('failed_attempts');
                if ($admin->failed_attempts >= self::MAX_ATTEMPTS) $admin->update(['locked_until' => now()->addMinutes(self::LOCK_MINUTES)]);
            }
            $this->record($locked ? 'locked' : 'failed', $admin, $r, $d['email']);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records, or the account is locked.']);
        }

        RateLimiter::clear($key);
        $r->session()->regenerate();
        session(['console.pending' => ['id' => $admin->id, 'at' => now()->timestamp]]);
        return $this->ok($r, route('console.2fa'));
    }

    public function showTwoFactor() { return $this->pending() ? view('superadminconsole.pages.console.two-factor') : redirect()->route('console.login'); }

    /** Step 2: authenticator code. Only now is the guard logged in. */
    public function verifyTwoFactor(Request $r, Google2FA $g2fa)
    {
        $admin = $this->pending();
        if (!$admin) return response()->json(['message' => 'Session expired. Please sign in again.', 'redirect' => route('superadminconsole.pages.console.login')], 401);

        $r->validate(['code' => 'required|digits:6']);
        $key = 'console-2fa|' . $admin->id;
        $this->throttle($key);

        $ts = $g2fa->verifyKeyNewer($admin->two_factor_secret, $r->code, $admin->two_factor_last_ts, 1);
        if ($ts === false) {
            RateLimiter::hit($key, 300);
            $this->record('2fa_failed', $admin, $r);
            throw ValidationException::withMessages(['code' => 'Invalid or expired code. Check your authenticator app.']);
        }

        $admin->update(['two_factor_last_ts' => $ts, 'failed_attempts' => 0, 'locked_until' => null, 'last_login_at' => now(), 'last_login_ip' => $r->ip()]);
        RateLimiter::clear($key);
        Auth::guard('superadmin')->login($admin);
        $r->session()->regenerate();
        session()->forget('console.pending');
        session(['console.2fa_passed' => true, 'console.last_activity' => now()->timestamp]);
        $this->record('success', $admin, $r);
        return $this->ok($r, route('console.dashboard'));
    }

    public function logout(Request $r)
    {
        $this->record('logout', Auth::guard('superadmin')->user(), $r);
        Auth::guard('superadmin')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('superadminconsole.pages.console.login');
    }

    private function pending(): ?SuperAdmin
    {
        $p = session('console.pending');
        return ($p && now()->timestamp - $p['at'] <= 300) ? SuperAdmin::find($p['id']) : null;
    }

    private function throttle(string $key): void
    {
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $s = RateLimiter::availableIn($key);
            abort(response()->json(['message' => 'Too many attempts. Please wait before trying again.', 'retry_after' => $s], 429));
        }
    }

    private function verifyTurnstile(Request $r): void
    {
        if (!($secret = config('console.turnstile.secret'))) return;
        $ok = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret, 'response' => $r->input('cf-turnstile-response'), 'remoteip' => $r->ip(),
        ])->json('success');
        if (!$ok) throw ValidationException::withMessages(['email' => 'Security check failed. Please try again.']);
    }

    private function record(string $event, ?SuperAdmin $a, Request $r, ?string $email = null): void
    {
        DB::table('super_admin_login_logs')->insert([
            'super_admin_id' => $a?->id, 'email' => $email ?? $a?->email, 'event' => $event,
            'ip' => $r->ip(), 'user_agent' => substr((string) $r->userAgent(), 0, 255), 'created_at' => now(),
        ]);
    }

    private function ok(Request $r, string $url) { return $r->expectsJson() ? response()->json(['redirect' => $url]) : redirect($url); }
}
