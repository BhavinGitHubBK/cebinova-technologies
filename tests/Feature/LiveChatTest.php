<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LiveChat;
use App\Models\User;
use App\Support\LiveChatScript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LiveChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_stores_name_and_mobile_then_a_question(): void
    {
        $start = $this->postJson('/chat', [
            'name' => 'Meera Shah',
            'mobile' => '9876543210',
        ])->assertOk();

        $chat = LiveChat::query()->first();
        $this->assertNotNull($chat);
        $this->assertSame('Meera Shah', $chat->name);
        $this->assertSame('9876543210', $chat->mobile);
        $start->assertJsonPath('reply', 'Hello Meera! What would you like to know?')
            ->assertJsonPath('name', 'Meera');

        $this->postJson('/chat', [
            'name' => 'Meera Shah',
            'mobile' => '98765 43210',
            'chat_id' => $start->json('id'),
            'token' => $start->json('token'),
            'question' => 'website',
        ])->assertOk()
            ->assertJsonPath('reply', 'A business website starts at ₹14,999. Share what you need and the team will suggest the right plan.')
            ->assertJsonPath('link.label', 'See website pricing')
            ->assertJsonPath('link.url', route('pricing'));

        $start->assertJsonPath('link', null);
        $this->assertSame(1, LiveChat::query()->count());

        $this->actingAs(User::factory()->create())
            ->get('/admin/live-chats')
            ->assertOk()
            ->assertSee('Meera Shah')
            ->assertSee('98765 43210')
            ->assertSee('What does a website cost?')
            ->assertSee('Last active');

        $this->get('/admin/live-chats/'.$chat->id)
            ->assertOk()
            ->assertSee('What they asked')
            ->assertSee('What does a website cost?')
            ->assertSee('A business website starts at ₹14,999. Share what you need and the team will suggest the right plan.')
            ->assertSee('https://wa.me/919876543210', false)
            ->assertDontSee('What would you like to know');
    }

    public function test_sharing_name_and_mobile_in_chat_signs_the_visitor_in(): void
    {
        $this->postJson('/chat', [
            'name' => 'bhavin khadela',
            'mobile' => '9624688531',
        ])->assertOk()
            ->assertJsonPath('reply', 'Hello Bhavin! What would you like to know?')
            ->assertJsonPath('account.name', 'bhavin khadela')
            ->assertCookie('cn_visitor');

        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertSame('9624688531', Auth::guard('customer')->user()->mobile);
    }

    public function test_signed_in_visitor_is_greeted_by_name_and_not_asked_again(): void
    {
        $customer = Customer::query()->create(['name' => 'bhavin khadela', 'mobile' => '9624688531']);

        $this->actingAs($customer, 'customer')
            ->get('/')
            ->assertOk()
            ->assertSee('data-chat-name="Bhavin"', false);

        $this->postJson('/chat', ['question' => 'website'])
            ->assertOk()
            ->assertJsonPath('name', 'Bhavin')
            ->assertJsonPath('reply', 'A business website starts at ₹14,999. Share what you need and the team will suggest the right plan.');

        $chat = LiveChat::query()->sole();
        $this->assertSame('bhavin khadela', $chat->name);
        $this->assertSame('9624688531', $chat->mobile);
        $this->assertSame('What does a website cost?', $chat->question);
        $this->assertSame(1, count($chat->exchanges()));
    }

    public function test_signed_out_visitor_is_asked_for_name_again(): void
    {
        $customer = Customer::query()->create(['name' => 'Bhavin', 'mobile' => '9624688531']);

        $this->actingAs($customer, 'customer')
            ->withSession(['customer_signed_out' => true])
            ->get('/')
            ->assertOk()
            ->assertSee('data-chat-name=""', false);
    }

    public function test_a_new_conversation_starts_after_a_quiet_half_hour(): void
    {
        $customer = Customer::query()->create(['name' => 'Bhavin', 'mobile' => '9624688531']);
        $this->actingAs($customer, 'customer');

        $first = $this->postJson('/chat', ['question' => 'website'])->assertOk();
        $this->postJson('/chat', [
            'chat_id' => $first->json('id'),
            'token' => $first->json('token'),
            'question' => 'website',
        ])->assertOk()->assertJsonPath('id', $first->json('id'));

        $this->travel(LiveChat::IDLE_MINUTES + 1)->minutes();

        $later = $this->postJson('/chat', [
            'chat_id' => $first->json('id'),
            'token' => $first->json('token'),
            'question' => 'website',
        ])->assertOk();

        $this->assertNotSame($first->json('id'), $later->json('id'));
        $this->assertSame(2, LiveChat::query()->count());
    }

    public function test_every_question_links_to_a_working_page(): void
    {
        $customer = Customer::query()->create(['name' => 'Bhavin', 'mobile' => '9624688531']);
        $this->actingAs($customer, 'customer');

        foreach (LiveChatScript::questions() as $key => $question) {
            $link = $this->postJson('/chat', ['question' => $key])->assertOk()->json('link');
            $this->assertSame($question['link'], $link);

            if (str_starts_with($link['url'], url('/'))) {
                $this->get($link['url'])->assertOk();
            } else {
                $this->assertStringStartsWith('https://wa.me/', $link['url']);
            }
        }
    }

    public function test_chat_rejects_a_short_mobile(): void
    {
        $this->postJson('/chat', [
            'name' => 'Meera Shah',
            'mobile' => '12345',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('mobile');

        $this->assertSame(0, LiveChat::query()->count());
    }
}
