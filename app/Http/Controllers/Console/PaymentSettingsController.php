<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Console > Payment Gateway: owner enters the Razorpay keys once. */
class PaymentSettingsController extends Controller
{
    public function edit()
    {
        return view('superadminconsole.pages.console.settings.payments', ['s' => [
            'mode' => Setting::get('razorpay.mode', 'test'), 'key_id' => Setting::get('razorpay.key_id'),
            'has_secret' => (bool) Setting::get('razorpay.key_secret'), 'has_webhook' => (bool) Setting::get('razorpay.webhook_secret'),
            'enabled' => Setting::get('razorpay.enabled') === '1',
        ], 'webhookUrl' => route('razorpay.webhook')]);
    }

    public function update(Request $r)
    {
        $d = $r->validate([
            'mode' => 'required|in:test,live',
            'key_id' => ['required', 'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/'],
            'key_secret' => 'nullable|string|min:10|max:100', 'webhook_secret' => 'nullable|string|min:6|max:100',
        ], ['key_id.regex' => 'A Razorpay Key ID looks like rzp_test_XXXXXXXX or rzp_live_XXXXXXXX.']);
        if (!str_starts_with($d['key_id'], "rzp_{$d['mode']}_")) throw ValidationException::withMessages(['key_id' => "This Key ID does not belong to {$d['mode']} mode."]);
        if (empty($d['key_secret']) && !Setting::get('razorpay.key_secret')) throw ValidationException::withMessages(['key_secret' => 'Enter the Key Secret.']);

        Setting::put('razorpay.mode', $d['mode']); Setting::put('razorpay.key_id', $d['key_id']);
        if (!empty($d['key_secret'])) Setting::putSecret('razorpay.key_secret', $d['key_secret']);
        if (!empty($d['webhook_secret'])) Setting::putSecret('razorpay.webhook_secret', $d['webhook_secret']);
        Setting::put('razorpay.enabled', $r->boolean('enabled') ? '1' : '0');
        return response()->json(['message' => 'Payment gateway settings saved.']);
    }

    public function test()
    {
        [$ok, $msg] = Razorpay::ping();
        return response()->json(['ok' => $ok, 'message' => $msg], $ok ? 200 : 422);
    }
}
