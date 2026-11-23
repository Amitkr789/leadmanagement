<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name',
        'description',
        'priority',
        'due_date',
        'status',
        'assigned_to',
        'assigned_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            // "Y-m-d" keeps the JSON value a plain date (no timezone shift in the browser)
            'due_date'     => 'date:Y-m-d',
            'completed_at' => 'datetime',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function assignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    public function activityLogs()
    {
        return $this->hasMany(TaskActivityLog::class)->latest();
    }

    public function checklistItems()
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /* ---------------- Scopes ---------------- */

    /**
     * Super admins see every task. A regular admin only ever sees the
     * tasks that were assigned to them — they cannot see, edit, or
     * assign work to anyone else.
     */
    public function scopeVisibleTo(Builder $query, Admin $admin): Builder
    {
        if ($admin->isSuperAdmin()) {
            return $query;
        }

        return $query->where('assigned_to', $admin->id);
    }

    /* ---------------- Helpers ---------------- */

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }

        return $this->due_date->lt(now()->startOfDay());
    }

    public function canBeEditedBy(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function canBeUpdatedBy(Admin $admin): bool
    {
        return $admin->isSuperAdmin() || (int) $this->assigned_to === (int) $admin->id;
    }
}