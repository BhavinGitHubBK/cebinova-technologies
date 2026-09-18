<?php

namespace Tests\Feature;

use Tests\TestCase;

class PackageEnquiryTest extends TestCase
{
    public function test_package_enquiry_url_prefills_digital_marketing_without_client_price(): void
    {
        $url = package_enquiry_url('Complete Growth', 'Yearly');

        $this->assertStringContainsString('service=Digital%20Marketing', $url);
        $this->assertStringContainsString('package_category=Complete%20Growth', $url);
        $this->assertStringContainsString('plan_duration=Yearly', $url);
        $this->assertStringContainsString('source=Complete%20Growth', $url);
        $this->assertStringNotContainsString('plan_price', $url);
    }

    public function test_contact_page_shows_locked_complete_growth_yearly_plan(): void
    {
        $this->get(package_enquiry_url('Complete Growth', 'Yearly'))
            ->assertOk()
            ->assertSee('Digital Marketing', false)
            ->assertSee('Complete Growth', false)
            ->assertSee('Yearly', false)
            ->assertSee('₹49,999', false)
            ->assertSee('Selected plan', false)
            ->assertSee('Thank you for contacting CEBINOVA Technologies.', false)
            ->assertSee('Our team will review your requirement and get in touch with you shortly.', false);
    }

    public function test_contact_page_shows_regular_quarterly_price_from_config(): void
    {
        $this->get(package_enquiry_url('Regular Marketing', 'Quarterly'))
            ->assertOk()
            ->assertSee('Regular Marketing', false)
            ->assertSee('Quarterly', false)
            ->assertSee('₹13,499', false);
    }

    public function test_packages_page_ctas_point_to_digital_marketing_enquiries(): void
    {
        $this->get('/marketing-packages')
            ->assertOk()
            ->assertSee('service=Digital%20Marketing', false)
            ->assertSee('package_category=Regular%20Marketing', false)
            ->assertSee('package_category=Festival%20Marketing', false)
            ->assertSee('package_category=Complete%20Growth', false)
            ->assertSee('plan_duration=Monthly', false)
            ->assertSee('plan_duration=Yearly', false);
    }

    public function test_general_whatsapp_message_is_prefilled(): void
    {
        config(['cebinova.contact.whatsapp' => '919876543210']);

        $url = whatsapp_url();

        $this->assertStringStartsWith('https://wa.me/919876543210?text=', $url);
        $this->assertStringContainsString(urlencode("Hi CEBINOVA,\nI'd like to know more about your technology solutions."), $url);
    }

    public function test_package_whatsapp_message_is_prefilled(): void
    {
        config(['cebinova.contact.whatsapp' => '919876543210']);

        $url = package_whatsapp_url('Complete Growth', 'Yearly');

        $this->assertStringContainsString(urlencode("Hi CEBINOVA,\nI'm interested in the Complete Growth – Yearly Plan.\nPlease share more details."), $url);
    }

    public function test_free_consultation_cta_tracks_source(): void
    {
        $this->get(consultation_url())
            ->assertOk()
            ->assertSee('value="Free Consultation"', false);
    }
}
