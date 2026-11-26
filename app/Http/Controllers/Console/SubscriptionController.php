<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\CompanyConsole;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SubscriptionController extends Controller
{
    public function index(Request $r)
    {
        // Company profile + Tenant subscription + Plan details
        $q = CompanyConsole::with([
            'tenant.plan',
        ])->whereHas('tenant');

        // Search company details
        if ($s = $r->query('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%")
                    ->orWhere('contact_name', 'like', "%{$s}%");
            });
        }

        // Filter by plan slug
        if ($r->filled('plan')) {
            $q->whereHas('tenant.plan', function ($w) use ($r) {
                $w->where('slug', $r->query('plan'));
            });
        }

        // Filter by tenant subscription status
        if ($r->filled('status')) {
            $q->whereHas('tenant', function ($w) use ($r) {
                $w->where('status', $r->query('status'));
            });
        }

        // Monthly Recurring Revenue (MRR)
        $mrr = DB::table('tenants')
            ->join('plans', 'plans.id', '=', 'tenants.plan_id')
            ->where('tenants.status', 'active')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN tenants.billing_cycle = 'yearly'
                            THEN plans.yearly_price / 12
                        ELSE plans.monthly_price
                    END
                ) AS m
            ")
            ->value('m');

        // Upcoming expiry window
        $soon = [
            now(),
            now()->copy()->addDays(7),
        ];

        $stats = [
            'active' => Tenant::where('status', 'active')->count(),

            'trial' => Tenant::where('status', 'trial')->count(),

            'mrr' => (int) round($mrr ?? 0),

            'expiring' => Tenant::whereIn('status', ['trial', 'active'])
                ->where(function ($w) use ($soon) {
                    $w->whereBetween('trial_ends_at', $soon)
                        ->orWhereBetween('subscription_ends_at', $soon);
                })
                ->count(),
        ];

        return view(
            'superadminconsole.pages.console.subscriptions.index',
            [
                // Blade variable name retained for compatibility.
                'companies' => $q->latest()->paginate(10)->withQueryString(),

                'plans' => Plan::orderBy('sort_order')->get(),

                'stats' => $stats,
            ]
        );
    }
}
