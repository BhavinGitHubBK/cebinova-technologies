<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect();

        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_authenticated_admin_can_open_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@cebinova.test',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }
}
