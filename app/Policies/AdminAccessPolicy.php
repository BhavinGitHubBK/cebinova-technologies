<?php

namespace App\Policies;

use App\Models\User;

class AdminAccessPolicy
{
    public function viewDashboard(User $user): bool
    {
        return true;
    }

    public function viewLeads(User $user): bool
    {
        return true;
    }

    public function manageLeads(User $user): bool
    {
        return $user->canWriteContent();
    }

    public function viewServices(User $user): bool
    {
        return true;
    }

    public function manageServices(User $user): bool
    {
        return $user->canWriteContent();
    }

    public function viewPackages(User $user): bool
    {
        return true;
    }

    public function managePackages(User $user): bool
    {
        return $user->canWriteContent();
    }

    public function viewMedia(User $user): bool
    {
        return true;
    }

    public function manageMedia(User $user): bool
    {
        return $user->canWriteContent();
    }

    public function viewSettings(User $user): bool
    {
        return true;
    }

    public function manageSettings(User $user): bool
    {
        return $user->canWriteContent();
    }

    public function viewUsers(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function manageUsers(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function viewActivityLogs(User $user): bool
    {
        return true;
    }

    public function viewContent(User $user): bool
    {
        return true;
    }

    public function manageContent(User $user): bool
    {
        return $user->canWriteContent();
    }
}
