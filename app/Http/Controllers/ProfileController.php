<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangeOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    private const OTP_TTL_MINUTES    = 10;
    private const OTP_MAX_ATTEMPTS   = 5;
    private const OTP_RESEND_SECONDS = 60;

    // Show profile page
    public function edit()
    {
        $admin = Auth::guard('admin')->user();
        return view('pages.auth.profile', compact('admin'));
    }

    // Update name/email/phone
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
        ], [
            'phone.regex' => 'Enter a valid phone number.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $admin->update($request->only('name', 'email', 'phone'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => $admin,
        ]);
    }

    /**
     * STEP 1 of password change.
     * Validates current + new password, then emails a 6-digit code to the
     * email saved in the database (never to an email sent by the browser).
     * The new password is stored (hashed) in cache together with the hashed OTP,
     * so it is only applied after the code is verified.
     */
    public function sendPasswordOtp(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'success' => false,
                'errors'  => ['current_password' => ['Current password is wrong.']],
            ], 422);
        }

        if (Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'errors'  => ['password' => ['New password must be different from your current password.']],
            ], 422);
        }

        // Resend cooldown
        $cooldownUntil = Cache::get($this->cooldownKey($admin));
        if ($cooldownUntil && $cooldownUntil > now()->timestamp) {
            $wait = $cooldownUntil - now()->timestamp;
            return response()->json([
                'success'     => false,
                'message'     => "Please wait {$wait}s before requesting another code.",
                'retry_after' => $wait,
            ], 429);
        }

        $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(self::OTP_TTL_MINUTES);

        Cache::put($this->otpKey($admin), [
            'otp'        => Hash::make($otp),
            'password'   => Hash::make($request->password),
            'attempts'   => 0,
            'expires_at' => $expiresAt->timestamp,
        ], $expiresAt);

        Cache::put(
            $this->cooldownKey($admin),
            now()->addSeconds(self::OTP_RESEND_SECONDS)->timestamp,
            now()->addSeconds(self::OTP_RESEND_SECONDS)
        );

        try {
            Mail::to($admin->email)->send(new PasswordChangeOtpMail($admin->name, $otp, self::OTP_TTL_MINUTES));
        } catch (\Throwable $e) {
            Log::error('Password OTP mail failed: ' . $e->getMessage());
            Cache::forget($this->otpKey($admin));
            Cache::forget($this->cooldownKey($admin));

            return response()->json([
                'success' => false,
                'message' => 'We could not send the verification email. Please try again in a moment.',
            ], 500);
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Verification code sent to your email.',
            'masked_email' => $this->maskEmail($admin->email),
            'expires_in'   => self::OTP_TTL_MINUTES * 60,
            'resend_in'    => self::OTP_RESEND_SECONDS,
        ]);
    }

    /**
     * STEP 2 of password change.
     * Verifies the code (max 5 attempts, 10 min expiry) and applies the new password.
     */
    public function verifyPasswordOtp(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'otp' => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return $this->otpError('Enter the 6-digit code from your email.');
        }

        $key    = $this->otpKey($admin);
        $record = Cache::get($key);

        if (!$record || now()->timestamp >= $record['expires_at']) {
            Cache::forget($key);
            return $this->otpError('This code has expired. Please request a new one.', true);
        }

        if (!Hash::check($request->otp, $record['otp'])) {
            $record['attempts']++;
            $left = self::OTP_MAX_ATTEMPTS - $record['attempts'];

            if ($left <= 0) {
                Cache::forget($key);
                return $this->otpError('Too many wrong attempts. Please request a new code.', true);
            }

            Cache::put($key, $record, Carbon::createFromTimestamp($record['expires_at']));

            return $this->otpError("That code isn't right. {$left} " . Str::plural('attempt', $left) . ' left.');
        }

        // Code is correct -> apply the new password that was validated in step 1
        $admin->password = $record['password'];
        $admin->setRememberToken(Str::random(60)); // kills "remember me" sessions on other devices
        $admin->save();

        Cache::forget($key);
        Cache::forget($this->cooldownKey($admin));

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /* ---------------- helpers ---------------- */

    private function otpKey($admin): string
    {
        return 'pwd_change_otp:' . $admin->id;
    }

    private function cooldownKey($admin): string
    {
        return 'pwd_change_otp_cooldown:' . $admin->id;
    }

    private function otpError(string $message, bool $expired = false)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => ['otp' => [$message]],
            'expired' => $expired,
        ], 422);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));

        return $visible . str_repeat('•', max(3, mb_strlen($name) - 2)) . '@' . $domain;
    }
}