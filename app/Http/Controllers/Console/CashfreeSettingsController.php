<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{FleetVerification, Setting};
use App\Services\Cashfree;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Console > Cashfree Verification: owner chooses sandbox / live, keys, endpoints and the monthly limit per company. */
class CashfreeSettingsController extends Controller
{
    public function edit()
    {
        $svc = collect(Cashfree::SERVICES)->map(fn ($label, $k) => ['label' => $label, 'on' => Setting::get("cashfree.svc.$k") === '1', 'path' => Cashfree::path($k)]);
        $month = FleetVerification::where('created_at', '>=', now()->startOfMonth());
        return view('superadminconsole.pages.console.settings.cashfree', [
            's' => ['mode' => Cashfree::mode(), 'client_id' => Setting::get('cashfree.client_id'), 'has_secret' => (bool) Setting::get('cashfree.client_secret'), 'enabled' => Setting::get('cashfree.enabled') === '1',
                'limit' => Cashfree::monthlyLimit(), 'api_version' => Setting::get('cashfree.api_version')],
            'services' => $svc, 'hosts' => ['sandbox' => 'https://sandbox.cashfree.com', 'live' => 'https://api.cashfree.com'],
            'usage' => ['calls' => (clone $month)->count(), 'ok' => (clone $month)->where('status', 'success')->count(), 'companies' => (clone $month)->distinct()->count('company_id')],
        ]);
    }

    public function update(Request $r)
    {
        $rules = ['mode' => 'required|in:sandbox,live', 'client_id' => 'required|string|max:120', 'client_secret' => 'nullable|string|min:8|max:200',
            'monthly_limit' => 'required|integer|min:1|max:100000', 'api_version' => ['nullable', 'regex:/^[0-9A-Za-z.\-]{1,30}$/']];
        foreach (array_keys(Cashfree::SERVICES) as $k) $rules["path.$k"] = ['nullable', 'regex:' . Cashfree::PATH_RULE];
        $d = $r->validate($rules, ['path.*.regex' => 'Use only a path that starts with "/", for example /verification/driving-license. No domain.', 'api_version.regex' => 'Letters, digits, dot and dash only.']);
        if (empty($d['client_secret']) && !Setting::get('cashfree.client_secret')) throw ValidationException::withMessages(['client_secret' => 'Enter the Client Secret.']);

        Setting::put('cashfree.mode', $d['mode']); Setting::put('cashfree.client_id', $d['client_id']); Setting::put('cashfree.monthly_limit', (string) $d['monthly_limit']); Setting::put('cashfree.api_version', $d['api_version'] ?? null);
        if (!empty($d['client_secret'])) Setting::putSecret('cashfree.client_secret', $d['client_secret']);
        foreach (array_keys(Cashfree::SERVICES) as $k) {
            Setting::put("cashfree.path.$k", $d['path'][$k] ?? '');
            Setting::put("cashfree.svc.$k", $r->boolean("svc.$k") ? '1' : '0');
        }
        Setting::put('cashfree.enabled', $r->boolean('enabled') ? '1' : '0');
        return response()->json(['message' => 'Cashfree settings saved.']);
    }

    public function test()
    {
        [$ok, $msg] = Cashfree::ping();
        return response()->json(['ok' => $ok, 'message' => $msg], $ok ? 200 : 422);
    }
}
