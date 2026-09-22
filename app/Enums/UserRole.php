<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Editor => 'Editor',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canWrite(): bool
    {
        return $this !== self::Viewer;
    }
}
