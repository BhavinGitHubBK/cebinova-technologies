<?php

namespace Tests\Feature;

use App\Support\MarketingPackages;
use Tests\TestCase;

class MarketingPackagesHelperTest extends TestCase
{
    public function test_helper_reads_config_prices(): void
    {
        $this->assertSame(4999, MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
        $this->assertContains('Regular Marketing', MarketingPackages::categories());
        $this->assertTrue(MarketingPackages::isValid('Regular Marketing', 'Monthly'));
        $this->assertFalse(MarketingPackages::isValid('Regular Marketing', 'NotAPlan'));
    }

    public function test_package_array_matches_catalog_keys(): void
    {
        $regular = MarketingPackages::packageArray('regular');
        $this->assertSame('Regular Marketing', $regular['title']);
        $this->assertArrayHasKey('monthly', $regular['plans']);
    }
}
