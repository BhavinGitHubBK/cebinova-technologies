<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Support\MarketingPackages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadService
{
    public function storeFromRequest(StoreLeadRequest $request, ?PricingCalculator $pricing = null): ?Lead
    {
        if ($request->isHoneypotTriggered()) {
            return null;
        }

        $category = $request->validated('package_category');
        $duration = $request->validated('plan_duration');

        $serviceRecord = null;
        if ($request->validated('service_id')) {
            $serviceRecord = Service::query()->active()->find($request->validated('service_id'));
            if (! $serviceRecord) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'service_id' => 'The selected service is unavailable.',
                ]);
            }
        }

        $marketingData = null;
        if ($request->validated('marketing_package_id')) {
            $marketingData = app(MarketingPackageCalculator::class)->calculate(
                (int) $request->validated('marketing_package_id'),
                (int) $request->validated('marketing_plan_id')
            );
            $category = $marketingData['category'];
            $duration = $marketingData['duration'];
        }

        $pricingData = null;
        if ($request->validated('pricing_package_id')) {
            $pricingData = ($pricing ?? app(PricingCalculator::class))->calculate(
                (int) $request->validated('pricing_package_id'),
                $request->validated('pricing_option_ids', [])
            );
        }

        $duplicate = Lead::query()
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where(function ($query) use ($request) {
                $query->where('phone', $request->validated('phone'));

                if ($request->validated('email')) {
                    $query->orWhere('email', $request->validated('email'));
                }
            })
            ->latest()
            ->first();

        if ($duplicate) {
            return $duplicate;
        }

        $lead = Lead::query()->create([
            'name' => $request->validated('name'),
            'business_name' => $request->validated('business_name'),
            'phone' => $request->validated('phone'),
            'whatsapp' => $request->validated('whatsapp'),
            'email' => $request->validated('email'),
            'business_type' => $request->validated('business_type'),
            'service' => $serviceRecord?->name ?? $request->validated('service'),
            'service_id' => $serviceRecord?->id,
            'service_snapshot' => $serviceRecord ? [
                'service_id' => $serviceRecord->id,
                'name' => $serviceRecord->name,
                'slug' => $serviceRecord->slug,
                'captured_at' => now()->toIso8601String(),
            ] : null,
            'package_category' => $category,
            'plan_duration' => $duration,
            'selected_price' => isset($marketingData['price']) ? cebinova_inr($marketingData['price']) : MarketingPackages::formattedPrice($category, $duration),
            'marketing_package_id' => $marketingData['package_id'] ?? null,
            'marketing_plan_id' => $marketingData['plan_id'] ?? null,
            'marketing_snapshot' => $marketingData['snapshot'] ?? null,
            'pricing_package_id' => $pricingData['package_id'] ?? null,
            'pricing_option_ids' => $pricingData['option_ids'] ?? null,
            'estimated_total' => $pricingData['total'] ?? null,
            'pricing_snapshot' => $pricingData['snapshot'] ?? null,
            'city' => $request->validated('city'),
            'budget' => $request->validated('budget'),
            'message' => $request->validated('message'),
            'free_consultation' => $request->boolean('consultation'),
            'source' => $serviceRecord ? 'Service' : $this->resolvedSource($request, $category),
            'page_url' => mb_substr((string) $request->headers->get('referer'), 0, 2048) ?: null,
            'status' => Lead::STATUS_NEW,
        ]);

        $this->sendNotifications($lead);

        return $lead;
    }

    public function updateStatus(Lead $lead, LeadStatus $status): void
    {
        $updates = ['status' => $status->value];
        $timestampColumn = $status->timestampColumn();

        if ($timestampColumn && ! $lead->{$timestampColumn}) {
            $updates[$timestampColumn] = now();
        }

        $lead->update($updates);
    }

    private function sendNotifications(Lead $lead): void
    {
        $recipient = config('cebinova.leads.notification_email');

        try {
            if (filled($recipient)) {
                Mail::raw($this->adminMessage($lead), function ($message) use ($lead, $recipient) {
                    $message->to($recipient)->subject('New website lead: '.$lead->name);
                });
            }

            if (config('cebinova.leads.send_acknowledgement') && filled($lead->email)) {
                Mail::raw(
                    "Thank you! Your enquiry has been received. Our team will contact you shortly.\n\nCEBINOVA Technologies\nTechnology for Every Business",
                    function ($message) use ($lead) {
                        $message->to($lead->email)->subject('We received your enquiry');
                    }
                );
            }
        } catch (Throwable $exception) {
            Log::warning('Lead saved but email notification failed.', [
                'lead_id' => $lead->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function adminMessage(Lead $lead): string
    {
        return implode("\n", [
            'A new website enquiry has been received.',
            '',
            'Name: '.$lead->name,
            'Business: '.($lead->business_name ?: 'Not provided'),
            'Phone: '.$lead->phone,
            'Email: '.($lead->email ?: 'Not provided'),
            'Service: '.($lead->service ?: 'Not provided'),
            'Budget: '.($lead->budget ?: 'Not provided'),
            'Message: '.($lead->message ?: 'Not provided'),
            'Submitted: '.$lead->created_at?->toDateTimeString(),
        ]);
    }

    private function resolvedSource(StoreLeadRequest $request, ?string $category): string
    {
        if ($category && in_array($category, MarketingPackages::categories(), true)) {
            return $category;
        }

        $allowed = config('cebinova.leads.sources');
        $submitted = $request->validated('source');

        if (is_string($submitted) && in_array($submitted, $allowed, true)) {
            return $submitted;
        }

        return 'Website Contact';
    }
}
