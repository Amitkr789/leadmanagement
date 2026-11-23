<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SuperAdmin extends Authenticatable
{
    protected $guarded = [];
    protected $hidden = ['password', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed', 'two_factor_secret' => 'encrypted', 'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime', 'locked_until' => 'datetime', 'last_login_at' => 'datetime',
        ];
    }
}
