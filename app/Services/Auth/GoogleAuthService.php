<?php

namespace App\Services\Auth;

use App\Exceptions\GoogleLoginException;
use App\Models\Admin;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class GoogleAuthService
{
    public function __construct(private TenantProvisioner $provisioner)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.google.enabled')
            && filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function signupEnabled(): bool
    {
        return (bool) config('tenancy.google_signup');
    }

    public function redirect(): RedirectResponse
    {
        // Stateful flow: Socialite stores and verifies the OAuth "state" in the session (CSRF protection).
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function fetchGoogleUser(): GoogleUser
    {
        try {
            return Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            throw new GoogleLoginException('Google sign-in could not be completed. Please try again.');
        }
    }

    /**
     * 1. Known Google profile or known email (added by a company super admin, or registered before) -> sign in.
     * 2. Unknown email and signup enabled -> create a new company and make this person its super admin (owner).
     * 3. Unknown email and signup disabled -> refuse.
     */
    public function authenticate(GoogleUser $google, Request $request): GoogleLoginResult
    {
        $googleId = (string) $google->getId();
        $email    = strtolower(trim((string) $google->getEmail()));
        $name     = trim((string) $google->getName());
        $verified = filter_var(data_get($google->user, 'email_verified', false), FILTER_VALIDATE_BOOLEAN);

        // Only an email that Google has verified may create or open an account.
        if ($googleId === '' || $email === '' || ! $verified) {
            throw new GoogleLoginException('Your Google email address is not verified.');
        }

        $admin   = $this->findExisting($googleId, $email);
        $created = false;

        if (! $admin) {
            if (! $this->signupEnabled()) {
                throw new GoogleLoginException(
                    'This Google account is not registered. Ask your company admin to add your email first.'
                );
            }

            try {
                $admin   = $this->provisioner->registerOwner($name, $email, $googleId, $google->getAvatar(), $request->ip());
                $created = true;
            } catch (UniqueConstraintViolationException $e) {
                // Two requests raced for the same email: use the account the other request created.
                $admin = $this->findExisting($googleId, $email);

                if (! $admin) {
                    throw new GoogleLoginException('Google sign-in could not be completed. Please try again.');
                }
            }
        }

        if ($admin->google_id !== null && $admin->google_id !== $googleId) {
            Log::warning('Google login: email matches a different linked Google profile', ['admin_id' => $admin->id]);

            throw new GoogleLoginException('This account is linked to a different Google profile. Contact your admin.');
        }

        if (! $admin->isActive()) {
            throw new GoogleLoginException('Your account has been blocked. Please contact your company admin.');
        }

        if (! $admin->tenant?->isUsable()) {
            throw new GoogleLoginException('Your company workspace is inactive. Please contact support.');
        }

        if (! $created) {
            // Link (first Google login of a user that was added by email) and refresh login info.
            $admin->forceFill([
                'google_id'        => $googleId,
                'avatar'           => $google->getAvatar(),
                'google_linked_at' => $admin->google_linked_at ?? now(),
                'last_login_at'    => now(),
                'last_login_ip'    => $request->ip(),
            ])->save();
        }

        Log::info('Google login', ['admin_id' => $admin->id, 'tenant_id' => $admin->tenant_id, 'new_company' => $created]);

        return new GoogleLoginResult($admin, $created);
    }

    private function findExisting(string $googleId, string $email): ?Admin
    {
        // withoutGlobalScopes: no company is known yet. admins.email is globally unique,
        // so one email always maps to exactly one company.
        return Admin::withoutGlobalScopes()->where('google_id', $googleId)->first()
            ?? Admin::withoutGlobalScopes()->where('email', $email)->first();
    }
}