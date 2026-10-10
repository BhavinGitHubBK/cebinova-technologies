<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        PageSection::query()->firstOrCreate(
            ['page' => 'home', 'key' => 'hero'],
            [
                'heading' => 'Technology That Helps Every Business Grow.',
                'subheading' => config('cebinova.positioning'),
                'body' => 'From your first website to eCommerce, custom software and AI-powered automation, CEBINOVA gives your business the technology it needs to start, operate and grow.',
                'cta_label' => 'Get Free Consultation',
                'cta_url' => '/contact',
                'secondary_cta_label' => 'Explore Our Solutions',
                'secondary_cta_url' => '/solutions',
                'is_visible' => true,
                'sort_order' => 1,
            ]
        );

        PageSection::query()->firstOrCreate(
            ['page' => 'home', 'key' => 'proof'],
            [
                'heading' => 'Why businesses can trust CEBINOVA.',
                'subheading' => 'Proof',
                'body' => 'Real counts from the work we publish. A number stays off this page until it is true.',
                'settings' => [
                    'proof' => [
                        ['key' => 'projects', 'value' => '', 'label' => 'Projects delivered'],
                        ['key' => 'years', 'value' => '', 'label' => 'Years of experience'],
                        ['key' => 'support', 'value' => '', 'label' => 'Support commitment'],
                    ],
                ],
                'is_visible' => true,
                'sort_order' => 2,
            ]
        );

        PageSection::query()->firstOrCreate(
            ['page' => 'home', 'key' => 'cta'],
            [
                'heading' => 'Ready to Take Your Business Forward?',
                'subheading' => 'Next step',
                'body' => 'Start with the solution you need today. CEBINOVA can grow with you tomorrow.',
                'cta_label' => 'Get Free Consultation',
                'cta_url' => '/contact',
                'secondary_cta_label' => 'Talk to CEBINOVA',
                'secondary_cta_url' => '/whatsapp',
                'is_visible' => true,
                'sort_order' => 90,
            ]
        );

        $pages = [
            ['about', 'hero', 'About', 'Technology built around your business.', 'CEBINOVA Technologies is a complete technology partner for businesses that want to move from traditional operations to smart digital systems - without buying more than they need.', '/contact', 'View Services', '/services', [
                ['value' => 'ONE', 'label' => 'Technology partner'],
                ['value' => '5', 'label' => 'Growth stages'],
                ['value' => '6+', 'label' => 'Solution areas'],
                ['value' => 'E2E', 'label' => 'Digital journey'],
            ], 1],
            ['services', 'hero', 'Services', 'Complete technology services. One partner.', 'Clear categories so you can start with the work that matters now - then add systems and growth support as the business moves forward.', '/contact', 'See Solution Pricing', '/pricing', [], 1],
            ['solutions', 'hero', 'Solutions', 'Business solutions built for real operations.', 'Practical digital solutions for different businesses - with working demos that show what CEBINOVA can build.', '/contact', 'View Demos', '/demos', [
                ['value' => '3', 'label' => 'Live demos'],
                ['value' => '6', 'label' => 'Solution areas'],
                ['value' => '9+', 'label' => 'Industries'],
                ['value' => 'E2E', 'label' => 'Growth path'],
            ], 1],
            ['industries', 'hero', 'Industries', 'Technology for every business.', 'Sample starting solutions for the kinds of businesses we are built to help. If you do not see yours, tell us how you operate.', '/contact', 'View Solutions', '/solutions', [
                ['value' => '13+', 'label' => 'Industry types'],
                ['value' => 'ONE', 'label' => 'Technology partner'],
                ['value' => '6+', 'label' => 'Solution areas'],
                ['value' => 'E2E', 'label' => 'Digital journey'],
            ], 1],
            ['demos', 'hero', 'Demos', 'See what CEBINOVA can build.', 'Working digital solution demos for real business categories - not slide decks. Open a demo, then tell us what your business needs.', '/contact', 'Explore All Solutions', '/solutions', [], 1],
            ['contact', 'hero', 'Contact', 'Tell us about your business.', 'Share where you are today. We will help you identify the right technology for the next stage of growth.', '#enquiry-form', 'Ask on WhatsApp', '/whatsapp', [
                ['value' => 'FREE', 'label' => 'Consultation'],
                ['value' => 'AHM', 'label' => 'Ahmedabad based'],
                ['value' => 'WA', 'label' => 'Quick replies'],
                ['value' => 'ONE', 'label' => 'Technology partner'],
            ], 1],
            ['pricing', 'hero', 'Pricing', 'Choose the Right Solution for Your Business', 'Start with a clear website package, then add apps, domain, hosting and support only if you need them. See a real estimate before you talk to us.', '/contact', 'Open Calculator', '#calculator', [], 1],
            ['marketing', 'hero', 'Marketing Packages', (string) config('cebinova.marketing.hero_title'), (string) config('cebinova.marketing.hero_text'), '/contact', 'See Plan Prices', '#marketing-plans', [], 1],
            ['privacy', 'page', 'Legal', 'Privacy Policy', 'A clear policy for the presentation website. This will be refined before public launch.', '/contact', null, null, [], 1],
            ['terms', 'page', 'Legal', 'Terms', 'These terms cover use of this website. Project work is scoped separately in a proposal or agreement.', '/contact', null, null, [], 1],
        ];

        foreach ($pages as [$page, $key, $eyebrow, $heading, $lead, $ctaUrl, $secondaryLabel, $secondaryUrl, $proof, $order]) {
            PageSection::query()->firstOrCreate(
                ['page' => $page, 'key' => $key],
                [
                    'subheading' => $eyebrow,
                    'heading' => $heading,
                    'body' => $lead,
                    'cta_label' => 'Get Free Consultation',
                    'cta_url' => $ctaUrl,
                    'secondary_cta_label' => $secondaryLabel,
                    'secondary_cta_url' => $secondaryUrl,
                    'settings' => $proof === [] ? [] : ['proof' => $proof],
                    'is_visible' => true,
                    'sort_order' => $order,
                ]
            );
        }

        PageSection::query()->firstOrCreate(
            ['page' => 'privacy', 'key' => 'body'],
            [
                'heading' => 'Privacy Policy',
                'body' => "CEBINOVA Technologies collects the information you submit through the enquiry form - such as name, business name, phone, email and project details - so we can respond to your request.\n\nWe do not sell this information. It is stored to follow up on consultations and related work.\n\nIf you use WhatsApp, that conversation is also subject to WhatsApp’s own terms.",
                'is_visible' => true,
                'sort_order' => 2,
            ]
        );

        PageSection::query()->firstOrCreate(
            ['page' => 'terms', 'key' => 'body'],
            [
                'heading' => 'Terms',
                'body' => "This website describes services and demo solutions for CEBINOVA Technologies. Content is for information and presentation. It is not a binding offer until we confirm scope and pricing in writing.\n\nDemo projects are labelled as concept / demo solutions and should not be read as live client case studies.\n\nEnquiry submissions do not create a contract. We will confirm next steps after reviewing your business needs.",
                'is_visible' => true,
                'sort_order' => 2,
            ]
        );
    }
}
