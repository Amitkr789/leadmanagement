<?php
namespace App\Console\Commands;

use App\Models\LeadIntegration;
use App\Services\LeadIngest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/** Fetches leads for "API keys" integrations. Meta is live; JustDial and IndiaMART are added once their API access is confirmed. */
class PullLeads extends Command
{
    protected $signature = 'leads:pull';
    protected $description = 'Fetch new leads for integrations that use API keys';

    public function handle(): int
    {
        $n = 0;
        LeadIntegration::with('company')->where('method', 'api')->where('is_active', true)->where('provider', 'meta')->each(function (LeadIntegration $i) use (&$n) {
            if (!$i->company || $i->company->isSuspended()) return;
            $r = Http::timeout(15)->get('https://graph.facebook.com/v19.0/' . $i->cfg('form_id') . '/leads', ['fields' => 'id,created_time,field_data', 'limit' => 50, 'access_token' => $i->secret('access_token')]);
            if (!$r->successful()) { $i->update(['last_error' => 'Meta: ' . substr((string) $r->json('error.message', 'fetch failed'), 0, 230)]); return; }
            foreach ($r->json('data', []) as $lead) if (LeadIngest::store($i, LeadIngest::fromMeta($lead), $lead)) $n++;
            $i->update(['last_tested_at' => now(), 'last_error' => null]);
        });
        $this->info("{$n} new leads saved.");
        return self::SUCCESS;
    }
}
