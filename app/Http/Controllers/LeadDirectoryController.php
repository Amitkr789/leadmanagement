<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Contacts + Companies: the "people and organisations" a lead is created from.
 * Flow in the UI: pick or create a Contact → pick or create a Company → fill in the lead.
 */
class LeadDirectoryController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    private function like(string $q): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    }

    /* ==================== CONTACTS ==================== */

    public function contactsSearch(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $q     = trim((string) $request->query('q', ''));

        $query = Contact::visibleTo($admin)->with('company:id,name')->withCount('leads');

        if ($q !== '') {
            $like = $this->like($q);
            $key  = Contact::phoneKey($q);
            $query->where(function ($w) use ($like, $key, $q) {
                $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('whatsapp', 'like', $like);
                if ($key && strlen($key) >= 4 && preg_match('/\d{4}/', $q)) {
                    $w->orWhere('phone_key', 'like', '%' . $key . '%');
                }
            });
        }

        return response()->json([
            'success'  => true,
            'contacts' => $query->orderByDesc('id')->limit(8)->get(),
        ]);
    }

    public function contactStore(Request $request): JsonResponse
    {
        $admin = $this->admin();

        $v = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'whatsapp'   => 'nullable|string|max:20',
            'job_title'  => 'nullable|string|max:120',
            'company_id' => 'nullable|integer|exists:companies,id',
        ]);

        $v->after(function ($v) use ($request) {
            if (!trim((string) $request->input('phone')) && !trim((string) $request->input('email'))) {
                $v->errors()->add('phone', 'Add a phone number or an e-mail address.');
            }
        });

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $data = $v->validated();

        // Duplicate check on phone (last 10 digits) and e-mail.
        $dup = Contact::query()
            ->where(function ($w) use ($data) {
                $key = Contact::phoneKey($data['phone'] ?? null);
                if ($key) {
                    $w->orWhere('phone_key', $key);
                }
                if (!empty($data['email'])) {
                    $w->orWhereRaw('LOWER(email) = ?', [mb_strtolower($data['email'])]);
                }
            })->first();

        if ($dup && (Contact::phoneKey($data['phone'] ?? null) || !empty($data['email']))) {
            $visible = Contact::visibleTo($admin)->with('company:id,name')->withCount('leads')->find($dup->id);

            return response()->json([
                'success'   => false,
                'duplicate' => true,
                'contact'   => $visible,   // null when it belongs to someone else's leads
                'message'   => $visible
                    ? 'A contact with this phone / e-mail already exists.'
                    : 'A contact with this phone / e-mail already exists and belongs to another team member.',
            ], 409);
        }

        $contact = Contact::create([
            'name'       => trim($data['name']),
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'whatsapp'   => $data['whatsapp'] ?? null,
            'job_title'  => $data['job_title'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'created_by' => $admin->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Contact created.', 'contact' => $contact->load('company:id,name')->loadCount('leads')]);
    }

    /* ==================== DEALS (picker in the create-lead flow) ==================== */

    /** Search the deals this admin can see — by deal name, company or contact. Open deals first. */
    public function dealsSearch(Request $request): JsonResponse
    {
        $admin     = $this->admin();
        $q         = trim((string) $request->query('q', ''));
        $contactId = (int) $request->query('contact_id', 0);

        $query = Deal::visibleTo($admin)->with(['company:id,name', 'owner:id,name'])->withCount('contacts');

        if ($contactId) {
            $query->withCount(['contacts as has_contact' => fn ($c) => $c->where('contacts.id', $contactId)]);
        }

        if ($q !== '') {
            $like = $this->like($q);
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('contacts', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        $deals = $query->orderByRaw("CASE WHEN stage IN ('won','lost') THEN 1 ELSE 0 END")->orderByDesc('id')->limit(8)->get()->map(fn ($d) => [
            'id'             => $d->id,
            'name'           => $d->name,
            'amount'         => (float) $d->amount,
            'stage'          => $d->stage,
            'stage_label'    => data_get(Deal::STAGES, $d->stage . '.label') ?: ucwords(str_replace('_', ' ', (string) $d->stage)),
            'close_date'     => $d->close_date instanceof \DateTimeInterface ? $d->close_date->format('Y-m-d') : $d->close_date,
            'company'        => $d->company ? ['id' => $d->company->id, 'name' => $d->company->name] : null,
            'owner'          => $d->owner?->name,
            'contacts_count' => (int) $d->contacts_count,
            'has_contact'    => (int) ($d->has_contact ?? 0) > 0,
        ]);

        return response()->json(['success' => true, 'deals' => $deals]);
    }

    /* ==================== COMPANIES ==================== */

    public function companiesSearch(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $q     = trim((string) $request->query('q', ''));

        $query = Company::visibleTo($admin)->withCount(['contacts', 'leads']);

        if ($q !== '') {
            $like = $this->like($q);
            $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('website', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('gst_number', 'like', $like));
        }

        return response()->json(['success' => true, 'companies' => $query->orderBy('name')->limit(8)->get()]);
    }

    public function companyStore(Request $request): JsonResponse
    {
        $admin = $this->admin();

        $v = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'website'    => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:30',
            'gst_number' => 'nullable|string|max:30',
            'address'    => 'nullable|string|max:500',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $data = $v->validated();

        $dup = Company::query()
            ->where(function ($w) use ($data) {
                $w->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data['name']))]);
                if (!empty($data['gst_number'])) {
                    $w->orWhereRaw('UPPER(gst_number) = ?', [mb_strtoupper(trim($data['gst_number']))]);
                }
            })->first();

        if ($dup) {
            $visible = Company::visibleTo($admin)->withCount(['contacts', 'leads'])->find($dup->id);

            return response()->json([
                'success'   => false,
                'duplicate' => true,
                'company'   => $visible,
                'message'   => $visible ? 'This company already exists.' : 'This company already exists and belongs to another team member.',
            ], 409);
        }

        $company = Company::create(array_merge($data, ['name' => trim($data['name']), 'created_by' => $admin->id]));

        return response()->json(['success' => true, 'message' => 'Company created.', 'company' => $company->loadCount(['contacts', 'leads'])]);
    }

    /** Inline edit of a company's details (from the lead drawer). */
    public function companyUpdate(Request $request, $id): JsonResponse
    {
        $company = Company::visibleTo($this->admin())->findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'website'    => 'sometimes|nullable|string|max:255',
            'phone'      => 'sometimes|nullable|string|max:30',
            'gst_number' => 'sometimes|nullable|string|max:30',
            'address'    => 'sometimes|nullable|string|max:500',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $data = $v->validated();
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        foreach (['website', 'phone', 'gst_number', 'address'] as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = trim((string) $data[$k]) === '' ? null : trim($data[$k]);
            }
        }

        // Renaming onto another company's name / GST number would create a duplicate.
        $dup = Company::where('id', '!=', $company->id)->where(function ($w) use ($data) {
            if (!empty($data['name'])) {
                $w->orWhereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])]);
            }
            if (!empty($data['gst_number'])) {
                $w->orWhereRaw('UPPER(gst_number) = ?', [mb_strtoupper($data['gst_number'])]);
            }
        })->first();
        if ((!empty($data['name']) || !empty($data['gst_number'])) && $dup) {
            return response()->json(['success' => false, 'duplicate' => true, 'message' => 'Another company already uses this name / GST number.'], 409);
        }

        $company->update($data);

        // Keep the copy on each lead in step.
        $mirror = [];
        if (isset($data['name'])) {
            $mirror['company_name'] = $company->name;
        }
        if (array_key_exists('gst_number', $data)) {
            $mirror['gst_number'] = $company->gst_number;
        }
        if ($mirror) {
            Lead::where('company_id', $company->id)->update($mirror);
        }

        return response()->json(['success' => true, 'message' => 'Company updated.', 'company' => $company->loadCount(['contacts', 'leads'])]);
    }
}