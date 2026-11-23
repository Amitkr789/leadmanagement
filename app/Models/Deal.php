<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deal extends Model
{
    /** key => label + default win probability (%) */
    public const STAGES = [
        'new'         => ['label' => 'New',         'probability' => 10],
        'qualified'   => ['label' => 'Qualified',   'probability' => 30],
        'proposal'    => ['label' => 'Proposal',    'probability' => 50],
        'negotiation' => ['label' => 'Negotiation', 'probability' => 75],
        'won'         => ['label' => 'Won',         'probability' => 100],
        'lost'        => ['label' => 'Lost',        'probability' => 0],
    ];
    public const OPEN_STAGES = ['new', 'qualified', 'proposal', 'negotiation'];
    public const PRIORITIES  = ['low', 'medium', 'high'];
    public const TYPES       = ['new_business', 'existing_business'];

    protected $fillable = [
        'name', 'amount', 'stage', 'probability', 'priority', 'deal_type', 'source',
        'close_date', 'closed_at', 'lost_reason', 'description',
        'owner_id', 'company_id', 'lead_id', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'probability' => 'integer',
        'close_date'  => 'date:Y-m-d',
        'closed_at'   => 'datetime',
    ];

    public function owner(): BelongsTo   { return $this->belongsTo(Admin::class, 'owner_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(Admin::class, 'created_by'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function lead(): BelongsTo    { return $this->belongsTo(Lead::class); }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'deal_contact')->withTimestamps();
    }

    /** super_admin: everything. Everyone else: deals they created or own. */
    public function scopeVisibleTo(Builder $query, Admin $admin): Builder
    {
        if ($admin->role === 'super_admin') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q->where('created_by', $admin->id)->orWhere('owner_id', $admin->id));
    }
}