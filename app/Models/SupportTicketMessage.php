<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SupportTicketMessage extends Model
{
    protected $fillable = ['ticket_id', 'type', 'body', 'created_by'];

    public function ticket(): BelongsTo  { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(Admin::class, 'created_by'); }

    /** Automatic timeline row ("Status changed from Open to Resolved"). */
    public static function system(SupportTicket $ticket, string $body, ?int $adminId = null): self
    {
        return static::create([
            'ticket_id'  => $ticket->id,
            'type'       => 'system',
            'body'       => $body,
            'created_by' => $adminId ?? Auth::guard('admin')->id(),
        ]);
    }
}