<?php

namespace App\Services;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Support\MarketingPackages;

class LeadService
{
    public function storeFromRequest(StoreLeadRequest $request): ?Lead
    {
        if ($request->isHoneypotTriggered()) {
            return null;
        }

        $category = $request->validated('package_category');
        $duration = $request->validated('plan_duration');

        return Lead::query()->create([
            'name' => $request->validated('name'),
            'business_name' => $request->validated('business_name'),
            'phone' => $request->validated('phone'),
            'whatsapp' => $request->validated('whatsapp'),
            'email' => $request->validated('email'),
            'business_type' => $request->validated('business_type'),
            'service' => $request->validated('service'),
            'package_category' => $category,
            'plan_duration' => $duration,
            'selected_price' => MarketingPackages::formattedPrice($category, $duration),
            'city' => $request->validated('city'),
            'budget' => $request->validated('budget'),
            'message' => $request->validated('message'),
            'free_consultation' => $request->boolean('consultation'),
            'source' => $this->resolvedSource($request, $category),
            'status' => Lead::STATUS_NEW,
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
