<?php

return [
    // true  = an unknown Google account creates a NEW company and becomes its super admin (owner).
    // false = invite-only: only users that a company super admin already added can sign in.
    'google_signup' => (bool) env('GOOGLE_SIGNUP_ENABLED', true),

    // Free trial given to every self-registered company.
    'trial_days' => (int) env('TENANT_TRIAL_DAYS', 14),

    // Max users during the trial (null = unlimited). Needs the tenants.max_users column.
    'trial_max_users' => env('TENANT_TRIAL_MAX_USERS', 3) === null ? null : (int) env('TENANT_TRIAL_MAX_USERS', 3),
];