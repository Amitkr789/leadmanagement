<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const STATUSES = [
        'open'        => 'Open',
        'in_progress' => 'In progress',
        'waiting'     => 'Waiting on customer',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
    ];
    public const OPEN_STATUSES = ['open', 'in_progress', 'waiting'];

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];

    /** hours to respond / resolve, by priority — used to pre-fill due_at */
    public const SLA_HOURS = ['urgent' => 4, 'high' => 24, 'medium' => 48, 'low' => 72];

    public const CATEGORIES = ['General', 'Billing & payment', 'Delivery', 'Product / quality', 'Technical', 'Callback request', 'Other'];

    public const CHANNELS = ['phone' => 'Phone call', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'walk_in' => 'Walk-in', 'web' => 'Website'];

    /** Relations (+ columns) sent to the browser with every ticket. */
    public const RELATIONS = [
        'assignee:id,name',
        'creator:id,name',
        'deal:id,name,stage,amount',
        'lead:id,customer_name,phone,lead_type',
        'contact:id,name,email,phone,whatsapp',
        'company:id,name',
    ];

    protected $fillable = [
        'ticket_no', 'subject', 'description', 'status', 'priority', 'category', 'channel',
        'due_at', 'first_response_at', 'resolved_at', 'closed_at',
        'deal_id', 'lead_id', 'contact_id', 'company_id', 'assigned_to', 'created_by',
    ];

    protected $casts = [
        'due_at'            => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
    ];

    protected static function booted(): void
    {
        // TKT-00012 — built from the primary key, so it is unique without a counter table.
        static::created(function (SupportTicket $t) {
            $t->forceFill(['ticket_no' => 'TKT-' . str_pad((string) $t->id, 5, '0', STR_PAD_LEFT)])->saveQuietly();
        });
    }

    /** Everything the front-end needs to render pickers (statuses, priorities, SLA ...). */
    public static function config(): array
    {
        return [
            'statuses'     => self::STATUSES,
            'openStatuses' => self::OPEN_STATUSES,
            'priorities'   => self::PRIORITIES,
            'categories'   => self::CATEGORIES,
            'channels'     => self::CHANNELS,
            'sla'          => self::SLA_HOURS,
        ];
    }

    public function assignee(): BelongsTo { return $this->belongsTo(Admin::class, 'assigned_to'); }
    public function creator(): BelongsTo  { return $this->belongsTo(Admin::class, 'created_by'); }
    public function deal(): BelongsTo     { return $this->belongsTo(Deal::class); }
    public function lead(): BelongsTo     { return $this->belongsTo(Lead::class); }
    public function contact(): BelongsTo  { return $this->belongsTo(Contact::class); }
    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id')->oldest('id');
    }

    /** super_admin: everything. Everyone else: tickets they raised or that are assigned to them. */
    public function scopeVisibleTo(Builder $query, Admin $admin): Builder
    {
        if ($admin->role === 'super_admin') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q->where('created_by', $admin->id)->orWhere('assigned_to', $admin->id));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }
}