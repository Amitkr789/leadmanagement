<?php

namespace App\Services\Auth;

use App\Models\Admin;

final class GoogleLoginResult
{
    public function __construct(
        public readonly Admin $admin,
        /** true when this login just created a brand-new company and its owner */
        public readonly bool $created,
    ) {
    }
}