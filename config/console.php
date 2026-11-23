<?php
return [
    'path'         => env('CONSOLE_PATH', 'console'),            // use a non-obvious path in production
    'idle_minutes' => (int) env('CONSOLE_IDLE_MINUTES', 20),
    'allowed_ips'  => array_filter(array_map('trim', explode(',', env('CONSOLE_ALLOWED_IPS', '')))),
    'cf_access'    => ['enabled' => env('CF_ACCESS_ENABLED', false), 'team' => env('CF_ACCESS_TEAM'), 'aud' => env('CF_ACCESS_AUD')],
    'turnstile'    => ['site_key' => env('TURNSTILE_SITE_KEY'), 'secret' => env('TURNSTILE_SECRET')],
];
