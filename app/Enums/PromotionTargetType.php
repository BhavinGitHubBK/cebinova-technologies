<?php

namespace App\Enums;

enum PromotionTargetType: string
{
    case AllWebsitePackages = 'all_website_packages';
    case AllMarketingPackages = 'all_marketing_packages';

    public function label(): string
    {
        return match ($this) {
            self::AllWebsitePackages => 'Website packages',
            self::AllMarketingPackages => 'Marketing packages',
        };
    }
}
