<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'email', 'phone', 'whatsapp', 'job_title', 'company_id', 'created_by'];

    protected static function booted(): void
    {
        static::saving(function (Contact $c) {
            $c->phone_key = self::phoneKey($c->phone);
        });
    }

    /** "+91 98765-43210", "09876543210" and "9876543210" all become "9876543210". */
    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        return strlen($digits) >= 10 ? substr($digits, -10) : $digits;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /** super_admin sees every contact of their company; others see the ones they created or that sit on a lead they can see. */
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