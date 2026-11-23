<?php
namespace App\Console\Commands;

use App\Models\SuperAdmin;
use Illuminate\Console\Command;
use PragmaRX\Google2FA\Google2FA;

/** There is deliberately NO web registration for owners. Create them here, on the server. */
class CreateSuperAdmin extends Command
{
    protected $signature = 'console:create-admin {email} {name}';
    protected $description = 'Create a super admin and enrol an authenticator app';

    public function handle(Google2FA $g): int
    {
        $pw = $this->secret('Password (min 14 characters)');
        if (strlen((string) $pw) < 14) { $this->error('Password too short.'); return self::FAILURE; }

        $secret = $g->generateSecretKey(32);
        $this->info("Add this key to your authenticator app (manual entry):\n  {$secret}\nOr open this URL as a QR code:\n  " . $g->getQRCodeUrl(config('app.name'), $this->argument('email'), $secret));
        if (!$g->verifyKey($secret, (string) $this->ask('Enter the 6-digit code to confirm'))) {
            $this->error('Code did not match. Nothing was saved.'); return self::FAILURE;
        }
        SuperAdmin::create(['name' => $this->argument('name'), 'email' => $this->argument('email'), 'password' => $pw,
            'two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()]);
        $this->info('Super admin created.');
        return self::SUCCESS;
    }
}
