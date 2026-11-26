<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $guarded = [];

    public static function get(string $key, ?string $default = null): ?string { return static::where('key', $key)->value('value') ?? $default; }
    public static function put(string $key, ?string $value): void { static::updateOrCreate(['key' => $key], ['value' => $value]); }
    public static function secret(string $key): ?string { $v = static::get($key); return $v ? Crypt::decryptString($v) : null; }
    public static function putSecret(string $key, string $value): void { static::put($key, Crypt::encryptString($value)); }
}
