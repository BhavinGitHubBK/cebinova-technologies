<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageRoutesTest extends TestCase
{
    public static function pages(): array
    {
        return [
            ['/'],
            ['/about'],
            ['/services'],
            ['/services/web-development'],
            ['/services/ecommerce'],
            ['/services/custom-software'],
            ['/services/ai-automation'],
            ['/services/digital-growth'],
            ['/services/digital-business'],
            ['/solutions'],
            ['/solutions/ai-automation'],
            ['/solutions/ecommerce'],
            ['/solutions/business-management'],
            ['/industries'],
            ['/marketing-packages'],
            ['/portfolio'],
            ['/demos'],
            ['/pricing'],
            ['/solutions/retail'],
            ['/solutions/professional-services'],
            ['/contact'],
            ['/privacy-policy'],
            ['/terms'],
        ];
    }

    #[DataProvider('pages')]
    public function test_marketing_pages_return_ok(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public function test_homepage_uses_brand_title(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SARVIX Technologies', false)
            ->assertSee('Technology That Helps', false)
            ->assertSee('Every Business Grow.', false)
            ->assertSee('What SARVIX builds', false)
            ->assertSee('>Web<', false)
            ->assertSee('>eCommerce<', false)
            ->assertSee('>Growth<', false);
    }

    public function test_site_chrome_uses_current_logo_assets(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('assets/brand/sarvix-s-icon.webp', false)
            ->assertSee('assets/brand/sarvix-s-icon.png', false)
            ->assertSee('class="sarvix-brand-name"', false)
            ->assertSee('class="sarvix-brand-tech"', false)
            ->assertSee('Technology for Every Business.', false)
            ->assertSee('favicon.png', false)
            ->assertDontSee('images/branding/logo-nav.png', false)
            ->assertDontSee('sarvix-brand-rule', false);

        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<header id="site-nav"[\s\S]*?<\/header>/',
            $html,
            'Expected a site header.'
        );
        preg_match('/<header id="site-nav"[\s\S]*?<\/header>/', $html, $header);
        $this->assertStringContainsString('Technology for Every Business.', $header[0]);
        $this->assertStringContainsString('TECHNOLOGIES', $header[0]);
        $this->assertStringContainsString('SARVI', $header[0]);
        $this->assertStringContainsString('sarvix-accent', $header[0]);
        $this->assertStringContainsString('sarvix-logo-icon', $header[0]);
        $this->assertStringNotContainsString('background-image', $header[0]);

        $this->assertFileExists(public_path('assets/brand/sarvix-s-icon.webp'));
        $this->assertFileExists(public_path('assets/brand/sarvix-s-icon.png'));
        $this->assertFileExists(public_path('favicon.png'));
    }

    public function test_homepage_teases_three_marketing_categories(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Regular Marketing', false)
            ->assertSee('Festival Marketing', false)
            ->assertSee('Complete Growth', false)
            ->assertSee('Monthly', false)
            ->assertSee('Quarterly', false)
            ->assertSee('Half-Yearly', false)
            ->assertSee('Yearly', false)
            ->assertSee('Explore Marketing Plans', false)
            ->assertSee('Marketing Packages', false)
            ->assertDontSee('10 Social Media Posts', false)
            ->assertDontSee('Full package details coming soon', false)
            ->assertDontSee('BizPilot', false)
            ->assertSee('Technology Built Around Your Business.', false)
            ->assertSee('Go Digital', false)
            ->assertSee('Discover SARVIX', false)
            ->assertSee('Solutions Built for Real Businesses.', false)
            ->assertSee('View Demos', false)
            ->assertSee('Explore All Solutions', false);
    }

    public function test_legacy_marketing_and_packages_urls_redirect(): void
    {
        $this->get('/marketing')->assertRedirect('/marketing-packages');
        $this->get('/packages')->assertRedirect('/marketing-packages');
    }

    public function test_kirana_url_redirects_to_pricing(): void
    {
        $this->get('/solutions/kirana')->assertRedirect('/pricing');
    }

    public function test_packages_page_presents_marketing_structure(): void
    {
        $this->get('/marketing-packages')
            ->assertOk()
            ->assertSee('Your Complete Marketing Team - Starting at ₹4,999/month.', false)
            ->assertSee('We plan, design and send ready-to-post content. You approve. Customers keep seeing your business.', false)
            ->assertSee('Regular Marketing Plans', false)
            ->assertSee('Festival Marketing Plans', false)
            ->assertSee('Complete Growth', false)
            ->assertSee('BEST VALUE', false)
            ->assertSee('RECOMMENDED', false)
            ->assertSee('Which plan is for you?', false)
            ->assertSee('Which plan should I pick?', false)
            ->assertSee('₹4,999', false)
            ->assertSee('₹13,499', false)
            ->assertSee('₹24,999', false)
            ->assertSee('₹44,999', false)
            ->assertSee('₹1,499', false)
            ->assertSee('₹3,499', false)
            ->assertSee('₹5,999', false)
            ->assertSee('₹9,999', false)
            ->assertSee('₹15,999', false)
            ->assertSee('₹29,999', false)
            ->assertSee('₹49,999', false)
            ->assertSee('₹4,999 less than buying Regular + Festival yearly.', false)
            ->assertSee('/ month', false)
            ->assertSee('/ 3 months', false)
            ->assertSee('/ year', false)
            ->assertSee('Just ₹3,750/month · Save ₹14,989', false)
            ->assertSee('GST extra, if applicable. No hidden package charges.', false)
            ->assertSee('Festival creatives follow the SARVIX Festival Calendar - Diwali, Navratri, Independence Day and other important dates.', false)
            ->assertSee('10 Social Media Posts', false)
            ->assertSee('Current Month Festival Coverage', false)
            ->assertSee('Paid Advertising Budget', false)
            ->assertSee('Get This Plan', false)
            ->assertSee('Ask on WhatsApp', false)
            ->assertSee('Discuss', false)
            ->assertSee('Monthly Report', false)
            ->assertSee('Why is Complete Growth only ₹1,000 more than Regular?', false)
            ->assertDontSee('₹52,999', false)
            ->assertDontSee('limited time', false);
    }

    public function test_yearly_marketing_plans_save_against_monthly(): void
    {
        $this->assertSame(14989, \App\Support\MarketingPackages::savingsVsMonthly('Regular Marketing', 'Yearly'));
        $this->assertSame(7989, \App\Support\MarketingPackages::savingsVsMonthly('Festival Marketing', 'Yearly'));
        $this->assertSame(21989, \App\Support\MarketingPackages::savingsVsMonthly('Complete Growth', 'Yearly'));
        $this->assertNull(\App\Support\MarketingPackages::savingsVsMonthly('Regular Marketing', 'Monthly'));
        $this->assertSame(3750, \App\Support\MarketingPackages::effectiveMonthly('Regular Marketing', 'Yearly'));
        $this->assertSame('₹44,999/year · Just ₹3,750/month · Save ₹14,989', \App\Support\MarketingPackages::priceHeadline('Regular Marketing', 'Yearly'));
    }

    public function test_festival_marketing_plans_include_reels(): void
    {
        $expected = [
            'monthly' => '2 Reels / Video Creatives',
            'quarterly' => '10 Reels / Video Creatives',
            'half-yearly' => '20 Reels / Video Creatives',
            'yearly' => '50 Reels / Video Creatives',
        ];

        foreach ($expected as $duration => $item) {
            $this->assertContains($item, config('sarvix.marketing.festival.plans.'.$duration.'.includes'));
        }

        $this->get('/marketing-packages')
            ->assertOk()
            ->assertSee('Reels are short videos for Instagram, Facebook and WhatsApp. These are design or motion creatives. Professional video shooting is not included.', false);
    }

    public function test_service_page_guides_the_next_step(): void
    {
        $this->get('/services/ai-automation')
            ->assertOk()
            ->assertSee('Who this is for', false)
            ->assertSee('Fewer missed leads, faster replies and less manual follow-up.', false)
            ->assertSee('What this includes', false)
            ->assertSee('How it works', false)
            ->assertSee('Understand', false)
            ->assertSee('Do I need AI on day one?', false)
            ->assertSee('Get Free Consultation', false)
            ->assertSee('Ask on WhatsApp', false)
            ->assertSee('See AI solution', false)
            ->assertSee('Ready to Take Your Business Forward?', false)
            ->assertSee('/solutions/ai-automation', false);
    }

    public function test_solution_page_guides_the_next_step(): void
    {
        $this->get('/solutions/ai-automation')
            ->assertOk()
            ->assertSee('Who this is for', false)
            ->assertSee('View AI service', false)
            ->assertSee('How it works', false)
            ->assertSee('Get Free Consultation', false)
            ->assertSee('Ask on WhatsApp', false)
            ->assertSee('Ready to Take Your Business Forward?', false)
            ->assertSee('/services/ai-automation', false);
    }

    public function test_contact_page_shows_mobile_number(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('+91 96246 8831', false)
            ->assertSee('tel:+91962468831', false)
            ->assertDontSee('Phone - available on request', false);
    }

    public function test_contact_form_preselects_package_enquiry(): void
    {
        $this->get(package_enquiry_url('Regular Marketing', 'Quarterly'))
            ->assertOk()
            ->assertSee('value="Digital Marketing"', false)
            ->assertSee('value="Regular Marketing"', false)
            ->assertSee('value="Quarterly"', false)
            ->assertSee('₹13,499', false)
            ->assertSee('Selected plan', false);
    }

    public function test_contact_form_preselects_complete_growth_yearly(): void
    {
        $this->get(package_enquiry_url('Complete Growth', 'Yearly'))
            ->assertOk()
            ->assertSee('value="Digital Marketing"', false)
            ->assertSee('value="Complete Growth"', false)
            ->assertSee('value="Yearly"', false)
            ->assertSee('₹49,999', false)
            ->assertSee('Selected plan', false);
    }

    public function test_demos_page_is_sarvix_branded(): void
    {
        $this->get('/demos')
            ->assertOk()
            ->assertSee('See What SARVIX Can Build for Your Business.', false)
            ->assertSee('SARVIX Kirana &amp; Grocery Solution', false)
            ->assertSee('LIVE DEMO', false)
            ->assertDontSee('BizPilot', false)
            ->assertDontSee('file:///', false);
    }

    public function test_pricing_estimate_sidebar_styles_are_sticky(): void
    {
        $overlay = file_get_contents(public_path('assets/solutions/kirana-pricing/css/sarvix-brand.css'));
        $estimate = file_get_contents(public_path('assets/solutions/kirana-pricing/css/estimate-premium.css'));

        $this->assertNotFalse($overlay);
        $this->assertNotFalse($estimate);
        $this->assertStringContainsString('overflow-x: visible', $overlay);
        $this->assertStringContainsString('html:has(.kirana-pricing) main', $overlay);
        $this->assertMatchesRegularExpression('/position:\s*sticky/', $estimate);
        $this->assertMatchesRegularExpression('/--estimate-sticky-top/', $overlay);
        $this->assertStringContainsString('top: var(--estimate-sticky-top, 5.75rem)', $estimate);
    }

    public function test_pricing_estimate_badges_avoid_tailwind_fixed_class(): void
    {
        $js = file_get_contents(public_path('assets/solutions/kirana-pricing/js/script.js'));

        $this->assertNotFalse($js);
        $this->assertStringContainsString('charge-fixed', $js);
        $this->assertDoesNotMatchRegularExpression(
            '/est-charge-tag[\'" ][^\'"]*\bfixed\b/',
            $js
        );
    }

    public function test_pricing_page_keeps_source_pricing_separate_from_marketing(): void
    {
        $this->get('/pricing')
            ->assertOk()
            ->assertSee('Build Your Digital Store', false)
            ->assertSee('SARVIX Pricing', false)
            ->assertSee('Choose website', false)
            ->assertSee('Choose Support Plan', false)
            ->assertSee('Book Free Consultation', false)
            ->assertSee('View Details', false)
            ->assertSee('value="business"', false)
            ->assertSee('Included in every website', false)
            ->assertSee('₹14,999', false)
            ->assertSee('₹24,999', false)
            ->assertSee('₹29,999', false)
            ->assertSee('id="calculator"', false)
            ->assertSee('/pricing', false)
            ->assertSee('id="site-nav"', false)
            ->assertDontSee('BizPilot', false)
            ->assertDontSee('file:///', false)
            ->assertDontSee('Regular Marketing', false)
            ->assertDontSee('SARVIX Kirana Solution', false);
    }

    public function test_primary_nav_uses_merged_labels(): void
    {
        $nav = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/aria-label="Primary"[\s\S]*Marketing Packages[\s\S]*Pricing[\s\S]*Demos[\s\S]*Contact/', $nav);
        $this->assertDoesNotMatchRegularExpression('/aria-label="Primary"[\s\S]*class="nav-link[^"]*">\s*Industries\s*</', $nav);
        $this->assertDoesNotMatchRegularExpression('/aria-label="Primary"[\s\S]*class="nav-link[^"]*">\s*Packages\s*</', $nav);
        $this->assertDoesNotMatchRegularExpression('/aria-label="Primary"[\s\S]*class="nav-link[^"]*">\s*Marketing\s*</', $nav);
    }

    public function test_kirana_plan_enquiry_uses_business_solution_not_marketing_package(): void
    {
        $this->get(solution_enquiry_url([
            'solution' => 'Kirana & Grocery',
            'plan' => 'Starter',
            'price' => '₹14,999',
            'source' => 'SARVIX Kirana Solution',
            'business_type' => 'Kirana & Grocery',
        ]))
            ->assertOk()
            ->assertSee('value="Business Solution"', false)
            ->assertSee('value="SARVIX Kirana Solution"', false)
            ->assertSee('Kirana &amp; Grocery', false)
            ->assertSee('Starter', false)
            ->assertDontSee('package_category=Regular%20Marketing', false);
    }

    public function test_live_demo_routes_serve_html_without_old_brand(): void
    {
        $this->get('/demos/kirana')
            ->assertOk()
            ->assertSee('SARVIX Technologies', false)
            ->assertDontSee('BizPilot', false)
            ->assertDontSee('file:///', false);
    }
}
