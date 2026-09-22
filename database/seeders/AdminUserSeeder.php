<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@cebinova.test');
        $name = env('ADMIN_NAME', 'Cebinova Admin');
        $password = env('ADMIN_PASSWORD');
        $generated = false;

        if (! filled($password)) {
            $password = Str::password(16);
            $generated = true;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
            ]
        );

        if ($generated) {
            $this->command?->warn("ADMIN_PASSWORD was empty. Generated once: {$password}");
            $this->command?->warn('Store this password securely. It will not be shown again.');
        } else {
            $this->command?->info("Admin user ready: {$email}");
        }
    }
}
