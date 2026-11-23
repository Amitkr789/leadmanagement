<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{Company, Plan};
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $r)
    {
        $q = Company::with('planDetail')->latest();
        if ($s = $r->query('q')) $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('city', 'like', "%$s%"));
        if ($r->filled('plan')) $q->where('plan', $r->query('plan'));
        if ($r->filled('status')) $q->where('status', $r->query('status'));

        $soon = [now(), now()->addDays(7)];
        $mrr = Company::where('companies.status', 'active')->join('plans', 'plans.slug', '=', 'companies.plan')
            ->selectRaw("SUM(CASE WHEN companies.billing_cycle = 'yearly' THEN plans.yearly_price / 12 ELSE plans.monthly_price END) AS m")->value('m');

        return view('superadminconsole.pages.console.subscriptions.index', [
            'companies' => $q->paginate(10)->withQueryString(),
            'plans' => Plan::orderBy('sort_order')->get(),
            'stats' => [
                'active' => Company::where('status', 'active')->count(), 'trial' => Company::where('status', 'trial')->count(), 'mrr' => (int) round($mrr),
                'expiring' => Company::whereIn('status', ['trial', 'active'])->where(fn ($w) => $w->whereBetween('trial_ends_at', $soon)->orWhereBetween('subscription_ends_at', $soon))->count(),
            ],
        ]);
    }
}
