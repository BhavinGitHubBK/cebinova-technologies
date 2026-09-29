<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Service;
use App\Models\Solution;
use Illuminate\Database\Seeder;

class SolutionIndustrySeeder extends Seeder
{
    public function run(): void
    {
        $serviceMap = ['kirana' => ['web-development', 'ecommerce', 'custom-software'], 'retail' => ['web-development', 'ecommerce', 'digital-growth'], 'professional-services' => ['web-development', 'digital-growth'], 'ecommerce' => ['ecommerce', 'custom-software'], 'business-management' => ['custom-software', 'ai-automation'], 'ai-automation' => ['ai-automation', 'custom-software']];
        foreach ($serviceMap as $solutionSlug => $serviceSlugs) {
            $solution = Solution::query()->where('slug', $solutionSlug)->first();
            if (! $solution) continue;
            $ids = Service::query()->whereIn('slug', $serviceSlugs)->pluck('id', 'slug');
            $solution->services()->sync(collect($serviceSlugs)->mapWithKeys(fn ($slug, $index) => isset($ids[$slug]) ? [$ids[$slug] => ['sort_order' => $index]] : [])->all());
        }

        $industryMap = ['kirana' => ['kirana', 'ecommerce'], 'dairy' => ['business-management'], 'food' => ['ecommerce', 'retail'], 'retail' => ['retail', 'ecommerce'], 'manufacturing' => ['business-management'], 'wholesale' => ['business-management', 'ecommerce'], 'export' => ['business-management'], 'professional' => ['professional-services', 'business-management'], 'healthcare' => ['professional-services', 'business-management'], 'education' => ['professional-services', 'ai-automation'], 'real-estate' => ['professional-services', 'business-management'], 'startups' => ['ai-automation', 'business-management'], 'services' => ['professional-services', 'business-management', 'ai-automation']];
        foreach ($industryMap as $industrySlug => $solutionSlugs) {
            $industry = Industry::query()->where('slug', $industrySlug)->first();
            if (! $industry) continue;
            $ids = Solution::query()->whereIn('slug', $solutionSlugs)->pluck('id', 'slug');
            $industry->solutions()->sync(collect($solutionSlugs)->mapWithKeys(fn ($slug, $index) => isset($ids[$slug]) ? [$ids[$slug] => ['sort_order' => $index]] : [])->all());
        }
    }
}
