<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class DealActivity extends Model
{
    public const TYPES = ['note', 'call', 'email', 'meeting', 'task'];

    protected $fillable = ['deal_id', 'type', 'body', 'meta', 'activity_at', 'completed_at', 'created_by'];

    protected $casts = [
        'meta'         => 'array',
        'activity_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function deal(): BelongsTo    { return $this->belongsTo(Deal::class); }
    public function creator(): BelongsTo { return $this->belongsTo(Admin::class, 'created_by'); }

    /** Write an automatic "system" row on the deal timeline. $meta['kind'] picks the icon on the page. */
    public static function log(Deal $deal, string $body, ?int $adminId = null, array $meta = []): self
    {
        return static::create([
            'deal_id'     => $deal->id,
            'type'        => 'system',
            'body'        => $body,
            'meta'        => $meta ?: null,
            'activity_at' => now(),
            'created_by'  => $adminId ?? Auth::guard('admin')->id(),
        ]);
    }
}