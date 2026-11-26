<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadAttachment;
use App\Models\LeadReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LeadManageController extends Controller
{
    private function currentAdmin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    /* ==================== PAGE ==================== */

    public function Leadmanage()
    {
        return view('pages.lead', [
            'admins'       => Admin::orderBy('name')->get(['id', 'name']),
            'leadTypes'    => Lead::LEAD_TYPES,
            'sources'      => ['Website', 'Referral', 'Cold Call', 'Social Media', 'Walk-in', 'Email Campaign', 'Trade Show', 'Other'],
            'platforms'    => ['Facebook', 'Instagram', 'LinkedIn', 'WhatsApp', 'Other'],
            // Sirf super_admin ko "view leads by user" wala search-select dikhega.
            'isSuperAdmin' => $this->currentAdmin()->role === 'super_admin',
        ]);
    }

    /* ==================== LISTING (AJAX) ====================
       Returns every lead the current admin is allowed to see, in one
       shot — filtering/search/pagination/category tabs (aur ab
       "view leads by user" bhi, super_admin ke liye) sab client-side
       lead.js me handle hote hai, kyunki super_admin ko yahin par
       saare leads already mil jaate hai visibleTo() se.

       Reminders aur Attachments dono per-admin scoped hai: super_admin
       sab dekhta hai, baaki sirf apna khud ka (jo unhone create/upload
       kiya ho), even if lead khud shared/assigned hai. Reminders yahan
       type ('reminder' ya 'note') dono ke saath aate hai — front-end
       (lead.blade.php) inhe do alag sections me split karta hai. */

    public function data()
    {
        $admin = $this->currentAdmin();

        $leads = Lead::query()
            ->with([
                'creator:id,name',
                'assignedAdmins:id,name',
                'contact:id,name,email,phone,whatsapp,company_id',
                'company:id,name,website,phone,gst_number,address',
                'reminders' => fn ($q) => $q->visibleTo($admin),
                'attachments' => fn ($q) => $q->visibleTo($admin)->with('uploader:id,name'),
            ])
            ->visibleTo($admin)
            ->orderByDesc('is_pinned')
            ->orderByDesc('id')
            ->get();

        $this->attachDeals($leads, $admin);

        return response()->json([
            'success' => true,
            'leads' => $leads,
            'current_admin' => ['id' => $admin->id, 'name' => $admin->name, 'role' => $admin->role],
        ]);
    }

    /* ==================== CREATE ====================
       A lead is created FROM a contact (and optionally a company):
         - contact_id            → use that contact (must be visible to the admin)
         - or customer_name+phone → find a contact with the same phone, otherwise create one
       The contact's name / phone / e-mail / WhatsApp and the company's name are copied onto the
       lead row, so reminders, bills, the share card and the alarm popup keep working as before. */

    public function store(Request $request)
    {
        $validator = $this->leadValidator($request, true);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors(), 'message' => $validator->errors()->first()], 422);
        }

        $admin = $this->currentAdmin();

        $result = DB::transaction(function () use ($request, $admin) {
            if ($request->filled('contact_id')) {
                $contact = Contact::visibleTo($admin)->findOrFail($request->contact_id);
            } else {
                $key     = Contact::phoneKey($request->phone);
                $contact = $key ? Contact::visibleTo($admin)->where('phone_key', $key)->first() : null;
                $contact ??= Contact::create([
                    'name'       => trim($request->customer_name),
                    'email'      => $request->email ?: null,
                    'phone'      => $request->phone,
                    'whatsapp'   => $request->whatsapp ?: null,
                    'created_by' => $admin->id,
                ]);
            }

            $company = $request->filled('company_id') ? Company::visibleTo($admin)->findOrFail($request->company_id) : ($contact->company_id ? Company::find($contact->company_id) : null);

            if ($company && !$contact->company_id) {
                $contact->update(['company_id' => $company->id]);
            }

            // Deal: a deal is tied to a lead through the lead's CONTACT (deal <-> contacts), so linking = adding the contact.
            $deal = null;
            if ($request->filled('deal_id')) {
                $deal = Deal::visibleTo($admin)->findOrFail($request->deal_id);
                $deal->contacts()->syncWithoutDetaching([$contact->id]);
                if ($company && !$deal->company_id) {
                    $deal->update(['company_id' => $company->id]);
                }
            } elseif ($request->filled('new_deal.name')) {
                $nd   = $request->input('new_deal');
                $deal = Deal::create([
                    'name'        => trim($nd['name']),
                    'amount'      => $nd['amount'] ?? 0,
                    'stage'       => $nd['stage'],
                    'probability' => Deal::STAGES[$nd['stage']]['probability'],
                    'priority'    => $nd['priority'] ?? 'medium',
                    'deal_type'   => $nd['deal_type'] ?? null,
                    'source'      => $nd['source'] ?? null,
                    'close_date'  => $nd['close_date'] ?? null,
                    'description' => $nd['description'] ?? null,
                    'owner_id'    => $admin->id,
                    'company_id'  => $company?->id,
                    'created_by'  => $admin->id,
                ]);
                $deal->contacts()->sync([$contact->id]);
            }

            $lead = Lead::create([
                'customer_name'   => $contact->name,
                'email'           => $contact->email,
                'phone'           => $contact->phone ?? '',
                'whatsapp'        => $contact->whatsapp,
                'contact_id'      => $contact->id,
                'company_id'      => $company?->id,
                'company_name'    => $company?->name,
                'gst_number'      => $company?->gst_number,
                'product_name'    => trim((string) $request->product_name) !== '' ? $request->product_name : ($deal?->name ?? ''),
                'source'          => $request->source,
                'social_platform' => $request->source === 'Social Media' ? $request->social_platform : null,
                'lead_type'       => $request->lead_type,
                'created_by'      => $admin->id,
            ]);

            $lead->assignedAdmins()->sync($request->input('assigned_admins', []));

            return [$lead, $deal, $request->filled('deal_id')];
        });

        [$lead, $deal, $linkedExisting] = $result;

        $lead->load($this->leadRelations());
        $this->attachDeals([$lead], $admin);

        $message = 'Lead added successfully.';
        if ($deal) {
            $message = $linkedExisting ? 'Lead added and linked to the deal "' . $deal->name . '".' : 'Lead and deal created.';
        }

        return response()->json(['success' => true, 'message' => $message, 'lead' => $lead, 'deal_id' => $deal?->id]);
    }

    /* ==================== UPDATE ==================== */

    public function update(Request $request, $id)
    {
        $lead = Lead::visibleTo($this->currentAdmin())->findOrFail($id);

        $validator = $this->leadValidator($request);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lead->update([
            'customer_name' => $request->customer_name,
            'product_name' => $request->product_name ?? '',
            'whatsapp' => $request->whatsapp,
            'phone' => $request->phone,
            'source' => $request->source,
            'social_platform' => $request->source === 'Social Media' ? $request->social_platform : null,
            'lead_type' => $request->lead_type,
        ]);

        $this->syncContactFromLead($lead, $request->only(['customer_name', 'phone', 'whatsapp']) + ($request->has('email') ? ['email' => $request->email] : []));

        $lead->assignedAdmins()->sync($request->input('assigned_admins', []));
        $lead->load($this->leadRelations());

        return response()->json(['success' => true, 'message' => 'Lead updated successfully.', 'lead' => $lead]);
    }

    private function leadValidator(Request $request, bool $creating = false)
    {
        return Validator::make($request->all(), [
            // creating: either an existing contact, or the name + phone of a new one
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'customer_name' => $creating ? 'required_without:contact_id|nullable|string|max:255' : 'required|string|max:255',
            'phone' => $creating ? 'required_without:contact_id|nullable|string|max:20' : 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'company_id' => 'nullable|integer|exists:companies,id',
            'product_name' => 'nullable|string|max:255',
            // creating only: link an existing deal, or create a new one together with the lead
            'deal_id' => $creating ? 'nullable|integer|exists:deals,id|prohibits:new_deal' : 'prohibited',
            'new_deal' => $creating ? 'nullable|array' : 'prohibited',
            'new_deal.name' => 'required_with:new_deal|string|max:255',
            'new_deal.stage' => 'required_with:new_deal|in:' . implode(',', Deal::OPEN_STAGES),
            'new_deal.amount' => 'nullable|numeric|min:0|max:99999999999',
            'new_deal.close_date' => 'nullable|date',
            'new_deal.priority' => 'nullable|in:' . implode(',', Deal::PRIORITIES),
            'new_deal.deal_type' => 'nullable|in:' . implode(',', Deal::TYPES),
            'new_deal.source' => 'nullable|string|max:50',
            'new_deal.description' => 'nullable|string|max:5000',
            'source' => 'required|string|max:50',
            'social_platform' => 'nullable|string|max:50',
            'lead_type' => 'required|in:' . implode(',', Lead::LEAD_TYPES),
            'assigned_admins' => 'nullable|array',
            'assigned_admins.*' => 'exists:admins,id',
        ]);
    }

    private function leadRelations(): array
    {
        return [
            'creator:id,name',
            'assignedAdmins:id,name',
            'contact:id,name,email,phone,whatsapp,company_id',
            'company:id,name,website,phone,gst_number,address',
        ];
    }

    /** The contact is the source of truth for name / phone / e-mail / WhatsApp: push edits to it and to its other leads. */
    private function syncContactFromLead(Lead $lead, array $fields): void
    {
        $map = ['customer_name' => 'name', 'email' => 'email', 'phone' => 'phone', 'whatsapp' => 'whatsapp'];
        $contactData = [];
        foreach ($map as $leadCol => $contactCol) {
            if (array_key_exists($leadCol, $fields)) {
                $contactData[$contactCol] = $fields[$leadCol] === '' ? null : $fields[$leadCol];
            }
        }
        if (!$contactData || !$lead->contact_id || !($contact = Contact::find($lead->contact_id))) {
            return;
        }

        $contact->update($contactData);

        $mirror = [];
        foreach ($map as $leadCol => $contactCol) {
            if (array_key_exists($contactCol, $contactData)) {
                $mirror[$leadCol] = $leadCol === 'phone' ? ($contactData[$contactCol] ?? '') : $contactData[$contactCol];
            }
        }
        Lead::where('contact_id', $contact->id)->where('id', '!=', $lead->id)->update($mirror);
    }

    /* ==================== INLINE FIELD EDIT (drawer) ====================
       PATCH /leads/{id} with any subset of the editable fields. Used by click-to-edit in the
       lead drawer, so it validates only what was sent. */

    public function patch(Request $request, $id)
    {
        $admin = $this->currentAdmin();
        $lead  = Lead::visibleTo($admin)->findOrFail($id);

        $rules = [
            'customer_name'     => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|nullable|email|max:255',
            'phone'             => 'sometimes|required|string|max:20',
            'whatsapp'          => 'sometimes|nullable|string|max:20',
            'product_name'      => 'sometimes|nullable|string|max:255',
            'source'            => 'sometimes|required|string|max:50',
            'social_platform'   => 'sometimes|nullable|string|max:50',
            'lead_type'         => 'sometimes|required|in:' . implode(',', Lead::LEAD_TYPES),
            'company_id'        => 'sometimes|nullable|integer|exists:companies,id',
            'assigned_admins'   => 'sometimes|nullable|array',
            'assigned_admins.*' => 'exists:admins,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors(), 'message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $lead, $admin) {
            $own = array_intersect_key($data, array_flip(['customer_name', 'email', 'phone', 'whatsapp', 'product_name', 'source', 'social_platform', 'lead_type']));

            if (array_key_exists('product_name', $own)) {
                $own['product_name'] = $own['product_name'] ?? '';
            }
            if (array_key_exists('phone', $own)) {
                $own['phone'] = $own['phone'] ?? '';
            }
            if (isset($own['source']) && $own['source'] !== 'Social Media') {
                $own['social_platform'] = null;
            }

            if (array_key_exists('company_id', $data)) {
                $company = $data['company_id'] ? Company::visibleTo($admin)->findOrFail($data['company_id']) : null;
                $own['company_id']   = $company?->id;
                $own['company_name'] = $company?->name;
                $own['gst_number']   = $company?->gst_number;
                if ($company && $lead->contact_id) {
                    Contact::where('id', $lead->contact_id)->whereNull('company_id')->update(['company_id' => $company->id]);
                }
            }

            $lead->update($own);
            $this->syncContactFromLead($lead, array_intersect_key($data, array_flip(['customer_name', 'email', 'phone', 'whatsapp'])));

            if (array_key_exists('assigned_admins', $data)) {
                $lead->assignedAdmins()->sync($data['assigned_admins'] ?? []);
            }
        });

        $lead->refresh()->load($this->leadRelations());

        return response()->json(['success' => true, 'message' => 'Saved.', 'lead' => $lead]);
    }

    /* ==================== BULK STATUS ==================== */

    public function bulkType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'       => 'required|array|min:1|max:500',
            'ids.*'     => 'integer',
            'lead_type' => 'required|in:' . implode(',', Lead::LEAD_TYPES),
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors(), 'message' => $validator->errors()->first()], 422);
        }

        $count = Lead::visibleTo($this->currentAdmin())->whereIn('id', $request->ids)->update(['lead_type' => $request->lead_type]);

        return response()->json(['success' => true, 'message' => "{$count} lead(s) moved to " . ucfirst($request->lead_type) . '.', 'count' => $count]);
    }

    /* ==================== INLINE STATUS (lead type) UPDATE ====================
       Used by the "view lead" offcanvas so the lead's status/type can be
       changed right there — no need to open the full edit form. Only
       touches lead_type, so it stays valid even if other required fields
       (phone, product, etc.) were left blank on an older record. */

    public function updateType(Request $request, $id)
    {
        $lead = Lead::visibleTo($this->currentAdmin())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'lead_type' => 'required|in:' . implode(',', Lead::LEAD_TYPES),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lead->lead_type = $request->lead_type;
        $lead->save();
        $lead->load($this->leadRelations());

        return response()->json(['success' => true, 'message' => 'Lead status updated.', 'lead' => $lead]);
    }

    /* ==================== DELETE ==================== */

    public function destroy($id)
    {
        Lead::visibleTo($this->currentAdmin())->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Lead deleted.']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        $count = Lead::visibleTo($this->currentAdmin())->whereIn('id', $ids)->count();
        Lead::visibleTo($this->currentAdmin())->whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => "{$count} lead(s) deleted."]);
    }

    /* ==================== PIN ==================== */

    public function togglePin($id)
    {
        $lead = Lead::visibleTo($this->currentAdmin())->findOrFail($id);
        $lead->is_pinned = ! $lead->is_pinned;
        $lead->save();

        return response()->json(['success' => true, 'is_pinned' => $lead->is_pinned]);
    }

    /* ==================== DETAIL ==================== */

    public function show($id)
    {
        $admin = $this->currentAdmin();

        $lead = Lead::visibleTo($admin)
            ->with([
                'creator:id,name',
                'assignedAdmins:id,name',
                'contact:id,name,email,phone,whatsapp,company_id',
                'company:id,name,website,phone,gst_number,address',
                'reminders' => fn ($q) => $q->visibleTo($admin),
                'attachments' => fn ($q) => $q->visibleTo($admin)->with('uploader:id,name'),
            ])
            ->findOrFail($id);

        $this->attachDeals([$lead], $admin);

        return response()->json(['success' => true, 'lead' => $lead]);
    }

    /* ==================== LEAD PROFILE PAGE ====================
       Full page that opens when a lead's name is clicked anywhere in the CRM.
       GET /admin/leads/{id}/view  (route name: leads.profile) */

    public function view($id)
    {
        $admin = $this->currentAdmin();

        $lead = Lead::visibleTo($admin)->with($this->leadRelations())->findOrFail($id);
        $this->attachDeals([$lead], $admin);

        return view('pages.lead-view', [
            'lead'         => $lead,
            'leadTypes'    => Lead::LEAD_TYPES,
            'isSuperAdmin' => $admin->role === 'super_admin',
        ]);
    }

    /* ==================== DEALS LINKED TO A LEAD ====================
       A deal is tied to a lead through the lead's CONTACT (deal <-> contacts), and more loosely through the
       lead's COMPANY (same company on the deal). Each lead gets a `linked_deals` array — id, name, amount,
       stage, close date, owner and `via` ("contact" | "company") — used for the deal links on the leads page.
       Never breaks the page: if deals can't be read, the lead simply has none. */

    private function attachDeals($leads, Admin $admin): void
    {
        $leads = collect($leads)->filter()->values();
        foreach ($leads as $l) {
            $l->setAttribute('linked_deals', []);
        }

        $contactIds = $leads->pluck('contact_id')->filter()->unique()->values()->all();
        $companyIds = $leads->pluck('company_id')->filter()->unique()->values()->all();
        if (! $contactIds && ! $companyIds) {
            return;
        }

        try {
            $deals = Deal::visibleTo($admin)
                ->where(function ($q) use ($contactIds, $companyIds) {
                    if ($contactIds) {
                        $q->whereHas('contacts', fn ($c) => $c->whereIn('contacts.id', $contactIds));
                    }
                    if ($companyIds) {
                        $q->orWhereIn('company_id', $companyIds);
                    }
                })
                ->with(['contacts:id', 'owner:id,name'])
                ->orderByDesc('id')
                ->get(['id', 'name', 'amount', 'stage', 'priority', 'close_date', 'owner_id', 'company_id']);
        } catch (\Throwable $e) {
            report($e);

            return;
        }

        foreach ($leads as $l) {
            $mine = [];
            foreach ($deals as $d) {
                $viaContact = $l->contact_id && $d->contacts->contains('id', $l->contact_id);
                $viaCompany = $l->company_id && (int) $d->company_id === (int) $l->company_id;
                if (! $viaContact && ! $viaCompany) {
                    continue;
                }
                $mine[] = [
                    'id'          => $d->id,
                    'name'        => $d->name,
                    'amount'      => (float) $d->amount,
                    'stage'       => $d->stage,
                    'stage_label' => data_get(Deal::STAGES, $d->stage . '.label') ?: ucwords(str_replace('_', ' ', (string) $d->stage)),
                    'priority'    => $d->priority,
                    'close_date'  => $d->close_date instanceof \DateTimeInterface ? $d->close_date->format('Y-m-d') : $d->close_date,
                    'owner'       => $d->owner?->name,
                    'via'         => $viaContact ? 'contact' : 'company',
                ];
            }
            $l->setAttribute('linked_deals', $mine);
        }
    }

    /* ==================== ATTACHMENTS ====================
       storeAttachment: koi bhi admin jo lead dekh sakta hai (visibleTo)
       usme attachment upload kar sakta hai.
       destroyAttachment: LeadAttachment::visibleTo() scope already
       non-super admins ko sirf apna khud ka upload hi dikhata/deta hai,
       isliye yahi findOrFail ownership check bhi ban jaata hai —
       super_admin kisi ka bhi delete kar sakta hai. */

    public function storeAttachment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'attachment' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,zip,txt',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $admin = $this->currentAdmin();
        $lead = Lead::visibleTo($admin)->findOrFail($request->lead_id);

        $file = $request->file('attachment');
        $path = $file->store('lead-attachments', 'public');

        $attachment = LeadAttachment::create([
            'lead_id' => $lead->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $admin->id,
        ]);

        $attachment->load('uploader:id,name');

        return response()->json(['success' => true, 'message' => 'Attachment uploaded.', 'attachment' => $attachment]);
    }

    public function destroyAttachment($id)
    {
        $admin = $this->currentAdmin();

        $attachment = LeadAttachment::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->findOrFail($id);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted.']);
    }

    /* ==================== REMINDERS ====================
       A "reminder" entry can be one of two kinds, chosen on the front-end:
         - type=reminder : has an optional date/time + appointment type,
           shows up in the alarm/bell poll (checkReminders) and on the
           standalone Reminders page (remindersData).
         - type=note     : also has an optional date/time (just informational —
           it never alerts), but no appointment type. Only shown in the lead
           detail panel's "Notes" list.
       appointment_type is forced null server-side for a note regardless of
       what the client sends, since that concept only applies to reminders.
       A note never triggers the alarm/bell because checkReminders() below
       explicitly filters to type=reminder — not because it lacks a date.
       (It does still show up in the Reminders page's data, under its own
       "Notes" tab — see remindersData().) */

    public function storeReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'type' => 'nullable|in:reminder,note',
            'note' => 'required|string|max:1000',
            'reminder_date' => 'nullable|date',
            'reminder_time' => 'nullable|date_format:H:i',
            'appointment_type' => 'nullable|in:whatsapp,meeting',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lead = Lead::visibleTo($this->currentAdmin())->findOrFail($request->lead_id);

        $type = $request->input('type', 'reminder') === 'note' ? 'note' : 'reminder';

        $reminder = LeadReminder::create([
            'lead_id' => $lead->id,
            'type' => $type,
            'note' => $request->note,
            'reminder_date' => $request->reminder_date,
            'reminder_time' => $request->reminder_time,
            'appointment_type' => $type === 'note' ? null : $request->appointment_type,
            'created_by' => $this->currentAdmin()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $type === 'note' ? 'Note added.' : 'Reminder added.',
            'reminder' => $reminder,
        ]);
    }

    /** Snooze / reschedule a follow-up (and optionally edit its text or type). Reopens it as "scheduled". */
    public function updateReminder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'sometimes|required|string|max:1000',
            'reminder_date' => 'required|date',
            'reminder_time' => 'nullable|date_format:H:i',
            'appointment_type' => 'sometimes|nullable|in:whatsapp,meeting',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors(), 'message' => $validator->errors()->first()], 422);
        }

        $admin = $this->currentAdmin();
        $reminder = LeadReminder::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->findOrFail($id);

        if (($reminder->type ?? 'reminder') === 'note') {
            return response()->json(['success' => false, 'message' => 'A note has no date to change.'], 422);
        }

        foreach ($validator->validated() as $key => $value) {
            $reminder->{$key} = $value;
        }
        $reminder->status = 'scheduled';
        $reminder->save();

        return response()->json(['success' => true, 'message' => 'Follow-up rescheduled.', 'reminder' => $reminder]);
    }

    public function updateReminderStatus(Request $request, $id)
    {
        $status = $request->input('status', 'completed');
        if (! in_array($status, ['scheduled', 'missed', 'silent', 'completed'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        $admin = $this->currentAdmin();
        $reminder = LeadReminder::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->findOrFail($id);

        $reminder->status = $status;
        $reminder->save();

        return response()->json(['success' => true, 'reminder' => $reminder]);
    }

    public function destroyReminder($id)
    {
        $admin = $this->currentAdmin();

        LeadReminder::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Polled by the front-end (and hit again by a precisely-timed client
     * timer for each "upcoming" reminder — see global-reminders.js), so
     * this needs to stay cheap and reliable rather than assume the poll
     * interval alone drives punctuality.
     *
     * Only type=reminder entries are considered — type=note entries never
     * carry a date, so they'd already be excluded by whereNotNull below,
     * but the explicit type filter keeps the intent obvious.
     *
     * Returns:
     *   - due: reminders whose date/time has already arrived and are still
     *     'scheduled' — reported every call, not just the first, so the
     *     alarm/bell can stay accurate until the user handles them.
     *   - upcoming: still-'scheduled' reminders whose time hasn't arrived
     *     yet, each with an exact `due_at` ISO timestamp so the client can
     *     set a precise timer instead of waiting for the next poll tick.
     *   - server_time: lets the client correct for its own clock drift
     *     when scheduling those timers.
     *
     * Scoped per-admin: each admin only gets alerted for reminders THEY
     * created (super_admin gets everything).
     */
    public function checkReminders()
    {
        $admin = $this->currentAdmin();
        $now = Carbon::now();

        $all = LeadReminder::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->where('type', 'reminder')
            ->where('status', 'scheduled')
            ->whereNotNull('reminder_date')
            ->with('lead:id,customer_name')
            ->get()
            ->map(function ($reminder) {
                $reminder->due_at = Carbon::parse(
                    $reminder->reminder_date->format('Y-m-d') . ' ' . ($reminder->reminder_time ?? '00:00:00')
                )->toIso8601String();

                return $reminder;
            });

        $due = $all->filter(fn ($r) => Carbon::parse($r->due_at)->lte($now))->values();
        $upcoming = $all->filter(fn ($r) => Carbon::parse($r->due_at)->gt($now))->values();

        return response()->json([
            'success' => true,
            'due' => $due,
            'upcoming' => $upcoming,
            'server_time' => $now->toIso8601String(),
        ]);
    }

    /* ==================== REMINDERS PAGE ====================
       A dedicated page that lists every reminder AND note across every
       lead the admin can see (not just one lead's, like the offcanvas
       does). reminders.blade.php splits them into two tabs — "Reminders"
       and "Notes" — over this same data set; filtering/search/status/lead
       is handled client-side there, same pattern as the leads table. */

    public function remindersPage()
    {
        return view('pages.reminders', [
            'leads' => Lead::visibleTo($this->currentAdmin())
                ->orderBy('customer_name')
                ->get(['id', 'customer_name', 'product_name', 'phone', 'lead_type']),
        ]);
    }

    public function remindersData()
    {
        $admin = $this->currentAdmin();

        $reminders = LeadReminder::whereHas('lead', fn ($q) => $q->visibleTo($admin))
            ->visibleTo($admin)
            ->with(['lead:id,customer_name,product_name,phone,lead_type'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['success' => true, 'reminders' => $reminders]);
    }
}