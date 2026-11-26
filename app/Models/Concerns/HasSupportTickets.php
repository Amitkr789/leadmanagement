<?php

namespace App\Models\Concerns;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Add to Deal, Lead, Contact and Company:
 *
 *     use App\Models\Concerns\HasSupportTickets;
 *     class Deal extends Model { use HasSupportTickets; ... }
 *
 * Gives you  $deal->supportTickets  /  $contact->supportTickets()->open()->count()  etc.
 * The foreign key is derived from the class name (deal_id, lead_id, contact_id, company_id).
 */
trait HasSupportTickets
{
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, Str::snake(class_basename($this)) . '_id')->latest('id');
    }
}