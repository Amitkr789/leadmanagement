<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Console\Command;

/**
 * One-time helper: turns the leads you already have into Contacts (and Companies, where a
 * company_name was filled in) so they show up in the new contact-first flow.
 *
 *   php artisan leads:backfill-contacts
 *
 * Safe to run more than once — leads that already have a contact_id are skipped, and leads with
 * the same phone number (last 10 digits) share one contact.
 */
class BackfillLeadContacts extends Command
{
    protected $signature = 'leads:backfill-contacts';
    protected $description = 'Create contacts (and companies) for existing leads that have none.';

    public function handle(): int
    {
        $made = ['contacts' => 0, 'companies' => 0, 'linked' => 0];

        Lead::whereNull('contact_id')->orderBy('id')->chunkById(200, function ($leads) use (&$made) {
            foreach ($leads as $lead) {
                $company = null;
                if (trim((string) $lead->company_name) !== '') {
                    $company = Company::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($lead->company_name))])->first();
                    if (!$company) {
                        $company = Company::create([
                            'name'       => trim($lead->company_name),
                            'gst_number' => $lead->gst_number ?: null,
                            'created_by' => $lead->created_by,
                        ]);
                        $made['companies']++;
                    }
                }

                $key     = Contact::phoneKey($lead->phone);
                $contact = $key ? Contact::where('phone_key', $key)->first() : null;

                if (!$contact) {
                    $contact = Contact::create([
                        'name'       => $lead->customer_name,
                        'email'      => $lead->email ?: null,
                        'phone'      => $lead->phone ?: null,
                        'whatsapp'   => $lead->whatsapp ?: null,
                        'company_id' => $company?->id,
                        'created_by' => $lead->created_by,
                    ]);
                    $made['contacts']++;
                }

                $lead->forceFill(['contact_id' => $contact->id, 'company_id' => $company?->id])->save();
                $made['linked']++;
            }
        });

        $this->info("Linked {$made['linked']} lead(s): {$made['contacts']} contact(s) and {$made['companies']} company(ies) created.");

        return self::SUCCESS;
    }
}