<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyActivity extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function admin() { return $this->belongsTo(SuperAdmin::class, 'super_admin_id'); }

    public static function log(Company $c, string $type, string $message): void
    {
        static::create(['company_id' => $c->id, 'super_admin_id' => Auth::guard('superadmin')->id(), 'type' => $type, 'message' => $message, 'created_at' => now()]);
    }
}
