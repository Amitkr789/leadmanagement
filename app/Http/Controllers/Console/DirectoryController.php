<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Company, User};
use Illuminate\Http\Request;

/** Branches & Users of every company, in one place. */
class DirectoryController extends Controller
{
    public function index(Request $r)
    {
        $tab = $r->query('tab') === 'branches' ? 'branches' : 'users';
        $s = trim((string) $r->query('q'));
        $cid = $r->query('company_id');

        $users = User::whereNotNull('users.company_id')->join('companies', 'companies.id', '=', 'users.company_id')
            ->select('users.*', 'companies.name as company_name')->orderBy('companies.name')->orderBy('users.name');
        $branches = Branch::with('company:id,name,max_branches')->latest('id');
        if ($s !== '') {
            $users->where(fn ($w) => $w->where('users.name', 'like', "%$s%")->orWhere('users.email', 'like', "%$s%"));
            $branches->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('city', 'like', "%$s%"));
        }
        if ($cid) { $users->where('users.company_id', $cid); $branches->where('company_id', $cid); }

        return view('superadminconsole.pages.console.directory.index', [
            'tab' => $tab, 'companies' => Company::orderBy('name')->get(['id', 'name']),
            'rows' => ($tab === 'users' ? $users : $branches)->paginate(12)->withQueryString(),
            'branchCounts' => Branch::selectRaw('company_id, COUNT(*) AS c')->groupBy('company_id')->pluck('c', 'company_id'),
            'totals' => ['users' => User::whereNotNull('company_id')->count(), 'branches' => Branch::count()],
        ]);
    }
}
