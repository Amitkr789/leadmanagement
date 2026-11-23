<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Companies directory page. Creating a company reuses LeadDirectoryController@companyStore
 * and editing one reuses LeadDirectoryController@companyUpdate (routes leads.companies.*).
 */
class CompanyOpenController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    public function index()
    {
        return view('pages.companies', [
            'admins'       => Admin::orderBy('name')->get(['id', 'name']),
            'meId'         => Auth::guard('admin')->id(),
            'isSuperAdmin' =>$this->admin()->role === 'super_admin',
        ]);
    }

    private function row(Company $c): array
    {
        return [
            'id'             => $c->id,
            'name'           => $c->name,
            'website'        => $c->website,
            'phone'          => $c->phone,
            'gst_number'     => $c->gst_number,
            'address'        => $c->address,
            'created_by'     => $c->created_by,
            'creator'        => $c->creator ? ['id' => $c->creator->id, 'name' => $c->creator->name] : null,
            'created_at'     => optional($c->created_at)->toISOString(),
            'contacts_count' => (int) ($c->contacts_count ?? 0),
            'leads_count'    => (int) ($c->leads_count ?? 0),
        ];
    }

    private function counted(Admin $admin)
    {
        return Company::visibleTo($admin)->with('creator:id,name')->withCount([
            'contacts' => fn ($q) => $q->visibleTo($admin),
            'leads'    => fn ($q) => $q->visibleTo($admin),
        ]);
    }

    public function data(): JsonResponse
    {
        $admin = $this->admin();

        return response()->json([
            'success'   => true,
            'companies' => $this->counted($admin)->orderByDesc('id')->get()->map(fn (Company $c) => $this->row($c))->values(),
        ]);
    }

    public function show($id): JsonResponse
    {
        $admin   = $this->admin();
        $company = $this->counted($admin)->findOrFail($id);
        $row     = $this->row($company);

        $row['contacts'] = $company->contacts()->visibleTo($admin)->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'job_title'])
            ->map(fn ($c) => $c->only(['id', 'name', 'email', 'phone', 'job_title']))->values();

        $row['leads'] = $company->leads()->visibleTo($admin)->orderByDesc('id')
            ->get(['id', 'customer_name', 'product_name', 'source', 'lead_type', 'created_at'])
            ->map(fn (Lead $l) => [
                'id'            => $l->id,
                'customer_name' => $l->customer_name,
                'product_name'  => $l->product_name,
                'source'        => $l->source,
                'lead_type'     => $l->lead_type,
                'created_at'    => optional($l->created_at)->toISOString(),
            ])->values();

        return response()->json(['success' => true, 'company' => $row]);
    }

    private function blockReason(Company $c, Admin $admin): ?string
    {
        if ($admin->role !== 'super_admin' && $c->created_by !== $admin->id) {
            return 'Only the person who created this company (or a super admin) can delete it.';
        }
        $contacts = Contact::where('company_id', $c->id)->count();
        $leads    = Lead::where('company_id', $c->id)->count();
        if ($contacts || $leads) {
            return "This company still has {$contacts} contact(s) and {$leads} lead(s). Move or delete them first.";
        }

        return null;
    }

    public function destroy($id): JsonResponse
    {
        $admin   = $this->admin();
        $company = Company::visibleTo($admin)->findOrFail($id);

        if ($reason = $this->blockReason($company, $admin)) {
            return response()->json(['success' => false, 'message' => $reason], 422);
        }
        $company->delete();

        return response()->json(['success' => true, 'message' => 'Company deleted.']);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $ids   = array_filter((array) $request->input('ids', []), 'is_numeric');

        $deleted = [];
        $skipped = [];
        foreach (Company::visibleTo($admin)->whereIn('id', $ids)->get() as $c) {
            if ($reason = $this->blockReason($c, $admin)) {
                $skipped[] = ['id' => $c->id, 'name' => $c->name, 'reason' => $reason];
                continue;
            }
            $c->delete();
            $deleted[] = $c->id;
        }

        $msg = count($deleted) . ' company(ies) deleted.' . ($skipped ? ' ' . count($skipped) . ' skipped (they still have contacts or leads, or belong to someone else).' : '');

        return response()->json(['success' => true, 'message' => $msg, 'deleted' => $deleted, 'skipped' => $skipped]);
    }
}