<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = TeamMember::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.team-members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.team-members.form', ['member' => new TeamMember]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $member = TeamMember::query()->create($data);
        ActivityLogger::log('create', 'team-members', $member, 'Team member created');

        return redirect()->route('admin.team-members.index')->with('success', 'Team member created.');
    }

    public function show(TeamMember $teamMember): View
    {
        return view('admin.team-members.show', ['member' => $teamMember]);
    }

    public function edit(TeamMember $teamMember): View
    {
        return view('admin.team-members.form', ['member' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $teamMember->update($this->validated($request));
        ActivityLogger::log('update', 'team-members', $teamMember, 'Team member updated');

        return redirect()->route('admin.team-members.index')->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->delete();
        ActivityLogger::log('delete', 'team-members', $teamMember, 'Team member deleted');

        return redirect()->route('admin.team-members.index')->with('success', 'Team member deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'designation' => ['nullable', 'string', 'max:190'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:60'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
