<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{Company, CompanyActivity};
use Illuminate\Http\Request;

/** Trials & Onboarding: every company that started on a free trial and has not converted yet. */
class TrialController extends Controller
{
    public function index(Request $r)
    {
        $base = Company::whereNotNull('trial_ends_at')->whereNull('subscription_ends_at');
        $filter = in_array($r->query('filter'), ['soon', 'expired'], true) ? $r->query('filter') : 'all';
        $q = (clone $base)->with('planDetail')->withCount(['users', 'branches'])->orderBy('trial_ends_at');
        if ($filter === 'soon') $q->where('status', 'trial')->whereBetween('trial_ends_at', [now(), now()->addDays(3)]);
        if ($filter === 'expired') $q->where('status', 'expired');

        return view('superadminconsole.pages.console.trials.index', [
            'trials' => $q->paginate(9)->withQueryString(), 'filter' => $filter,
            'stats' => [
                'trial' => (clone $base)->where('status', 'trial')->count(),
                'soon' => (clone $base)->where('status', 'trial')->whereBetween('trial_ends_at', [now(), now()->addDays(3)])->count(),
                'expired' => (clone $base)->where('status', 'expired')->count(),
                'converted' => CompanyActivity::where('type', 'upgraded')->where('message', 'like', 'Upgraded from%')->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }
}
