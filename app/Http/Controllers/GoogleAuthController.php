<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleLoginException;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    // Step 1: send the user to Google
    public function redirect(GoogleAuthService $google)
    {
        abort_unless($google->isEnabled(), 404);

        return $google->redirect();
    }

    // Step 2: Google sends the user back here
    public function callback(Request $request, GoogleAuthService $google)
    {
        abort_unless($google->isEnabled(), 404);

        // The user pressed "Cancel" on Google's consent screen.
        if ($request->filled('error')) {
            return $this->fail('Google sign-in was cancelled.');
        }

        try {
            $result = $google->authenticate($google->fetchGoogleUser(), $request);
        } catch (GoogleLoginException $e) {
            return $this->fail($e->getMessage());
        }

        Auth::guard('admin')->login($result->admin, false);
        $request->session()->regenerate();   // prevents session fixation

        // A brand-new company first lands on the "name your workspace" page.
        return $result->created
            ? redirect()->route('workspace.onboarding')->with('auth_status', 'Welcome! Your free trial has started.')
            : redirect()->route('auth.home.main');
    }

    private function fail(string $message)
    {
        return redirect()->route('login')->with('auth_error', $message);
    }
}