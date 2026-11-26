<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public const GST = 18; // percent
    public const MODES = ['Cash', 'UPI', 'Bank Transfer', 'Card', 'Cheque'];
    protected $guarded = [];
    protected function casts(): array { return ['period_start' => 'date', 'period_end' => 'date', 'paid_at' => 'datetime']; }
    public function company() { return $this->belongsTo(Company::class); }

    /** @return array{subtotal:int,gst:int,total:int} price + GST, rounded the same way issue() does */
    public static function quote(int $subtotal): array { $gst = (int) round($subtotal * self::GST / 100); return ['subtotal' => $subtotal, 'gst' => $gst, 'total' => $subtotal + $gst]; }

    /** Create a pending invoice. $amount overrides the plan price (used for prorated plan changes). */
    public static function issue(Company $c, Plan $plan, string $cycle, Carbon $start, Carbon $end, ?int $amount = null, ?string $desc = null): self
    {
        $sub = $amount ?? ($cycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price);
        $gst = (int) round($sub * self::GST / 100);
        $inv = static::create([
            'invoice_no' => 'TMP-' . Str::random(10), 'company_id' => $c->id, 'company_name' => $c->name, 'company_gst' => $c->gst_number,
            'company_address' => implode(', ', array_filter([$c->address, $c->city, $c->state])), 'plan_name' => $plan->name, 'billing_cycle' => $cycle,
            'description' => $desc ?? "{$plan->name} plan · " . ucfirst($cycle) . ' subscription',
            'period_start' => $start->toDateString(), 'period_end' => $end->toDateString(),
            'subtotal' => $sub, 'gst_percent' => self::GST, 'gst_amount' => $gst, 'total' => $sub + $gst, 'status' => 'pending',
            'created_by' => Auth::guard('superadmin')->id(),
        ]);
        $inv->update(['invoice_no' => sprintf('INV-%s-%05d', now()->format('Y'), $inv->id)]);
        return $inv;
    }
}
