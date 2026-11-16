<?php

namespace Tests\Feature;

use App\Models\PromotionCampaign;
use App\Services\Promotions\PromotionEngine;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_menu_lists_every_active_service(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Our Services')
            ->assertSee(route('services.show', 'web-development'), false)
            ->assertSee(route('services.show', 'digital-growth'), false);
    }

    public function test_offers_menu_is_hidden_without_a_running_offer(): void
    {
        $this->offer('Upcoming Offer', now()->addDays(3), now()->addDays(9));

        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee(route('offers.index'), false)
            ->assertDontSee('Upcoming Offer');
    }

    public function test_single_running_offer_stays_out_of_navigation(): void
    {
        $this->offer('Navratri Special', now()->subHour(), now()->addDays(5));

        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee(route('offers.show', 'navratri-special'), false)
            ->assertDontSee('Navratri Special')
            ->assertDontSee('Running Offers');
    }

    public function test_several_running_offers_stay_out_of_navigation(): void
    {
        $this->offer('Diwali Special', now()->subHour(), now()->addDays(30));
        $this->offer('Navratri Special', now()->subHour(), now()->addDays(5));

        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee('2 live')
            ->assertDontSee('Running Offers')
            ->assertDontSee('View All Offers');
    }

    private function offer(string $name, $starts, $ends): void
    {
        PromotionCampaign::query()->create([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'publication_status' => 'published',
            'coupon_required' => false,
            'show_pricing_badge' => true,
        ]);
        app(PromotionEngine::class)->forgetCache();
    }
}
