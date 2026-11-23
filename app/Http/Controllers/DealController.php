<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DealController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    private function relations(): array
    {
        return [
            'owner:id,name',
            'creator:id,name',
            'company:id,name,website,phone,gst_number,address',
            'contacts:id,name,email,phone,company_id',
        ];
    }

    private function fail($v): JsonResponse
    {
        return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
    }

    /* ==================== PAGE + LISTING ==================== */

    public function page()
    {
        return view('pages.deals', [
            'admins'       => Admin::orderBy('name')->get(['id', 'name']),
            'stages'       => Deal::STAGES,
            'openStages'   => Deal::OPEN_STAGES,
            'sources'      => ['Website', 'Referral', 'Cold Call', 'Social Media', 'Walk-in', 'Email Campaign', 'Trade Show', 'Other'],
            'isSuperAdmin' => $this->admin()->role === 'super_admin',
        ]);
    }

    /** One shot, like the leads page: filtering / sorting / pagination all happen client-side. */
    public function data(): JsonResponse
    {
        $admin = $this->admin();

        $deals = Deal::with($this->relations())->visibleTo($admin)->orderByDesc('id')->get();

        return response()->json([
            'success'       => true,
            'deals'         => $deals,
            'current_admin' => ['id' => $admin->id, 'name' => $admin->name, 'role' => $admin->role],
        ]);
    }

    /* ==================== CREATE ==================== */

    public function store(Request $request): JsonResponse
    {
        $admin = $this->admin();

        $v = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'amount'        => 'nullable|numeric|min:0|max:99999999999',
            'stage'         => 'required|in:' . implode(',', Deal::OPEN_STAGES),
            'priority'      => 'nullable|in:' . implode(',', Deal::PRIORITIES),
            'deal_type'     => 'nullable|in:' . implode(',', Deal::TYPES),
            'source'        => 'nullable|string|max:50',
            'close_date'    => 'nullable|date',
            'owner_id'      => 'nullable|integer|exists:admins,id',
            'company_id'    => 'nullable|integer|exists:companies,id',
            'contact_ids'   => 'nullable|array|max:20',
            'contact_ids.*' => 'integer|exists:contacts,id',
            'description'   => 'nullable|string|max:5000',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }
        $d = $v->validated();

        $deal = DB::transaction(function () use ($d, $admin) {
            $company = ! empty($d['company_id']) ? Company::visibleTo($admin)->findOrFail($d['company_id']) : null;

            $deal = Deal::create([
                'name'        => trim($d['name']),
                'amount'      => $d['amount'] ?? 0,
                'stage'       => $d['stage'],
                'probability' => Deal::STAGES[$d['stage']]['probability'],
                'priority'    => $d['priority'] ?? 'medium',
                'deal_type'   => $d['deal_type'] ?? null,
                'source'      => $d['source'] ?? null,
                'close_date'  => $d['close_date'] ?? null,
                'description' => $d['description'] ?? null,
                'owner_id'    => $d['owner_id'] ?? $admin->id,
                'company_id'  => $company?->id,
                'created_by'  => $admin->id,
            ]);

            $deal->contacts()->sync($this->visibleContactIds($admin, $d['contact_ids'] ?? []));

            return $deal;
        });

        return response()->json(['success' => true, 'message' => 'Deal created.', 'deal' => $deal->load($this->relations())]);
    }

    /* ==================== INLINE EDIT (drawer) ==================== */

    public function patch(Request $request, $id): JsonResponse
    {
        $admin = $this->admin();
        $deal  = Deal::visibleTo($admin)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'          => 'sometimes|required|string|max:255',
            'amount'        => 'sometimes|nullable|numeric|min:0|max:99999999999',
            'priority'      => 'sometimes|required|in:' . implode(',', Deal::PRIORITIES),
            'deal_type'     => 'sometimes|nullable|in:' . implode(',', Deal::TYPES),
            'source'        => 'sometimes|nullable|string|max:50',
            'close_date'    => 'sometimes|nullable|date',
            'probability'   => 'sometimes|nullable|integer|between:0,100',
            'owner_id'      => 'sometimes|nullable|integer|exists:admins,id',
            'company_id'    => 'sometimes|nullable|integer|exists:companies,id',
            'contact_ids'   => 'sometimes|nullable|array|max:20',
            'contact_ids.*' => 'integer|exists:contacts,id',
            'description'   => 'sometimes|nullable|string|max:5000',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }
        $d = $v->validated();

        DB::transaction(function () use ($d, $deal, $admin) {
            $own = array_intersect_key($d, array_flip(['name', 'amount', 'priority', 'deal_type', 'source', 'close_date', 'probability', 'owner_id', 'description']));

            if (array_key_exists('amount', $own)) {
                $own['amount'] = $own['amount'] ?? 0;
            }
            if (array_key_exists('probability', $own) && $own['probability'] === null) {
                $own['probability'] = Deal::STAGES[$deal->stage]['probability'];
            }
            if (array_key_exists('company_id', $d)) {
                $own['company_id'] = $d['company_id'] ? Company::visibleTo($admin)->findOrFail($d['company_id'])->id : null;
            }

            $deal->update($own);

            if (array_key_exists('contact_ids', $d)) {
                $deal->contacts()->sync($this->visibleContactIds($admin, $d['contact_ids'] ?? []));
            }
        });

        return response()->json(['success' => true, 'message' => 'Saved.', 'deal' => $deal->refresh()->load($this->relations())]);
    }

    /* ==================== STAGE (tracker / drag & drop) ==================== */

    public function updateStage(Request $request, $id): JsonResponse
    {
        $deal = Deal::visibleTo($this->admin())->findOrFail($id);

        $v = Validator::make($request->all(), [
            'stage'       => 'required|in:' . implode(',', array_keys(Deal::STAGES)),
            'lost_reason' => 'required_if:stage,lost|nullable|string|max:255',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }

        $stage  = $request->stage;
        $closed = in_array($stage, ['won', 'lost'], true);

        $deal->update([
            'stage'       => $stage,
            'probability' => Deal::STAGES[$stage]['probability'],
            'closed_at'   => $closed ? now() : null,
            'lost_reason' => $stage === 'lost' ? $request->lost_reason : null,
        ]);

        return response()->json(['success' => true, 'message' => 'Stage updated.', 'deal' => $deal->load($this->relations())]);
    }

    /* ==================== DELETE ==================== */

    public function destroy($id): JsonResponse
    {
        Deal::visibleTo($this->admin())->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Deal deleted.']);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['ids' => 'required|array|min:1|max:500', 'ids.*' => 'integer']);
        if ($v->fails()) {
            return $this->fail($v);
        }

        $count = Deal::visibleTo($this->admin())->whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true, 'message' => "{$count} deal(s) deleted."]);
    }

    /* ---------------- helpers ---------------- */

    /** Only contacts the admin may see can be attached. */
    private function visibleContactIds(Admin $admin, array $ids): array
    {
        return $ids ? Contact::visibleTo($admin)->whereIn('id', $ids)->pluck('id')->all() : [];
    }
}