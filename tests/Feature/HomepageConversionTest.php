<?php

namespace Tests\Feature;

use App\Models\Demo;
use App\Models\Industry;
use App\Models\Package;
use App\Models\PageSection;
use App\Models\Solution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_proof_section_is_not_on_the_homepage(): void
    {
        PageSection::query()->create([
            'page' => 'home',
            'key' => 'proof',
            'is_visible' => true,
            'sort_order' => 2,
            'settings' => [
                'proof' => [
                    ['key' => 'years', 'value' => '8', 'label' => 'Years of experience'],
                ],
            ],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Why businesses can trust CEBINOVA.', false)
            ->assertDontSee('home-proof', false)
            ->assertDontSee('Industries served', false)
            ->assertDontSee('Demo projects', false)
            ->assertDontSee('Years of experience', false);
    }

    public function test_problem_picker_needs_two_usable_solutions(): void
    {
        Solution::query()->update(['is_active' => false]);
        $this->solution('Only one', 'Shops need a catalogue.', 'A catalogue site.');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Start with the problem in front of you.', false)
            ->assertDontSee('Only one', false);

        $this->solution('Second path', 'Teams still track orders on paper.', 'An order system the staff can run.');

        $this->get('/')
            ->assertOk()
            ->assertSee('Start with the problem in front of you.', false)
            ->assertSee('Only one', false)
            ->assertSee('Second path', false)
            ->assertSee('Shops need a catalogue.', false)
            ->assertSee('An order system the staff can run.', false)
            ->assertSee('home-problems-card', false)
            ->assertSee('View this solution', false);
    }

    public function test_demo_previews_use_the_stored_asset_path_and_solution_category(): void
    {
        $solution = Solution::query()->where('slug', 'retail')->firstOrFail();
        $food = Industry::query()->where('slug', 'food')->firstOrFail();
        $demo = Demo::query()->create([
            'name' => 'Retail & Jewellery',
            'slug' => 'retail-shot',
            'short_description' => 'A polished catalogue for retailers.',
            'thumbnail' => 'assets/solutions/retail-preview.png',
            'is_active' => true,
            'is_featured' => true,
            'badge' => 'LIVE DEMO',
        ]);
        $demo->solutions()->sync([$solution->id]);
        $demo->industries()->sync([$food->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee(asset('assets/solutions/retail-preview.png'), false)
            ->assertDontSee('assets/assets/solutions/retail-preview.png', false)
            ->assertSee('home-port-cat">Retail', false)
            ->assertDontSee('Restaurants &amp; Food Businesses', false);
    }

    public function test_homepage_shows_three_website_packages_and_one_marketing_link(): void
    {
        foreach ([
            ['name' => 'Site Starter', 'slug' => 'site-starter', 'sort_order' => 1],
            ['name' => 'Site Business', 'slug' => 'site-business', 'sort_order' => 2, 'is_highlighted' => true],
            ['name' => 'Site Professional', 'slug' => 'site-professional', 'sort_order' => 3],
            ['name' => 'Site Extra', 'slug' => 'site-extra', 'sort_order' => 4],
        ] as $package) {
            Package::query()->create($package + [
                'category' => 'website',
                'is_active' => true,
                'short_description' => $package['name'].' summary',
            ]);
        }

        Package::query()->create([
            'category' => 'marketing_regular',
            'name' => 'Regular Marketing',
            'slug' => 'regular-marketing',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-package="Site Business"', false)
            ->assertSee('data-package="Site Starter"', false)
            ->assertSee('data-package="Site Professional"', false)
            ->assertDontSee('data-package="Site Extra"', false)
            ->assertDontSee('Regular Marketing', false)
            ->assertSee('Explore marketing packages', false)
            ->assertSee(route('marketing-packages'), false)
            ->assertSee('#plans', false);
    }

    private function solution(string $name, string $audience, string $outcome): void
    {
        Solution::query()->create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'audience' => $audience,
            'outcome' => $outcome,
            'is_active' => true,
        ]);
    }
}
