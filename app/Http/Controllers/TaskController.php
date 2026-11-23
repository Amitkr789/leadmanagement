<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Task;
use App\Models\TaskActivityLog;
use App\Models\TaskChecklistItem;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentedNotification;
use App\Notifications\TaskStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    private const STATUSES   = ['pending', 'in_progress', 'completed', 'reverted', 'on_hold'];
    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    /* =========================================================
     |  Page
     * ========================================================= */

    public function index()
    {
        $admin = auth('admin')->user();

        return view('pages.tasks.index', [
            'admins'       => Admin::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'priorities'   => self::PRIORITIES,
            'statuses'     => self::STATUSES,
            'isSuperAdmin' => $admin->isSuperAdmin(),
        ]);
    }

    /* =========================================================
     |  List + stats (JSON, scoped by role)
     * ========================================================= */

    public function data(Request $request)
    {
        $admin = auth('admin')->user();

        $tasks = $this->taskQuery($admin)->latest()->latest('id')->get();

        return response()->json([
            'success' => true,
            'tasks'   => $tasks,
            'current_admin' => [
                'id'             => $admin->id,
                'name'           => $admin->name,
                'is_super_admin' => $admin->isSuperAdmin(),
            ],
            'stats' => [
                'total'       => $tasks->count(),
                'pending'     => $tasks->where('status', 'pending')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'on_hold'     => $tasks->where('status', 'on_hold')->count(),
                'completed'   => $tasks->where('status', 'completed')->count(),
                'reverted'    => $tasks->where('status', 'reverted')->count(),
                'overdue'     => $tasks->filter->isOverdue()->count(),
            ],
        ]);
    }

    /* =========================================================
     |  Create — super admin only
     * ========================================================= */

    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can create and assign tasks.'], 403);
        }

        $data = $request->validate([
            'task_name'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:' . implode(',', self::PRIORITIES),
            'due_date'    => 'nullable|date',
            'assigned_to' => 'required|exists:admins,id',
        ]);

        $task = Task::create($data + [
            'assigned_by' => $admin->id,
            'status'      => 'pending',
        ]);

        $this->log($task, $admin, 'created', "{$admin->name} created this task.");
        $this->log($task, $admin, 'assigned', "{$admin->name} assigned this task to {$task->assignee->name}.");

        if ($task->assignee && $task->assignee->id !== $admin->id) {
            $task->assignee->notify(new TaskAssignedNotification($task));
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created and assigned.',
            'task'    => $this->hydrate($task),
        ]);
    }

    /* =========================================================
     |  Update details / reassign — super admin only
     * ========================================================= */

    public function update(Request $request, Task $task)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can edit or reassign a task.'], 403);
        }

        $data = $request->validate([
            'task_name'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:' . implode(',', self::PRIORITIES),
            'due_date'    => 'nullable|date',
            'assigned_to' => 'required|exists:admins,id',
        ]);

        // (int) on both sides: the request sends "3", the DB holds 3 — a strict
        // comparison of the raw values would log a reassignment on every edit.
        $previousAssignee = (int) $task->assigned_to;
        $task->update($data);

        $this->log($task, $admin, 'updated', "{$admin->name} updated this task's details.");

        if ($previousAssignee !== (int) $task->assigned_to) {
            $this->log($task, $admin, 'assigned', "{$admin->name} reassigned this task to {$task->assignee->name}.");
            if ($task->assignee && $task->assignee->id !== $admin->id) {
                $task->assignee->notify(new TaskAssignedNotification($task));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated.',
            'task'    => $this->hydrate($task->fresh()),
        ]);
    }

    /* =========================================================
     |  Duplicate — super admin only
     * ========================================================= */

    public function duplicate(Task $task)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can duplicate tasks.'], 403);
        }

        $copy = $task->replicate(['status', 'completed_at']);
        $copy->task_name    = Str::limit($task->task_name, 240, '') . ' (Copy)';
        $copy->status       = 'pending';
        $copy->completed_at = null;
        $copy->assigned_by  = $admin->id;
        $copy->save();

        foreach ($task->checklistItems as $item) {
            $copy->checklistItems()->create([
                'title'      => $item->title,
                'sort_order' => $item->sort_order,
            ]);
        }

        $this->log($copy, $admin, 'created', "{$admin->name} duplicated this task from \"{$task->task_name}\".");

        if ($copy->assignee && $copy->assignee->id !== $admin->id) {
            $copy->assignee->notify(new TaskAssignedNotification($copy));
        }

        return response()->json([
            'success' => true,
            'message' => 'Task duplicated.',
            'task'    => $this->hydrate($copy),
        ]);
    }

    /* =========================================================
     |  Show single task detail (comments/attachments/activity/checklist)
     * ========================================================= */

    public function show(Task $task)
    {
        $admin = auth('admin')->user();

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You do not have access to this task.'], 403);
        }

        $task->load([
            'assignee:id,name',
            'assigner:id,name',
            'comments.author:id,name',
            'attachments.uploader:id,name',
            'activityLogs.admin:id,name',
            'checklistItems',
        ]);

        return response()->json(['success' => true, 'task' => $task]);
    }

    /* =========================================================
     |  Status change — assignee (their own task) OR super admin
     * ========================================================= */

    public function updateStatus(Request $request, Task $task)
    {
        $admin = auth('admin')->user();

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You can only update tasks assigned to you.'], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        if ($task->status !== $data['status']) {
            $this->applyStatus($task, $admin, $data['status']);
        }

        $label = $this->statusLabel($task->status);

        return response()->json([
            'success' => true,
            'message' => "Status updated to {$label}.",
            'task'    => $this->hydrate($task->fresh()),
        ]);
    }

    /* =========================================================
     |  Bulk status — every task the admin is allowed to update
     * ========================================================= */

    public function bulkStatus(Request $request)
    {
        $admin = auth('admin')->user();

        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $tasks   = Task::visibleTo($admin)->whereIn('id', $data['ids'])->get();
        $changed = 0;

        foreach ($tasks as $task) {
            if ($task->status === $data['status']) {
                continue;
            }
            $this->applyStatus($task, $admin, $data['status']);
            $changed++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$changed} task(s) moved to " . $this->statusLabel($data['status']) . '.',
            'tasks'   => $this->taskQuery($admin)->whereIn('id', $data['ids'])->get(),
        ]);
    }

    /* =========================================================
     |  Bulk reassign — super admin only
     * ========================================================= */

    public function bulkAssign(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can reassign tasks.'], 403);
        }

        $data = $request->validate([
            'ids'         => 'required|array|min:1',
            'ids.*'       => 'integer',
            'assigned_to' => 'required|exists:admins,id',
        ]);

        $assignee = Admin::findOrFail($data['assigned_to']);
        $changed  = 0;

        foreach (Task::whereIn('id', $data['ids'])->get() as $task) {
            if ((int) $task->assigned_to === (int) $assignee->id) {
                continue;
            }

            $task->update(['assigned_to' => $assignee->id]);
            $this->log($task, $admin, 'assigned', "{$admin->name} reassigned this task to {$assignee->name}.");

            if ($assignee->id !== $admin->id) {
                $assignee->notify(new TaskAssignedNotification($task));
            }
            $changed++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$changed} task(s) assigned to {$assignee->name}.",
            'tasks'   => $this->taskQuery($admin)->whereIn('id', $data['ids'])->get(),
        ]);
    }

    /* =========================================================
     |  Delete — super admin only
     * ========================================================= */

    public function destroy(Task $task)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can delete tasks.'], 403);
        }

        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $task->delete();

        return response()->json(['success' => true, 'message' => 'Task deleted.']);
    }

    public function bulkDestroy(Request $request)
    {
        $admin = auth('admin')->user();

        if (!$admin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a super admin can delete tasks.'], 403);
        }

        $ids = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ])['ids'];

        $tasks = Task::whereIn('id', $ids)->get();

        foreach ($tasks as $task) {
            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $task->delete();
        }

        return response()->json(['success' => true, 'message' => $tasks->count() . ' task(s) deleted.']);
    }

    /* =========================================================
     |  Comments — assignee or assigner (super admin) of that task
     * ========================================================= */

    public function storeComment(Request $request)
    {
        $admin = auth('admin')->user();

        $data = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'comment' => 'required|string|max:5000',
        ]);

        $task = Task::findOrFail($data['task_id']);

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot comment on this task.'], 403);
        }

        $comment = $task->comments()->create([
            'admin_id' => $admin->id,
            'comment'  => $data['comment'],
        ]);

        $this->log($task, $admin, 'commented', "{$admin->name} added a comment.");

        $recipient = (int) $admin->id === (int) $task->assigned_to ? $task->assigner : $task->assignee;
        if ($recipient && $recipient->id !== $admin->id) {
            $recipient->notify(new TaskCommentedNotification($task, $admin, $data['comment']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added.',
            'comment' => $comment->load('author:id,name'),
        ]);
    }

    /* =========================================================
     |  Checklist (sub-tasks)
     * ========================================================= */

    public function storeChecklistItem(Request $request)
    {
        $admin = auth('admin')->user();

        $data = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'title'   => 'required|string|max:255',
        ]);

        $task = Task::findOrFail($data['task_id']);

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot edit this task.'], 403);
        }

        // Query the table directly: going through the relation would add its
        // ORDER BY to the aggregate, which MySQL (ONLY_FULL_GROUP_BY) rejects.
        $next = (int) TaskChecklistItem::where('task_id', $task->id)->max('sort_order') + 1;

        $item = $task->checklistItems()->create([
            'title'      => trim($data['title']),
            'sort_order' => $next,
        ]);

        $this->log($task, $admin, 'checklist_added', "{$admin->name} added checklist item \"{$item->title}\".");

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function toggleChecklistItem(Request $request, TaskChecklistItem $item)
    {
        $admin = auth('admin')->user();
        $task  = $item->task;

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot edit this task.'], 403);
        }

        $done = (bool) $request->validate(['is_done' => 'required|boolean'])['is_done'];

        $item->update([
            'is_done'      => $done,
            'completed_at' => $done ? now() : null,
        ]);

        if ($done) {
            $this->log($task, $admin, 'checklist_completed', "{$admin->name} completed \"{$item->title}\".");
        }

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function destroyChecklistItem(TaskChecklistItem $item)
    {
        $admin = auth('admin')->user();
        $task  = $item->task;

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot edit this task.'], 403);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /* =========================================================
     |  Attachments
     * ========================================================= */

    public function storeAttachment(Request $request)
    {
        $admin = auth('admin')->user();

        $data = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'file'    => 'required|file|max:10240', // 10MB
        ]);

        $task = Task::findOrFail($data['task_id']);

        if (!$task->canBeUpdatedBy($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot upload files to this task.'], 403);
        }

        $file = $request->file('file');
        $path = $file->store("task-attachments/{$task->id}", 'public');

        $attachment = $task->attachments()->create([
            'admin_id'      => $admin->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_size'     => $file->getSize(),
            'mime_type'     => $file->getClientMimeType(),
        ]);

        $this->log($task, $admin, 'attachment_added', "{$admin->name} attached \"{$attachment->original_name}\".");

        return response()->json([
            'success'    => true,
            'message'    => 'File attached.',
            'attachment' => $attachment->load('uploader:id,name'),
        ]);
    }

    public function destroyAttachment(\App\Models\TaskAttachment $attachment)
    {
        $admin = auth('admin')->user();
        $task = $attachment->task;

        if (!$admin->isSuperAdmin() && (int) $attachment->admin_id !== (int) $admin->id) {
            return response()->json(['success' => false, 'message' => 'You can only remove files you uploaded.'], 403);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $this->log($task, $admin, 'attachment_removed', "{$admin->name} removed \"{$attachment->original_name}\".");
        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment removed.']);
    }

    /* =========================================================
     |  Bell notifications
     * ========================================================= */

    public function notificationsCheck(Request $request)
    {
        $admin = auth('admin')->user();

        $unread = $admin->unreadNotifications()->take(15)->get();

        return response()->json([
            'success' => true,
            'count'   => $admin->unreadNotifications()->count(),
            'notifications' => $unread->map(fn($n) => [
                'id'         => $n->id,
                'task_id'    => $n->data['task_id'] ?? null,
                'message'    => $n->data['message'] ?? '',
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function notificationsMarkRead(Request $request)
    {
        $admin = auth('admin')->user();
        $id = $request->input('id');

        if ($id) {
            $admin->unreadNotifications()->where('id', $id)->first()?->markAsRead();
        } else {
            $admin->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /* =========================================================
     |  Helpers
     * ========================================================= */

    /** Base query used by every endpoint that returns task rows. */
    private function taskQuery(Admin $admin)
    {
        return Task::query()
            ->visibleTo($admin)
            ->with(['assignee:id,name', 'assigner:id,name'])
            ->withCount($this->countRelations());
    }

    /** Re-load relations + counts on a single task so the UI row is complete. */
    private function hydrate(Task $task): Task
    {
        return $task
            ->load(['assignee:id,name', 'assigner:id,name'])
            ->loadCount($this->countRelations());
    }

    private function countRelations(): array
    {
        return [
            'comments',
            'attachments',
            'checklistItems',
            'checklistItems as checklist_done_count' => fn($q) => $q->where('is_done', true),
        ];
    }

    private function applyStatus(Task $task, Admin $admin, string $status): void
    {
        $task->status       = $status;
        $task->completed_at = $status === 'completed' ? now() : null;
        $task->save();

        $this->log($task, $admin, 'status_changed', "{$admin->name} changed the status to " . $this->statusLabel($status) . '.');

        // Notify whoever isn't the one making the change.
        $recipient = (int) $admin->id === (int) $task->assigned_to ? $task->assigner : $task->assignee;
        if ($recipient && $recipient->id !== $admin->id) {
            $recipient->notify(new TaskStatusUpdatedNotification($task, $admin));
        }
    }

    private function statusLabel(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    private function log(Task $task, Admin $admin, string $action, string $description): void
    {
        TaskActivityLog::create([
            'task_id'     => $task->id,
            'admin_id'    => $admin->id,
            'action'      => $action,
            'description' => $description,
        ]);
    }
}