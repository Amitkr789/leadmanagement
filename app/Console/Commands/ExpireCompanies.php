<?php
namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class ExpireCompanies extends Command
{
    protected $signature = 'companies:expire';
    protected $description = 'Mark companies whose trial or subscription has ended as expired';

    public function handle(): int
    {
        $n = Company::where('status', 'trial')->where('trial_ends_at', '<', now())->update(['status' => 'expired'])
           + Company::where('status', 'active')->where('subscription_ends_at', '<', now())->update(['status' => 'expired']);
        $this->info("{$n} companies marked expired.");
        return self::SUCCESS;
    }
}
