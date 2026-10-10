<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnalyticsAccountEvent;
use App\Models\AnalyticsPageview;
use App\Models\AnalyticsVisitor;
use App\Models\Customer;
use App\Models\User;
use App\Services\Analytics\VisitorCapture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class VisitorAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_page_view_is_stored_without_a_session_and_identity_is_attached_later(): void
    {
        $first = $this->postJson(route('analytics.visit'), [
            'path' => '/pricing',
            'title' => 'Pricing',
            'referrer' => 'https://google.com/search?q=cebinova',
        ])->assertOk()->assertJson(['tracked' => true, 'identified' => false]);

        $uuid = $first->json('visitor');
        $this->assertNotEmpty($uuid);

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/contact',
            'title' => 'Contact',
            'referrer' => 'http://127.0.0.1/pricing',
        ])->assertOk();

        $visitor = AnalyticsVisitor::query()->where('uuid', $uuid)->first();
        $this->assertNotNull($visitor);
        $this->assertSame('/pricing', $visitor->first_landing_path);
        $this->assertSame('/contact', $visitor->exit_path);
        $this->assertSame('google.com', $visitor->first_referrer_domain);
        $this->assertSame('Local network', $visitor->country);
        $this->assertSame(['/pricing', '/contact'], $visitor->pageviews()->orderBy('id')->pluck('path')->all());

        $this->postJson(route('analytics.identify'), [
            'visitor' => $uuid,
            'name' => 'Ramesh Patel',
            'mobile' => '9876543210',
            'path' => '/contact',
        ])->assertOk()->assertJson(['identified' => true, 'visitor' => $uuid]);

        $visitor->refresh();
        $this->assertSame('Ramesh Patel', $visitor->name);
        $this->assertSame('9876543210', $visitor->mobile);
        $this->assertSame(2, $visitor->pageviews()->count());
    }

    public function test_refreshing_the_same_page_is_stored_only_when_the_page_changes(): void
    {
        $first = $this->postJson(route('analytics.visit'), [
            'path' => '/pricing',
            'title' => 'Pricing',
        ])->assertOk();

        $uuid = $first->json('visitor');

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/pricing',
            'title' => 'Pricing',
        ])->assertOk();

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/about',
            'title' => 'About',
        ])->assertOk();

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/pricing',
            'title' => 'Pricing',
        ])->assertOk();

        $visitor = AnalyticsVisitor::query()->where('uuid', $uuid)->first();
        $this->assertSame(['/pricing', '/about', '/pricing'], $visitor->pageviews()->orderBy('id')->pluck('path')->all());
    }

    public function test_the_same_mobile_is_one_person_with_a_separate_visit(): void
    {
        $first = $this->postJson(route('analytics.visit'), [
            'path' => '/pricing',
            'title' => 'Pricing',
        ])->assertOk();

        $this->postJson(route('analytics.identify'), [
            'visitor' => $first->json('visitor'),
            'name' => 'Bhavin Khadela',
            'mobile' => '9624688531',
            'path' => '/pricing',
        ])->assertOk();

        $second = $this->postJson(route('analytics.visit'), [
            'path' => '/contact',
            'title' => 'Contact',
        ])->assertOk();

        $this->assertNotSame($first->json('visitor'), $second->json('visitor'));

        $merged = $this->postJson(route('analytics.identify'), [
            'visitor' => $second->json('visitor'),
            'name' => 'Bhavin Khadela',
            'mobile' => '9624688531',
            'path' => '/contact',
        ])->assertOk()->assertJson(['visitor' => $first->json('visitor')]);

        $this->assertSame(1, AnalyticsVisitor::query()->count());
        $visitor = AnalyticsVisitor::query()->first();
        $this->assertSame('Bhavin Khadela', $visitor->name);
        $this->assertSame(2, $visitor->visits()->count());
        $this->assertSame(['/pricing', '/contact'], $visitor->pageviews()->orderBy('id')->pluck('path')->all());
        $this->assertSame($first->json('visitor'), $merged->json('visitor'));
    }

    public function test_the_same_visitor_is_kept_when_only_the_stored_id_is_sent(): void
    {
        $uuid = (string) Str::uuid();

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/',
            'title' => 'Home',
        ])->assertOk();

        $this->flushSession();

        $this->postJson(route('analytics.visit'), [
            'visitor' => $uuid,
            'path' => '/about',
            'title' => 'About',
            'referrer' => 'http://127.0.0.1/',
        ])->assertOk();

        $this->assertSame(1, AnalyticsVisitor::query()->count());
        $this->assertSame(2, AnalyticsPageview::query()->count());
    }

    public function test_public_ip_location_is_saved_from_the_lookup(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'city' => 'Surat',
                'region' => 'Gujarat',
                'country' => 'India',
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
            ->postJson(route('analytics.visit'), [
                'path' => '/',
                'title' => 'Home',
            ])->assertOk();

        $visitor = AnalyticsVisitor::query()->first();
        $this->assertSame('Surat', $visitor->city);
        $this->assertSame('Gujarat', $visitor->region);
        $this->assertSame('India', $visitor->country);
    }

    public function test_a_local_request_uses_the_public_network_location(): void
    {
        $this->app['env'] = 'local';

        Http::fake([
            'https://ipwho.is/' => Http::response([
                'success' => true,
                'city' => 'Ahmedabad',
                'region' => 'Gujarat',
                'country' => 'India',
            ]),
        ]);

        $this->withoutMiddleware()->postJson(route('analytics.visit'), [
            'path' => '/',
            'title' => 'Home',
        ])->assertOk();

        $visitor = AnalyticsVisitor::query()->first();
        $this->assertSame('Ahmedabad', $visitor->city);
        $this->assertSame('Gujarat', $visitor->region);
        $this->assertSame('India', $visitor->country);
    }

    public function test_a_shared_pin_is_saved_as_the_visitor_location(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'house_number' => '12',
                    'road' => 'Satellite Road',
                    'suburb' => 'Satellite',
                    'city' => 'Ahmedabad',
                    'state' => 'Gujarat',
                    'postcode' => '380015',
                    'country' => 'India',
                ],
            ]),
        ]);

        $uuid = (string) Str::uuid();

        $this->postJson(route('analytics.location'), [
            'visitor' => $uuid,
            'latitude' => 23.022505,
            'longitude' => 72.571362,
            'accuracy' => 18,
        ])->assertOk()->assertJson(['located' => true, 'visitor' => $uuid]);

        $visitor = AnalyticsVisitor::query()->where('uuid', $uuid)->first();
        $this->assertSame('12 Satellite Road, Satellite, Ahmedabad, Gujarat, 380015, India', $visitor->place());
        $this->assertSame(18, $visitor->location_accuracy);
        $this->assertStringContainsString('23.022505', (string) $visitor->mapUrl());
    }

    public function test_admin_sees_a_simple_visitor_list(): void
    {
        $visitor = AnalyticsVisitor::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Meera Shah',
            'mobile' => '9123456780',
            'city' => 'Surat',
            'region' => 'Gujarat',
            'country' => 'India',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'first_landing_path' => '/',
            'first_referrer_domain' => 'instagram.com',
            'exit_path' => '/pricing',
        ]);
        $visitor->pageviews()->create([
            'path' => '/',
            'page_title' => 'Home',
            'viewed_at' => now()->subMinute(),
        ]);
        $visitor->pageviews()->create([
            'path' => '/pricing',
            'page_title' => 'Pricing',
            'referrer_path' => '/',
            'viewed_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);

        $this->get(route('admin.analytics.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($admin)->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Meera Shah')
            ->assertSee('91234 56780')
            ->assertSee('Surat, Gujarat, India')
            ->assertSee('instagram.com')
            ->assertDontSee('Campaigns')
            ->assertDontSee('Conversion rate');

        $this->actingAs($admin)->get(route('admin.analytics.visitors.show', $visitor))
            ->assertOk()
            ->assertSee('Last page')
            ->assertSee('/pricing')
            ->assertSee('Home');
    }

    public function test_identifying_a_visitor_logs_them_in_and_lists_them_for_admin(): void
    {
        $this->postJson(route('analytics.identify'), [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
            'path' => '/',
        ])->assertOk()->assertJsonPath('account.name', 'Meera Shah')
            ->assertCookie(Auth::guard('customer')->getRecallerName());

        $customer = Customer::query()->where('mobile', '9876543210')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Meera Shah', $customer->name);
        $this->assertAuthenticatedAs($customer, 'customer');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Meera Shah')
            ->assertSee('Log out');

        $admin = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Meera Shah')
            ->assertSee('98765 43210');
    }

    public function test_logout_stays_logged_out_until_they_sign_in_again(): void
    {
        $identified = $this->postJson(route('analytics.identify'), [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
            'path' => '/',
        ])->assertOk();

        $this->post(route('account.logout'))->assertRedirect()->assertSessionHas('logged_out');
        $this->assertGuest('customer');

        $stale = $this->postJson(route('analytics.visit'), [
            'visitor' => $identified->json('visitor'),
            'path' => '/about',
            'title' => 'About',
        ])->assertOk()->assertJsonPath('account', null);
        $this->assertNotSame($identified->json('visitor'), $stale->json('visitor'));
        $this->assertDatabaseMissing('analytics_pageviews', [
            'visitor_id' => AnalyticsVisitor::query()->where('uuid', $identified->json('visitor'))->value('id'),
            'path' => '/about',
        ]);

        $this->assertGuest('customer');
        $this->get(route('account.show'))->assertRedirect(route('home'));

        $this->withCookie(VisitorCapture::COOKIE, $identified->json('visitor'))
            ->post(route('account.login'))
            ->assertRedirect(route('account.show'));
        $this->assertAuthenticated('customer');

        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Meera Shah')
            ->assertSee('98765 43210')
            ->assertSee('Log out');

        $this->get(route('account.logout'))->assertRedirect();
        $this->assertGuest('customer');

        $visitor = AnalyticsVisitor::query()->where('uuid', $identified->json('visitor'))->firstOrFail();
        $this->assertSame(
            ['login', 'logout', 'login', 'logout'],
            AnalyticsAccountEvent::query()->orderBy('id')->pluck('action')->all(),
        );

        $admin = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.analytics.visitors.show', $visitor))
            ->assertOk()
            ->assertDontSee('Login &amp; logout', false)
            ->assertSeeInOrder(['Activity', 'Logged out', 'Logged in'])
            ->assertSee('is-account is-login', false)
            ->assertSee('is-account is-logout', false);
    }

    public function test_logout_is_tracked_from_the_visitor_cookie_after_the_session_ended(): void
    {
        $identified = $this->postJson(route('analytics.identify'), [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
            'path' => '/',
        ])->assertOk();

        Auth::guard('customer')->logout();
        $this->flushSession();

        $this->withCookie(VisitorCapture::COOKIE, $identified->json('visitor'))
            ->post(route('account.logout'))
            ->assertRedirect()
            ->assertCookieExpired(VisitorCapture::COOKIE);

        $event = AnalyticsAccountEvent::query()->where('action', 'logout')->firstOrFail();
        $this->assertSame(Customer::query()->where('mobile', '9876543210')->value('id'), $event->customer_id);

        $after = $this->postJson(route('analytics.visit'), [
            'path' => '/services',
        ])->assertOk()
            ->assertJsonPath('account', null)
            ->assertJsonPath('identified', false)
            ->assertJsonPath('signed_out', true);
        $this->assertNotSame($identified->json('visitor'), $after->json('visitor'));

        $meera = AnalyticsVisitor::query()->where('uuid', $identified->json('visitor'))->firstOrFail();
        $this->assertDatabaseMissing('analytics_pageviews', ['visitor_id' => $meera->id, 'path' => '/services']);
        $this->assertDatabaseHas('analytics_pageviews', ['path' => '/services']);

        $admin = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSeeInOrder(['1', 'opened the site today']);
    }

    public function test_signing_in_again_connects_anonymous_pages_to_that_person(): void
    {
        $identified = $this->postJson(route('analytics.identify'), [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
            'path' => '/',
        ])->assertOk();
        $meera = AnalyticsVisitor::query()->where('uuid', $identified->json('visitor'))->firstOrFail();

        $this->withCookie(VisitorCapture::COOKIE, $identified->json('visitor'))
            ->post(route('account.logout'))
            ->assertRedirect();

        $anonymous = $this->postJson(route('analytics.visit'), ['path' => '/pricing'])
            ->assertOk()
            ->assertJsonPath('account', null);
        $this->assertNotSame($meera->uuid, $anonymous->json('visitor'));

        $this->postJson(route('analytics.identify'), [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
            'visitor' => $anonymous->json('visitor'),
            'path' => '/pricing',
        ])->assertOk()
            ->assertJsonPath('visitor', $meera->uuid)
            ->assertJsonPath('account.name', 'Meera Shah');

        $this->assertAuthenticated('customer');
        $this->assertDatabaseMissing('analytics_visitors', ['uuid' => $anonymous->json('visitor')]);
        $this->assertDatabaseHas('analytics_pageviews', ['visitor_id' => $meera->id, 'path' => '/pricing']);
        $this->assertSame(
            ['login', 'logout', 'login'],
            AnalyticsAccountEvent::query()->orderBy('id')->pluck('action')->all(),
        );
        $this->assertSame(3, AnalyticsAccountEvent::query()->where('analytics_visitor_id', $meera->id)->count());
    }

    public function test_unnamed_visitors_are_numbered(): void
    {
        $name = fn (int $id) => (new AnalyticsVisitor)->forceFill(['id' => $id])->label();

        $this->assertSame('Visitor #0001', $name(1));
        $this->assertSame('Visitor #0042', $name(42));
        $this->assertSame('Visitor #12345', $name(12345));
        $this->assertSame(42, AnalyticsVisitor::idFromNumberedName('Visitor #0042'));
        $this->assertSame(42, AnalyticsVisitor::idFromNumberedName('visitor 42'));
        $this->assertSame(42, AnalyticsVisitor::idFromNumberedName('#42'));
        $this->assertNull(AnalyticsVisitor::idFromNumberedName('9876543210'));
        $this->assertNull(AnalyticsVisitor::idFromNumberedName('Visitor #0000'));

        $this->postJson(route('analytics.visit'), ['path' => '/'])->assertOk();
        $this->postJson(route('analytics.visit'), ['path' => '/about'])->assertOk();
        [$first, $second] = AnalyticsVisitor::query()->orderBy('id')->get()->all();
        $admin = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.analytics.index', ['q' => $first->label()]))
            ->assertOk()
            ->assertSee($first->label())
            ->assertDontSee($second->label());
    }

    public function test_invalid_mobile_is_rejected(): void
    {
        $this->postJson(route('analytics.identify'), [
            'name' => 'A',
            'mobile' => '12345',
        ])->assertStatus(422)->assertJsonValidationErrors('mobile');
    }
}
