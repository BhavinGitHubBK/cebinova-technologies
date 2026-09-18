<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Asha Patel',
            'business_name' => 'Patel Retail',
            'phone' => '9876543210',
            'whatsapp' => '9876543210',
            'email' => 'asha@example.com',
            'business_type' => 'Retail',
            'service' => 'Website Development',
            'city' => 'Ahmedabad',
            'budget' => 'To be discussed',
            'message' => 'We need a business website and catalogue.',
            'consultation' => '1',
            'source' => 'Website Contact',
            'website' => '',
        ], $overrides);
    }

    public static function marketingPackages(): array
    {
        return [
            'regular monthly' => ['Regular Marketing', 'Monthly', '₹4,999'],
            'regular quarterly' => ['Regular Marketing', 'Quarterly', '₹13,499'],
            'regular half-yearly' => ['Regular Marketing', 'Half-Yearly', '₹24,999'],
            'regular yearly' => ['Regular Marketing', 'Yearly', '₹44,999'],
            'festival monthly' => ['Festival Marketing', 'Monthly', '₹1,499'],
            'festival quarterly' => ['Festival Marketing', 'Quarterly', '₹3,499'],
            'festival half-yearly' => ['Festival Marketing', 'Half-Yearly', '₹5,999'],
            'festival yearly' => ['Festival Marketing', 'Yearly', '₹9,999'],
            'growth monthly' => ['Complete Growth', 'Monthly', '₹5,999'],
            'growth quarterly' => ['Complete Growth', 'Quarterly', '₹15,999'],
            'growth half-yearly' => ['Complete Growth', 'Half-Yearly', '₹29,999'],
            'growth yearly' => ['Complete Growth', 'Yearly', '₹49,999'],
        ];
    }

    public function test_valid_enquiry_is_stored(): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload());

        $response->assertOk()->assertJson([
            'ok' => true,
            'message' => 'Thank you for contacting CEBINOVA Technologies.',
        ]);
        $this->assertDatabaseHas('leads', [
            'email' => 'asha@example.com',
            'business_name' => 'Patel Retail',
            'phone' => '9876543210',
            'whatsapp' => '9876543210',
            'city' => 'Ahmedabad',
            'status' => 'New',
            'source' => 'Website Contact',
            'free_consultation' => 1,
        ]);
    }

    public function test_name_phone_and_service_are_required(): void
    {
        $response = $this->postJson(route('contact.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'phone', 'service']);
        $this->assertSame(0, Lead::query()->count());
    }

    public function test_business_name_and_email_are_optional(): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload([
            'business_name' => '',
            'email' => '',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('leads', [
            'name' => 'Asha Patel',
            'business_name' => null,
            'email' => null,
        ]);
    }

    public function test_honeypot_is_accepted_but_not_stored(): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload([
            'website' => 'http://spam.example',
        ]));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(0, Lead::query()->count());
    }

    #[DataProvider('marketingPackages')]
    public function test_marketing_package_enquiry_stores_server_price(string $category, string $duration, string $price): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload([
            'service' => 'Digital Marketing',
            'package_category' => $category,
            'plan_duration' => $duration,
            'plan_price' => '₹1',
            'source' => $category,
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('leads', [
            'service' => 'Digital Marketing',
            'package_category' => $category,
            'plan_duration' => $duration,
            'selected_price' => $price,
            'source' => $category,
            'status' => 'New',
        ]);
    }

    public function test_package_fields_are_required_for_digital_marketing(): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload([
            'service' => 'Digital Marketing',
            'package_category' => '',
            'plan_duration' => '',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['package_category', 'plan_duration']);
        $this->assertSame(0, Lead::query()->count());
    }

    public function test_invalid_package_combination_is_rejected(): void
    {
        $response = $this->postJson(route('contact.store'), $this->validPayload([
            'service' => 'Digital Marketing',
            'package_category' => 'Complete Growth',
            'plan_duration' => 'Forever',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors(['plan_duration']);
        $this->assertSame(0, Lead::query()->count());
    }

    public function test_request_cannot_set_lead_status_or_notes(): void
    {
        $this->postJson(route('contact.store'), $this->validPayload([
            'status' => 'Converted',
            'notes' => 'ignore me',
        ]))->assertOk();

        $lead = Lead::query()->first();
        $this->assertSame('New', $lead->status);
        $this->assertNull($lead->notes);
    }

    public function test_free_consultation_source_is_stored(): void
    {
        $this->postJson(route('contact.store'), $this->validPayload([
            'source' => 'Free Consultation',
        ]))->assertOk();

        $this->assertDatabaseHas('leads', [
            'source' => 'Free Consultation',
            'free_consultation' => 1,
        ]);
    }

    public function test_technology_solution_source_is_stored(): void
    {
        $this->postJson(route('contact.store'), $this->validPayload([
            'service' => 'Custom Software',
            'source' => 'Technology Solution',
        ]))->assertOk();

        $this->assertDatabaseHas('leads', [
            'service' => 'Custom Software',
            'source' => 'Technology Solution',
        ]);
    }

    public function test_kirana_solution_enquiry_is_stored_without_marketing_package(): void
    {
        $this->postJson(route('contact.store'), $this->validPayload([
            'service' => 'Business Solution',
            'business_type' => 'Kirana & Grocery',
            'source' => 'CEBINOVA Kirana Solution',
            'message' => "Solution: Kirana & Grocery\nPlan: Starter\nIndicative price: ₹14,999",
        ]))->assertOk();

        $this->assertDatabaseHas('leads', [
            'service' => 'Business Solution',
            'business_type' => 'Kirana & Grocery',
            'source' => 'CEBINOVA Kirana Solution',
            'package_category' => null,
            'plan_duration' => null,
        ]);
    }

    public function test_html_is_stripped_from_text_fields(): void
    {
        $this->postJson(route('contact.store'), $this->validPayload([
            'name' => '<b>Asha</b>',
            'message' => '<script>alert(1)</script>Need a website',
        ]))->assertOk();

        $lead = Lead::query()->first();
        $this->assertSame('Asha', $lead->name);
        $this->assertSame('Need a website', $lead->message);
    }
}
