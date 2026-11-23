<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Company, CompanyActivity, Invoice, Plan, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Password};
use Illuminate\Support\{Carbon, Str};
use Illuminate\Validation\{Rule, ValidationException};

class CompanyController extends Controller
{
    public const STATES = ['Rajasthan', 'Delhi', 'Maharashtra', 'Gujarat', 'Karnataka', 'Uttar Pradesh', 'West Bengal', 'Tamil Nadu', 'Telangana', 'Madhya Pradesh', 'Punjab', 'Haryana', 'Bihar', 'Kerala'];

    public function create()
    {
        $plans = Plan::active()->orderBy('sort_order')->get();
        return view('superadminconsole.pages.console.companies.create', ['plans' => $plans, 'states' => self::STATES, 'selected' => ($plans->firstWhere('is_popular', true) ?? $plans->first())?->slug]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:120',
            'contact_name' => 'required|string|max:100',
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email' => 'required|email:strict|max:150|unique:companies,email',
            'city' => 'required|string|max:80',
            'state' => 'required|string|max:80',
            'gst_number' => ['nullable', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'],
            'address' => 'nullable|string|max:300',
            'access_type' => 'required|in:trial,paid',
            'plan' => ['required', Rule::exists('plans', 'slug')->where('is_active', true)],
            'trial_days' => 'required_if:access_type,trial|nullable|integer|min:1|max:90',
            'billing_cycle' => 'required_if:access_type,paid|nullable|in:monthly,yearly',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email:strict|max:150|unique:users,email',
            'admin_password' => 'required|string|min:10|max:100',
        ], ['mobile.regex' => 'Enter a valid 10-digit Indian mobile number.', 'gst_number.regex' => 'Enter a valid 15-character GSTIN.']);

        $trial = $d['access_type'] === 'trial';
        $plan = Plan::where('slug', $d['plan'])->firstOrFail();
        $ends = $trial ? now()->addDays((int) $d['trial_days']) : ($d['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth());

        $company = DB::transaction(function () use ($d, $trial, $plan, $ends) {
            $c = Company::create([
                'name' => $d['name'], 'slug' => Str::slug($d['name']) . '-' . Str::lower(Str::random(4)),
                'contact_name' => $d['contact_name'], 'mobile' => $d['mobile'], 'email' => $d['email'],
                'city' => $d['city'], 'state' => $d['state'], 'gst_number' => strtoupper($d['gst_number'] ?? '') ?: null, 'address' => $d['address'] ?? null,
                'plan' => $d['plan'], 'status' => $trial ? 'trial' : 'active', 'billing_cycle' => $trial ? null : $d['billing_cycle'],
                'trial_ends_at' => $trial ? $ends : null, 'subscription_ends_at' => $trial ? null : $ends,
                'max_users' => $plan->max_users, 'max_branches' => $plan->max_branches, 'created_by' => Auth::guard('superadmin')->id(),
            ]);
            // First company admin; the owner shares the password once (shown only in the success dialog).
            (new User)->forceFill([
                'name' => $d['admin_name'], 'email' => $d['admin_email'], 'password' => Hash::make($d['admin_password']),
                'company_id' => $c->id, 'role' => 'admin', 'email_verified_at' => null,
            ])->save();
            CompanyActivity::log($c, 'created', $trial ? "Company created on a {$d['trial_days']}-day free trial ({$plan->name} plan)" : "Company created on the {$plan->name} plan ({$d['billing_cycle']})");
            if (!$trial) Invoice::issue($c, $plan, $d['billing_cycle'], now(), $ends);
            Branch::create(['company_id' => $c->id, 'name' => 'Head Office', 'city' => $d['city'], 'is_head_office' => true]);
            return $c;
        });

        return response()->json([
            'company' => ['name' => $company->name, 'access' => $trial
                ? "Free trial, ends {$ends->format('d M Y')}" : "{$plan->name} · " . ucfirst($d['billing_cycle']) . ", renews {$ends->format('d M Y')}"],
            'email' => $d['admin_email'], 'login_url' => route('login'),
        ], 201);
    }

    /** COMPANIES tab: every registered company with search + filters. */
    public function index(Request $r)
    {
        $q = Company::with('planDetail')->withCount('users')->latest('id');
        if ($s = trim((string) $r->query('q'))) $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('city', 'like', "%$s%")->orWhere('contact_name', 'like', "%$s%"));
        if ($r->filled('status')) $q->where('status', $r->query('status'));
        if ($r->filled('plan')) $q->where('plan', $r->query('plan'));
        return view('superadminconsole.pages.console.companies.index', [
            'companies' => $q->paginate(10)->withQueryString(), 'plans' => Plan::orderBy('sort_order')->get(),
            'stats' => ['total' => Company::count(), 'active' => Company::where('status', 'active')->count(), 'trial' => Company::where('status', 'trial')->count(), 'suspended' => Company::where('status', 'suspended')->count()],
        ]);
    }

    /** Emails the user a standard "choose a new password" link. */
    public function sendResetLink(Request $r, Company $company)
    {
        $user = $company->users()->findOrFail($r->validate(['user_id' => 'required|integer'])['user_id']);
        $status = Password::sendResetLink(['email' => $user->email]);
        if ($status !== Password::RESET_LINK_SENT) throw ValidationException::withMessages(['user_id' => __($status)]);
        CompanyActivity::log($company, 'password', "Password reset link emailed to {$user->email}");
        return response()->json(['message' => "Reset link sent to {$user->email}."]);
    }

    public function resetPassword(Request $r, Company $company)
    {
        $d = $r->validate(['user_id' => 'required|integer', 'password' => 'required|string|min:10|max:100']);
        $user = $company->users()->findOrFail($d['user_id']);
        $user->forceFill(['password' => Hash::make($d['password']), 'remember_token' => Str::random(60)])->save();
        if (config('session.driver') === 'database') DB::table('sessions')->where('user_id', $user->id)->delete(); // sign them out everywhere
        CompanyActivity::log($company, 'password', "Password changed for {$user->email}");
        return response()->json(['message' => "Password changed for {$user->name}."]);
    }

    /** HTML fragment for the company details modal (overview / edit / upgrade tabs). */
    public function show(Company $company)
    {
        return view('superadminconsole.pages.console.companies._detail', [
            'c' => $company->load('planDetail')->loadCount('users'),
            'plans' => Plan::where('is_active', true)->orWhere('slug', $company->plan)->orderBy('sort_order')->get(),
            'states' => self::STATES,
            'activities' => $company->activities()->with('admin:id,name')->latest('id')->limit(30)->get(),
            'invoices' => $company->invoices()->latest('id')->limit(10)->get(),
            'users' => $company->users()->orderByRaw("role = 'admin' desc")->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function update(Request $r, Company $company)
    {
        $d = $r->validate([
            'name' => 'required|string|max:120', 'contact_name' => 'required|string|max:100',
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['required', 'email:strict', 'max:150', Rule::unique('companies', 'email')->ignore($company->id)],
            'city' => 'required|string|max:80', 'state' => 'required|string|max:80',
            'gst_number' => ['nullable', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'], 'address' => 'nullable|string|max:300',
        ], ['mobile.regex' => 'Enter a valid 10-digit Indian mobile number.', 'gst_number.regex' => 'Enter a valid 15-character GSTIN.']);
        $d['gst_number'] = strtoupper($d['gst_number'] ?? '') ?: null;
        $company->update($d);
        CompanyActivity::log($company, 'updated', 'Company details edited');
        return response()->json(['message' => 'Company details updated.']);
    }

    /** Trial -> paid, or change plan / billing cycle. Issues an invoice whenever money is due. */
    public function upgrade(Request $r, Company $company)
    {
        $d = $r->validate([
            'plan' => ['required', Rule::exists('plans', 'slug')->where('is_active', true)],
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);
        $plan = Plan::where('slug', $d['plan'])->firstOrFail();
        $users = $company->users()->count();
        if ($users > $plan->max_users) {
            throw ValidationException::withMessages(['plan' => "{$company->name} has {$users} users but {$plan->name} allows only {$plan->max_users}. Remove users first or pick a bigger plan."]);
        }
        $cycle = $d['billing_cycle']; $yearly = $cycle === 'yearly';
        $activating = $company->status !== 'active';
        if (!$activating && $plan->slug === $company->plan && $cycle === $company->billing_cycle) return response()->json(['message' => 'No changes to apply.']);

        $old = $company->planDetail; $oldEnd = $company->subscription_ends_at; $from = $company->status;
        $restart = $activating || $company->billing_cycle !== $cycle || !$oldEnd;
        $ends = $restart ? ($yearly ? now()->addYear() : now()->addMonth()) : $oldEnd;

        DB::transaction(function () use ($company, $plan, $cycle, $old, $oldEnd, $from, $activating, $restart, $ends, $yearly) {
            $company->update(['plan' => $plan->slug, 'status' => 'active', 'billing_cycle' => $cycle, 'trial_ends_at' => null,
                'subscription_ends_at' => $ends, 'max_users' => $plan->max_users, 'max_branches' => $plan->max_branches]);
            if ($restart) {
                $inv = Invoice::issue($company, $plan, $cycle, now(), $ends);
                $msg = $activating ? "Upgraded from {$from} to {$plan->name} ({$cycle})" : "Billing cycle changed to {$cycle} on {$plan->name}";
            } else { // mid-cycle plan change: bill only the prorated price difference
                $price = fn ($p) => $p ? ($yearly ? $p->yearly_price : $p->monthly_price) : 0;
                $span = $yearly ? 365 : 30;
                $left = min(max(0, (int) now()->diffInDays($oldEnd, false)), $span);
                $due = (int) round(max(0, $price($plan) - $price($old)) * $left / $span);
                $inv = $due > 0 ? Invoice::issue($company, $plan, $cycle, now(), $oldEnd, $due, "Plan change to {$plan->name} (prorated)") : null;
                $msg = 'Plan changed from ' . ($old->name ?? '—') . " to {$plan->name}";
            }
            CompanyActivity::log($company, 'upgraded', $msg . ($inv ? ", invoice {$inv->invoice_no} issued" : ''));
        });
        return response()->json(['message' => $activating ? "{$company->name} is now on the {$plan->name} plan." : "Plan changed to {$plan->name}."]);
    }

    public function extendTrial(Request $r, Company $company)
    {
        $days = $r->validate(['days' => 'required|integer|min:1|max:90'])['days'];
        if (!$company->trial_ends_at || $company->subscription_ends_at) throw ValidationException::withMessages(['days' => 'Only trial companies can be extended.']);
        $base = $company->trial_ends_at->isFuture() ? $company->trial_ends_at : now();
        $company->update(['trial_ends_at' => $base->copy()->addDays($days), 'status' => $company->status === 'suspended' ? 'suspended' : 'trial']);
        CompanyActivity::log($company, 'trial_extended', "Trial extended by {$days} days (now ends {$company->trial_ends_at->format('d M Y')})");
        return response()->json(['message' => "Trial extended by {$days} days."]);
    }

    public function setStatus(Request $r, Company $company)
    {
        $action = $r->validate(['action' => 'required|in:suspend,reactivate'])['action'];
        if ($action === 'suspend') {
            $company->update(['status' => 'suspended']);
            CompanyActivity::log($company, 'suspended', 'Company suspended. Staff can no longer sign in.');
            return response()->json(['message' => 'Company suspended.']);
        }
        $to = $company->subscription_ends_at?->isFuture() ? 'active' : ($company->trial_ends_at?->isFuture() ? 'trial' : null);
        if (!$to) throw ValidationException::withMessages(['action' => 'The trial or subscription has ended. Use the Upgrade tab to reactivate.']);
        $company->update(['status' => $to]);
        CompanyActivity::log($company, 'reactivated', "Company reactivated ({$to})");
        return response()->json(['message' => 'Company reactivated.']);
    }

    public function destroy(Request $r, Company $company)
    {
        $r->validate(['confirm_name' => ['required', Rule::in([$company->name])]], ['confirm_name.in' => 'The name you typed does not match.']);
        DB::transaction(function () use ($company) { $company->users()->delete(); $company->delete(); });
        return response()->json(['message' => 'Company deleted.']);
    }
}