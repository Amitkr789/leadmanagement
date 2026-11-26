<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    public function index()
    {
        return view(
            'superadminconsole.pages.console.plans.index',
            ['plans' => $this->all()]
        );
    }

    public function store(Request $r)
    {
        $d = $this->data($r);

        $slug = Str::slug($d['name']);

        if (Plan::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(3));
        }

        $plan = Plan::create($d + [
            'slug' => $slug,
            'sort_order' => (Plan::max('sort_order') ?? 0) + 1,
        ]);

        return $this->respond($plan, 'Plan created successfully.', 201);
    }

    public function update(Request $r, Plan $plan)
    {
        $plan->update($this->data($r, $plan));

        return $this->respond($plan, 'Plan updated.');
    }

    private function data(Request $r, ?Plan $p = null): array
    {
        // A free plan has no price and no trial, whatever the browser sent.
        $free = $r->boolean('is_free');
        if ($free) {
            $r->merge(['monthly_price' => 0, 'yearly_price' => 0, 'trial_days' => 0]);
        }

        $d = $r->validate([
            'name' => 'required|string|max:60|unique:plans,name' . ($p ? ",{$p->id}" : ''),
            'description' => 'nullable|string|max:140',
            'monthly_price' => 'required|integer|min:0|max:1000000',
            'yearly_price' => 'required|integer|min:0|max:10000000',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'max_users' => 'required|integer|min:1|max:10000',
            'max_branches' => 'required|integer|min:1|max:1000',
            'features' => 'nullable|array',
            'features.*' => ['string', Rule::in(array_keys(Plan::featureMap()))],
        ]);

        if (! $free) {
            if ((int) $d['monthly_price'] === 0) {
                throw ValidationException::withMessages([
                    'monthly_price' => 'Enter a price, or set the plan type to Free.',
                ]);
            }

            if ((int) $d['yearly_price'] > (int) $d['monthly_price'] * 12) {
                throw ValidationException::withMessages([
                    'yearly_price' => 'Yearly price cannot be more than 12 × the monthly price.',
                ]);
            }
        }

        $d['features'] = $d['features'] ?? [];
        $d['trial_days'] = (int) ($d['trial_days'] ?? 0);
        $d['is_free'] = $free;
        $d['is_popular'] = $r->boolean('is_popular');
        $d['is_active'] = $r->boolean('is_active');

        return $d;
    }

    private function respond(Plan $plan, string $msg, int $code = 200)
    {
        // Only one popular plan at a time.
        if ($plan->is_popular) {
            Plan::where('id', '!=', $plan->id)
                ->update(['is_popular' => false]);
        }

        return response()->json([
            'message' => $msg,
            'plan' => [
                'slug' => $plan->slug,
            ],
            'grid' => view(
                'superadminconsole.pages.console.plans._grid',
                ['plans' => $this->all()]
            )->render(),
            'choices' => view(
                'superadminconsole.pages.console.plans._choices',
                [
                    'plans' => Plan::active()->orderBy('sort_order')->get(),
                    'selected' => $plan->slug,
                ]
            )->render(),
        ], $code);
    }

    private function all()
    {
        return Plan::withCount('tenants')
            ->orderBy('sort_order')
            ->get();
    }

    public function changeStatus(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'action' => 'required|in:suspend,unsuspend',
        ]);

        $plan->update([
            'is_active' => $data['action'] === 'unsuspend',
        ]);

        return $this->actionResponse(
            $plan,
            $data['action'] === 'suspend'
                ? 'Plan suspended successfully.'
                : 'Plan activated successfully.'
        );
    }

    public function destroy(Plan $plan)
    {
        // A plan currently assigned to a tenant cannot be deleted.
        if ($plan->tenants()->exists()) {
            return response()->json([
                'message' => 'This plan is assigned to existing companies. Suspend it instead of deleting it.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
            'grid' => view(
                'superadminconsole.pages.console.plans._grid',
                ['plans' => $this->all()]
            )->render(),
            'choices' => view(
                'superadminconsole.pages.console.plans._choices',
                [
                    'plans' => Plan::active()->orderBy('sort_order')->get(),
                    'selected' => null,
                ]
            )->render(),
        ]);
    }

    private function actionResponse(Plan $plan, string $message)
    {
        return response()->json([
            'message' => $message,
            'plan' => ['slug' => $plan->slug],
            'grid' => view(
                'superadminconsole.pages.console.plans._grid',
                ['plans' => $this->all()]
            )->render(),
            'choices' => view(
                'superadminconsole.pages.console.plans._choices',
                [
                    'plans' => Plan::active()->orderBy('sort_order')->get(),
                    'selected' => $plan->slug,
                ]
            )->render(),
        ]);
    }
}