<?php
namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Outer wall: optional IP allow-list + verified Cloudflare Access JWT. Failures return 404 to hide the console. */
class ConsoleGate
{
    public function handle(Request $r, Closure $next)
    {
        $ips = config('console.allowed_ips');
        if ($ips && !in_array($r->ip(), $ips, true)) abort(404);

        $cf = config('console.cf_access');
        if ($cf['enabled']) {
            try {
                $keys = Cache::remember('cf_access_keys', 3600, fn () =>
                    Http::get("https://{$cf['team']}.cloudflareaccess.com/cdn-cgi/access/certs")->throw()->json());
                $claims = JWT::decode((string) $r->header('Cf-Access-Jwt-Assertion'), JWK::parseKeySet($keys));
                if (!in_array($cf['aud'], (array) $claims->aud, true)) abort(404);
            } catch (\Throwable $e) {
                abort(404);
            }
        }
        return $next($r);
    }
}
