<?php

namespace App\Services;

use App\Mail\LeadEnquiryMail;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class LeadNotifier
{
    public function notify(Lead $lead): void
    {
        $admin = config('cebinova.leads.notify_email') ?: config('cebinova.contact.email');

        if (filled($admin)) {
            $this->sendMail($admin, $lead, 'admin');
        }

        if (filled($lead->email)) {
            $this->sendMail($lead->email, $lead, 'customer');
        }

        $webhook = config('cebinova.leads.whatsapp_webhook');
        if (! filled($webhook)) {
            return;
        }

        try {
            Http::timeout(5)->acceptJson()->post($webhook, [
                'event' => 'lead.created',
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'service' => $lead->service,
                'business_name' => $lead->business_name,
                'message' => $lead->message,
                'source' => $lead->source,
                'page_url' => $lead->page_url,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function sendMail(string $address, Lead $lead, string $audience): void
    {
        try {
            Mail::to($address)->send(new LeadEnquiryMail($lead, $audience));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
