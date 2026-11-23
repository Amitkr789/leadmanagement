<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A company in the lead directory (a customer's customer). Not the SaaS tenant. */
class Company extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'website', 'phone', 'gst_number', 'address', 'created_by'];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, Admin $admin): Builder
    {
        if ($admin->role === 'super_admin') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($admin) {
            $q->where('created_by', $admin->id)
              ->orWhereHas('leads', fn (Builder $l) => $l->visibleTo($admin));
        });
    }
}