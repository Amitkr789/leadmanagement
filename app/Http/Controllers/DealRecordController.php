<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\DealAttachment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * The full "deal record" page (HubSpot-style): about + pipeline, activity timeline, attachments,
 * associated contacts and company. Field edits / stage / delete reuse DealController's endpoints.
 */
class DealRecordController extends Controller
{
    private function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    private function deal($id): Deal
    {
        return Deal::visibleTo($this->admin())->findOrFail($id);
    }

    private function payload(Deal $deal): array
    {
        $deal->load(Deal::RELATIONS);

        return [
            'deal'        => $deal,
            'activities'  => $deal->activities()->with('creator:id,name')->limit(500)->get(),
            'attachments' => $deal->attachments()->with('uploader:id,name')->get(),
        ];
    }

    /* ==================== PAGE ==================== */

    public function show($id)
    {
        $admin   = $this->admin();
        $deal    = $this->deal($id);
        $payload = $this->payload($deal);

        return view('pages.deal-show', [
            'dealId'     => $deal->id,
            'dealName'   => $deal->name,
            'payload'    => $payload,
            'stages'     => Deal::STAGES,
            'openStages' => Deal::OPEN_STAGES,
            'sources'    => Deal::SOURCES,
            'admins'     => Admin::orderBy('name')->get(['id', 'name']),
            'me'         => ['id' => $admin->id, 'name' => $admin->name, 'role' => $admin->role],
        ]);
    }

    /** Fresh deal + timeline + files (the page calls this after every change). */
    public function record($id): JsonResponse
    {
        return response()->json(['success' => true] + $this->payload($this->deal($id)));
    }

    /* ==================== ACTIVITIES ==================== */

    public function storeActivity(Request $request, $id): JsonResponse
    {
        $admin = $this->admin();
        $deal  = $this->deal($id);

        $v = Validator::make($request->all(), [
            'type'          => 'required|in:' . implode(',', DealActivity::TYPES),
            'body'          => 'required|string|max:5000',
            'subject'       => 'nullable|string|max:255',
            'outcome'       => 'nullable|string|max:50',
            'activity_date' => 'nullable|date',
            'activity_time' => 'nullable|date_format:H:i',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $type = $request->type;

        if ($request->filled('activity_date')) {
            $at = Carbon::parse($request->activity_date . ' ' . ($request->activity_time ?: now()->format('H:i')));
        } else {
            $at = in_array($type, ['task', 'meeting'], true) ? null : now();   // undated task = no due date
        }

        $meta = array_filter([
            'subject' => $type === 'email' ? $request->subject : null,
            'outcome' => $type === 'call' ? $request->outcome : null,
        ]);

        $activity = DealActivity::create([
            'deal_id'     => $deal->id,
            'type'        => $type,
            'body'        => trim($request->body),
            'meta'        => $meta ?: null,
            'activity_at' => $at,
            'created_by'  => $admin->id,
        ]);

        return response()->json(['success' => true, 'message' => ucfirst($type) . ' added.', 'activity' => $activity->load('creator:id,name')]);
    }

    /** Tick / untick a task or meeting. */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $activity = DealActivity::findOrFail($id);
        $this->deal($activity->deal_id);   // 404 unless the admin can see the deal

        if (! in_array($activity->type, ['task', 'meeting'], true)) {
            return response()->json(['success' => false, 'message' => 'Only tasks and meetings can be completed.'], 422);
        }

        $activity->completed_at = $request->boolean('completed') ? now() : null;
        $activity->save();

        return response()->json(['success' => true, 'activity' => $activity->load('creator:id,name')]);
    }

    public function destroyActivity($id): JsonResponse
    {
        $admin    = $this->admin();
        $activity = DealActivity::findOrFail($id);
        $this->deal($activity->deal_id);

        if ($activity->type === 'system' || ($activity->created_by !== $admin->id && $admin->role !== 'super_admin')) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own notes and tasks.'], 403);
        }

        $activity->delete();

        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }

    /* ==================== ATTACHMENTS ==================== */

    public function storeAttachment(Request $request, $id): JsonResponse
    {
        $admin = $this->admin();
        $deal  = $this->deal($id);

        $v = Validator::make($request->all(), [
            'attachment' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,zip,txt',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
        }

        $file = $request->file('attachment');
        $path = $file->store('deal-attachments', 'public');

        $attachment = DealAttachment::create([
            'deal_id'     => $deal->id,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'mime_type'   => $file->getClientMimeType(),
            'file_size'   => $file->getSize(),
            'uploaded_by' => $admin->id,
        ]);

        DealActivity::log($deal, 'File attached: ' . $attachment->file_name, $admin->id, ['kind' => 'file']);

        return response()->json(['success' => true, 'message' => 'File uploaded.', 'attachment' => $attachment->load('uploader:id,name')]);
    }

    public function destroyAttachment($id): JsonResponse
    {
        $admin      = $this->admin();
        $attachment = DealAttachment::findOrFail($id);
        $deal       = $this->deal($attachment->deal_id);

        if ($attachment->uploaded_by !== $admin->id && $admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'You can only delete files you uploaded.'], 403);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        DealActivity::log($deal, 'File removed: ' . $attachment->file_name, $admin->id, ['kind' => 'file']);

        return response()->json(['success' => true, 'message' => 'File deleted.']);
    }
}