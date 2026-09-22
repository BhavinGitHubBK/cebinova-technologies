<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('create', 'users', $user, 'User created');

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $newRole = $data['role'] instanceof UserRole ? $data['role'] : UserRole::from($data['role']);

        if ($user->isSuperAdmin() && $newRole !== UserRole::SuperAdmin && User::query()->where('role', UserRole::SuperAdmin)->count() <= 1) {
            return back()->withErrors(['role' => 'Cannot demote the last Super Admin.']);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $newRole,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('update', 'users', $user, 'User updated');

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin() && User::query()->where('role', UserRole::SuperAdmin)->count() <= 1) {
            return back()->withErrors(['user' => 'Cannot delete the last Super Admin.']);
        }

        $user->delete();
        ActivityLogger::log('delete', 'users', $user, 'User deleted');

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $password = Str::password(16);
        $user->update(['password' => Hash::make($password)]);
        ActivityLogger::log('password_reset', 'users', $user, 'Admin reset user password');

        return back()->with('success', "Temporary password for {$user->email}: {$password}");
    }
}
