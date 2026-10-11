<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resets_an_admin_password(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'is_active' => false]);

        $this->artisan('admin:password', ['email' => 'admin@example.com'])
            ->expectsQuestion('New password (min 8 characters)', 'new-secret-123')
            ->expectsQuestion('Repeat new password', 'new-secret-123')
            ->assertSuccessful();

        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-123', $user->password));
        $this->assertTrue((bool) $user->is_active);
    }

    public function test_it_rejects_unknown_email_and_mismatched_passwords(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $original = $user->password;

        $this->artisan('admin:password', ['email' => 'missing@example.com'])->assertFailed();

        $this->artisan('admin:password', ['email' => 'admin@example.com'])
            ->expectsQuestion('New password (min 8 characters)', 'new-secret-123')
            ->expectsQuestion('Repeat new password', 'different-123')
            ->assertFailed();

        $this->assertSame($original, $user->refresh()->password);
    }
}
