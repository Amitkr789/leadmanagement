<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        return view('superadminconsole.pages.console.plans.index', ['plans' => $this->all()]);
    }

    public function store(Request $r)
    {
        $d = $this->data($r);
        $slug = Str::slug($d['name']);
        if (Plan::where('slug', $slug)->exists()) $slug .= '-' . Str::lower(Str::random(3));
        $plan = Plan::create($d + ['slug' => $slug, 'sort_order' => (Plan::max('sort_order') ?? 0) + 1]);
        return $this->respond($plan, 'Plan created successfully.', 201);
    }

    public function update(Request $r, Plan $plan)
    {
        $plan->update($this->data($r, $plan));
        return $this->respond($plan, 'Plan updated.');
    }

    private function data(Request $r, ?Plan $p = null): array
    {
        $d = $r->validate([
            'name' => 'required|string|max:60|unique:plans,name' . ($p ? ",{$p->id}" : ''),
            'description' => 'nullable|string|max:140',
            'monthly_price' => 'required|integer|min:0|max:1000000',
            'yearly_price' => 'required|integer|min:0|max:10000000',
            'max_users' => 'required|integer|min:1|max:10000',
            'max_branches' => 'required|integer|min:1|max:1000',
            'features' => 'nullable|array', 'features.*' => 'in:' . implode(',', array_keys(Plan::FEATURES)),
        ]);
        $d['features'] = $d['features'] ?? [];
        $d['is_popular'] = $r->boolean('is_popular');
        $d['is_active'] = $r->boolean('is_active');
        return $d;
    }

    private function respond(Plan $plan, string $msg, int $code = 200)
    {
        if ($plan->is_popular) Plan::where('id', '!=', $plan->id)->update(['is_popular' => false]); // only one "Popular" plan
        return response()->json([
            'message' => $msg, 'plan' => ['slug' => $plan->slug],
            'grid' => view('superadminconsole.pages.console.plans._grid', ['plans' => $this->all()])->render(),                        // Plans page
            'choices' => view('superadminconsole.pages.console.plans._choices', ['plans' => Plan::active()->orderBy('sort_order')->get(), 'selected' => $plan->slug])->render(), // Add Company wizard
        ], $code);
    }

    private function all() { return Plan::withCount('companies')->orderBy('sort_order')->get(); }
}
