<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    private function fail($v): JsonResponse
    {
        return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
    }

    /** ticket query with everything the UI needs (+ number of real messages, not system rows) */
    private function query()
    {
        return SupportTicket::with(SupportTicket::RELATIONS)
            ->withCount(['messages as messages_count' => fn ($q) => $q->where('type', '!=', 'system')]);
    }

    private function like(string $q): string
    {
        return '%' . addcslashes($q, '%_\\') . '%';
    }

    /* ==================== PAGE + LISTING ==================== */

    public function page()
    {
        return view('pages.tickets', [
            'admins'       => Admin::orderBy('name')->get(['id', 'name']),
            'isSuperAdmin' => $this->admin()->role === 'super_admin',
        ]);
    }

    public function data(): JsonResponse
    {
        $admin = $this->admin();

        return response()->json([
            'success'       => true,
            'tickets'       => $this->query()->visibleTo($admin)->orderByDesc('id')->get(),
            'current_admin' => ['id' => $admin->id, 'name' => $admin->name, 'role' => $admin->role],
        ]);
    }

    /** One ticket + its conversation (used by the detail drawer). */
    public function show($id): JsonResponse
    {
        $ticket = $this->query()->visibleTo($this->admin())->findOrFail($id);

        return response()->json([
            'success'  => true,
            'ticket'   => $ticket,
            'messages' => $ticket->messages()->with('creator:id,name')->get(),
        ]);
    }

    /** Tickets of one deal / lead / contact / company — handy for a "Tickets" panel on those pages. */
    public function forRecord($type, $id): JsonResponse
    {
        $column = ['deal' => 'deal_id', 'lead' => 'lead_id', 'contact' => 'contact_id', 'company' => 'company_id'][$type] ?? null;
        abort_unless($column, 404);

        return response()->json([
            'success' => true,
            'tickets' => $this->query()->visibleTo($this->admin())->where($column, $id)->orderByDesc('id')->get(),
        ]);
    }

    /* ==================== RECORD LOOKUP (create-ticket modal) ==================== */

    /**
     * GET ?type=deal|lead|contact&q=...   or   ?type=deal&id=12
     * Returns normalised rows: {type, id, title, sub, company, contacts:[{id,name}]}
     */
    public function lookup(Request $request): JsonResponse
    {
        $admin = $this->admin();
        $type  = $request->query('type');
        $id    = $request->query('id');
        $q     = trim((string) $request->query('q', ''));
        $like  = $this->like($q);

        if ($type === 'deal') {
            $query = Deal::visibleTo($admin)->with(['company:id,name', 'contacts:id,name'])->latest('id');
            if ($id) {
                $query->whereKey($id);
            } elseif ($q !== '') {
                $query->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('contacts', fn ($c) => $c->where('name', 'like', $like)));
            }
            $items = $query->limit(8)->get()->map(fn ($d) => [
                'type'     => 'deal',
                'id'       => $d->id,
                'title'    => $d->name,
                'sub'      => Deal::STAGES[$d->stage]['label'] . ' · ₹' . number_format((float) $d->amount) . ($d->company ? ' · ' . $d->company->name : ''),
                'company'  => $d->company?->name,
                'contacts' => $d->contacts->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
            ]);
        } elseif ($type === 'lead') {
            $query = Lead::visibleTo($admin)->with(['contact:id,name', 'company:id,name'])->latest('id');
            if ($id) {
                $query->whereKey($id);
            } elseif ($q !== '') {
                $query->where(fn ($w) => $w->where('customer_name', 'like', $like)->orWhere('phone', 'like', $like)
                    ->orWhere('company_name', 'like', $like)->orWhere('product_name', 'like', $like));
            }
            $items = $query->limit(8)->get()->map(fn ($l) => [
                'type'     => 'lead',
                'id'       => $l->id,
                'title'    => $l->customer_name,
                'sub'      => implode(' · ', array_filter([ucfirst((string) $l->lead_type), $l->phone, $l->company_name])),
                'company'  => $l->company?->name ?? $l->company_name,
                'contacts' => $l->contact ? [['id' => $l->contact->id, 'name' => $l->contact->name]] : [],
            ]);
        } elseif ($type === 'contact') {
            $query = Contact::visibleTo($admin)->with('company:id,name')->latest('id');
            if ($id) {
                $query->whereKey($id);
            } elseif ($q !== '') {
                $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like));
            }
            $items = $query->limit(8)->get()->map(fn ($c) => [
                'type'     => 'contact',
                'id'       => $c->id,
                'title'    => $c->name,
                'sub'      => implode(' · ', array_filter([$c->email, $c->phone])),
                'company'  => $c->company?->name,
                'contacts' => [['id' => $c->id, 'name' => $c->name]],
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Unknown record type.'], 422);
        }

        return response()->json(['success' => true, 'items' => $items->values()]);
    }

    /* ==================== CREATE ==================== */

    public function store(Request $request): JsonResponse
    {
        $admin = $this->admin();

        $v = Validator::make($request->all(), [
            'subject'      => 'required|string|max:255',
            'description'  => 'nullable|string|max:5000',
            'category'     => 'nullable|in:' . implode(',', SupportTicket::CATEGORIES),
            'priority'     => 'nullable|in:' . implode(',', array_keys(SupportTicket::PRIORITIES)),
            'channel'      => 'nullable|in:' . implode(',', array_keys(SupportTicket::CHANNELS)),
            'assigned_to'  => 'nullable|integer|exists:admins,id',
            'due_at'       => 'nullable|date',
            'related_type' => 'nullable|in:deal,lead,contact',
            'related_id'   => 'nullable|integer',
            'contact_id'   => 'nullable|integer',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }
        $d = $v->validated();

        $links    = $this->resolveLinks($admin, $d['related_type'] ?? null, $d['related_id'] ?? null, $d['contact_id'] ?? null);
        $priority = $d['priority'] ?? 'medium';

        $ticket = DB::transaction(function () use ($d, $admin, $links, $priority) {
            $ticket = SupportTicket::create($links + [
                'subject'     => trim($d['subject']),
                'description' => $d['description'] ?? null,
                'status'      => 'open',
                'priority'    => $priority,
                'category'    => $d['category'] ?? 'General',
                'channel'     => $d['channel'] ?? null,
                'assigned_to' => $d['assigned_to'] ?? $admin->id,
                'due_at'      => ! empty($d['due_at']) ? $d['due_at'] : now()->addHours(SupportTicket::SLA_HOURS[$priority]),
                'created_by'  => $admin->id,
            ]);

            SupportTicketMessage::system($ticket, 'Ticket created' . (! empty($d['channel']) ? ' via ' . SupportTicket::CHANNELS[$d['channel']] : ''), $admin->id);

            return $ticket;
        });

        return response()->json([
            'success' => true,
            'message' => 'Ticket ' . $ticket->fresh()->ticket_no . ' created.',
            'ticket'  => $this->query()->findOrFail($ticket->id),
        ]);
    }

    /** deal / lead / contact  ->  the four association columns (contact + company are derived). */
    private function resolveLinks(Admin $admin, ?string $type, $id, $contactId): array
    {
        $links = ['deal_id' => null, 'lead_id' => null, 'contact_id' => null, 'company_id' => null];

        if ($type && $id) {
            if ($type === 'deal') {
                $deal = Deal::visibleTo($admin)->findOrFail($id);
                $links = ['deal_id' => $deal->id, 'lead_id' => $deal->lead_id, 'company_id' => $deal->company_id, 'contact_id' => $deal->contacts()->value('contacts.id')];
            } elseif ($type === 'lead') {
                $lead = Lead::visibleTo($admin)->findOrFail($id);
                $links = ['deal_id' => null, 'lead_id' => $lead->id, 'company_id' => $lead->company_id, 'contact_id' => $lead->contact_id];
            } else {
                $contact = Contact::visibleTo($admin)->findOrFail($id);
                $links = ['deal_id' => null, 'lead_id' => null, 'company_id' => $contact->company_id, 'contact_id' => $contact->id];
            }
        }

        // a deal can have several contacts — let the user pick which one raised the ticket
        if ($contactId && Contact::visibleTo($admin)->whereKey($contactId)->exists()) {
            $links['contact_id'] = (int) $contactId;
            $links['company_id'] ??= Contact::whereKey($contactId)->value('company_id');
        }

        return $links;
    }

    /* ==================== UPDATE ==================== */

    public function patch(Request $request, $id): JsonResponse
    {
        $admin  = $this->admin();
        $ticket = SupportTicket::visibleTo($admin)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'subject'     => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:5000',
            'status'      => 'sometimes|required|in:' . implode(',', array_keys(SupportTicket::STATUSES)),
            'priority'    => 'sometimes|required|in:' . implode(',', array_keys(SupportTicket::PRIORITIES)),
            'category'    => 'sometimes|required|in:' . implode(',', SupportTicket::CATEGORIES),
            'channel'     => 'sometimes|nullable|in:' . implode(',', array_keys(SupportTicket::CHANNELS)),
            'assigned_to' => 'sometimes|nullable|integer|exists:admins,id',
            'due_at'      => 'sometimes|nullable|date',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }
        $d = $v->validated();

        $old = $ticket->only(['subject', 'status', 'priority', 'category', 'assigned_to', 'due_at']);

        if (isset($d['status']) && $d['status'] !== $ticket->status) {
            $closed = in_array($d['status'], ['resolved', 'closed'], true);
            $d['resolved_at'] = $closed ? ($ticket->resolved_at ?? now()) : null;   // reopening clears it
            $d['closed_at']   = $d['status'] === 'closed' ? now() : null;
        }

        $ticket->update($d);
        $ticket->refresh();

        $log = fn (string $text) => SupportTicketMessage::system($ticket, $text, $admin->id);

        if (isset($d['status']) && $old['status'] !== $ticket->status) {
            $log('Status changed from ' . SupportTicket::STATUSES[$old['status']] . ' to ' . SupportTicket::STATUSES[$ticket->status]);
        }
        if (isset($d['priority']) && $old['priority'] !== $ticket->priority) {
            $log('Priority changed from ' . SupportTicket::PRIORITIES[$old['priority']] . ' to ' . SupportTicket::PRIORITIES[$ticket->priority]);
        }
        if (isset($d['category']) && $old['category'] !== $ticket->category) {
            $log("Category changed to {$ticket->category}");
        }
        if (isset($d['subject']) && $old['subject'] !== $ticket->subject) {
            $log('Subject updated');
        }
        if (array_key_exists('assigned_to', $d) && (int) $old['assigned_to'] !== (int) $ticket->assigned_to) {
            $names = Admin::whereIn('id', array_filter([$old['assigned_to'], $ticket->assigned_to]))->pluck('name', 'id');
            $log('Assigned to ' . ($names[$ticket->assigned_to] ?? 'nobody') . ($old['assigned_to'] ? ' (was ' . ($names[$old['assigned_to']] ?? '—') . ')' : ''));
        }
        if (array_key_exists('due_at', $d) && optional($old['due_at'])->timestamp !== optional($ticket->due_at)->timestamp) {
            $log($ticket->due_at ? 'Due date set to ' . $ticket->due_at->format('d M Y, g:i A') : 'Due date removed');
        }

        return response()->json(['success' => true, 'message' => 'Saved.', 'ticket' => $this->query()->findOrFail($ticket->id)]);
    }

    /* ==================== CONVERSATION ==================== */

    public function storeMessage(Request $request, $id): JsonResponse
    {
        $admin  = $this->admin();
        $ticket = SupportTicket::visibleTo($admin)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'type' => 'required|in:reply,note',
            'body' => 'required|string|max:5000',
        ]);
        if ($v->fails()) {
            return $this->fail($v);
        }

        $message = SupportTicketMessage::create([
            'ticket_id'  => $ticket->id,
            'type'       => $request->type,
            'body'       => trim($request->body),
            'created_by' => $admin->id,
        ]);

        // the first customer-facing reply stops the "first response" clock
        if ($request->type === 'reply' && ! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => $request->type === 'reply' ? 'Reply added.' : 'Note added.',
            'item'    => $message->load('creator:id,name'),
            'ticket'  => $this->query()->findOrFail($ticket->id),
        ]);
    }

    /* ==================== DELETE ==================== */

    public function destroy($id): JsonResponse
    {
        SupportTicket::visibleTo($this->admin())->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Ticket deleted.']);
    }
}