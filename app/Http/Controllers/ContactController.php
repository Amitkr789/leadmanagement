<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Contacts directory page: list everything the admin may see, open one, edit it inline, delete it.
 * Creating a contact reuses LeadDirectoryController@contactStore (route leads.contacts.store).
 */
class ContactController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    public function index()
    {
        return view('pages.contacts', [
            'admins'       => Admin::orderBy('name')->get(['id', 'name']),
            'isSuperAdmin' => $this->admin()->role === 'super_admin',
            'meId'         => $this->admin()->id,
            'leadTypes'    => Lead::LEAD_TYPES,
            'sources'      => ['Website', 'Referral', 'Cold Call', 'Social Media', 'Walk-in', 'Email Campaign', 'Trade Show', 'Other'],
            'platforms'    => ['Facebook', 'Instagram', 'LinkedIn', 'WhatsApp', 'Other'],
        ]);
    }

    /** One row of the table. $leads = the visible leads of this contact (newest first). */
    private function row(Contact $c, $leads): array
    {
        return [
            'id'               => $c->id,
            'name'             => $c->name,
            'email'            => $c->email,
            'phone'            => $c->phone,
            'whatsapp'         => $c->whatsapp,
            'job_title'        => $c->job_title,
            'company_id'       => $c->company_id,
            'company'          => $c->company ? ['id' => $c->company->id, 'name' => $c->company->name] : null,
            'created_by'       => $c->created_by,
            'creator'          => $c->creator ? ['id' => $c->creator->id, 'name' => $c->creator->name] : null,
            'created_at'       => optional($c->created_at)->toISOString(),
            'leads_count'      => $leads->count(),
            'latest_lead_type' => optional($leads->first())->lead_type,
        ];
    }

    public function data(): JsonResponse
    {
        $admin = $this->admin();

        $contacts = Contact::visibleTo($admin)
            ->with(['company:id,name', 'creator:id,name'])
            ->orderByDesc('id')
            ->get();

        $byContact = Lead::visibleTo($admin)
            ->whereNotNull('contact_id')
            ->orderByDesc('id')
            ->get(['id', 'contact_id', 'lead_type'])
            ->groupBy('contact_id');

        return response()->json([
            'success'  => true,
            'contacts' => $contacts->map(fn (Contact $c) => $this->row($c, $byContact->get($c->id, collect())))->values(),
        ]);
    }

    public function show($id): JsonResponse
    {
        $admin   = $this->admin();
        $contact = Contact::visibleTo($admin)->with(['company', 'creator:id,name'])->findOrFail($id);

        $leads = $contact->leads()->visibleTo($admin)
            ->with('assignedAdmins:id,name')
            ->orderByDesc('id')
            ->get(['id', 'contact_id', 'customer_name', 'product_name', 'source', 'lead_type', 'created_at', 'created_by']);

        $row            = $this->row($contact, $leads);
        $row['company'] = $contact->company ? $contact->company->only(['id', 'name', 'website', 'phone', 'gst_number', 'address']) : null;
        $row['leads']   = $leads->map(fn (Lead $l) => [
            'id'              => $l->id,
            'product_name'    => $l->product_name,
            'source'          => $l->source,
            'lead_type'       => $l->lead_type,
            'created_at'      => optional($l->created_at)->toISOString(),
            'assigned_admins' => $l->assignedAdmins->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])->values(),
        ])->values();

        return response()->json(['success' => true, 'contact' => $row]);
    }

    /** Inline edit — validates only the keys that were sent. */
    public function update(Request $request, $id): JsonResponse
    {
        $admin   = $this->admin();
        $contact = Contact::visibleTo($admin)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'       => 'sometimes|required|string|max:255',
            'email'      => 'sometimes|nullable|email|max:255',
            'phone'      => 'sometimes|nullable|string|max:20',
            'whatsapp'   => 'sometimes|nullable|string|max:20',
            'job_title'  => 'sometimes|nullable|string|max:120',
            'company_id' => 'sometimes|nullable|integer|exists:companies,id',
        ]);

        $v->after(function ($v) use ($request, $contact) {
            $phone = $request->has('phone') ? trim((string) $request->input('phone')) : (string) $contact->phone;
            $email = $request->has('email') ? trim((string) $request->input('email')) : (string) $contact->email;
            if ($phone === '' && $email === '') {
                $v->errors()->add('phone', 'A contact needs a phone number or an e-mail address.');
            }
        });

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $data = $v->validated();
        foreach (['email', 'phone', 'whatsapp', 'job_title'] as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = trim((string) $data[$k]) === '' ? null : trim($data[$k]);
            }
        }
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        if (!empty($data['company_id'])) {
            Company::visibleTo($admin)->findOrFail($data['company_id']);
        }

        // Another contact already using this phone (last 10 digits) / e-mail?
        $newPhone = array_key_exists('phone', $data) ? $data['phone'] : null;
        $newEmail = array_key_exists('email', $data) ? $data['email'] : null;
        $key      = Contact::phoneKey($newPhone);
        if ($key || $newEmail) {
            $dup = Contact::where('id', '!=', $contact->id)->where(function ($w) use ($key, $newEmail) {
                if ($key) {
                    $w->orWhere('phone_key', $key);
                }
                if ($newEmail) {
                    $w->orWhereRaw('LOWER(email) = ?', [mb_strtolower($newEmail)]);
                }
            })->first();

            if ($dup) {
                $mine = Contact::visibleTo($admin)->where('id', $dup->id)->exists();

                return response()->json([
                    'success'   => false,
                    'duplicate' => true,
                    'message'   => $mine
                        ? 'Another contact ("' . $dup->name . '") already uses this phone / e-mail.'
                        : 'Another contact already uses this phone / e-mail.',
                ], 409);
            }
        }

        DB::transaction(function () use ($contact, $data) {
            $contact->update($data);

            // Leads carry a copy of the contact's details; keep every lead in step.
            $mirror = [];
            foreach (['name' => 'customer_name', 'email' => 'email', 'phone' => 'phone', 'whatsapp' => 'whatsapp'] as $c => $l) {
                if (array_key_exists($c, $data)) {
                    $mirror[$l] = $l === 'phone' ? ($data[$c] ?? '') : $data[$c];
                }
            }
            if ($mirror) {
                Lead::where('contact_id', $contact->id)->update($mirror);
            }
        });

        $contact->refresh()->load(['company:id,name', 'creator:id,name']);
        $leads = $contact->leads()->visibleTo($admin)->orderByDesc('id')->get(['id', 'contact_id', 'lead_type']);

        return response()->json(['success' => true, 'message' => 'Saved.', 'contact' => $this->row($contact, $leads)]);
    }

    /** Why this contact can't be deleted, or null if it can. */
    private function blockReason(Contact $c, Admin $admin): ?string
    {
        if ($admin->role !== 'super_admin' && $c->created_by !== $admin->id) {
            return 'Only the person who created this contact (or a super admin) can delete it.';
        }
        $n = $c->leads()->count();

        return $n ? "This contact has {$n} lead" . ($n === 1 ? '' : 's') . '. Delete those leads first.' : null;
    }

    public function destroy($id): JsonResponse
    {
        $admin   = $this->admin();
        $contact = Contact::visibleTo($admin)->findOrFail($id);

        if ($reason = $this->blockReason($contact, $admin)) {
            return response()->json(['success' => false, 'message' => $reason], 422);
        }
        $contact->delete();

        return response()->json(['success' => true, 'message' => 'Contact deleted.']);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $ids   = array_filter((array) $request->input('ids', []), 'is_numeric');

        $deleted = [];
        $skipped = [];
        foreach (Contact::visibleTo($admin)->whereIn('id', $ids)->get() as $c) {
            if ($reason = $this->blockReason($c, $admin)) {
                $skipped[] = ['id' => $c->id, 'name' => $c->name, 'reason' => $reason];
                continue;
            }
            $c->delete();
            $deleted[] = $c->id;
        }

        $msg = count($deleted) . ' contact(s) deleted.' . ($skipped ? ' ' . count($skipped) . ' skipped (they still have leads or belong to someone else).' : '');

        return response()->json(['success' => true, 'message' => $msg, 'deleted' => $deleted, 'skipped' => $skipped]);
    }
}