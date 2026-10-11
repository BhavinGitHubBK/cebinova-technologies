<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:password {email}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error("No admin user found with email {$email}.");
        $this->line('Existing admin emails: '.User::query()->pluck('email')->implode(', '));

        return 1;
    }

    $password = (string) $this->secret('New password (min 8 characters)');

    if (strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');

        return 1;
    }

    if ($password !== (string) $this->secret('Repeat new password')) {
        $this->error('Passwords do not match.');

        return 1;
    }

    $user->forceFill(['password' => Hash::make($password), 'is_active' => true])->save();
    $this->info("Password updated for {$email}. You can sign in now.");

    return 0;
})->purpose('Reset an admin user password');
