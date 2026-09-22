<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lead::query()->with('assignee')->withTrashed();

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        foreach (['status', 'service', 'package_category', 'source'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        if ($request->get('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->get('trashed') !== 'with') {
            $query->withoutTrashed();
        }

        $sort = in_array($request->get('sort'), ['created_at', 'name', 'status', 'follow_up_at'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $leads = $query->orderBy($sort, $dir)->paginate(20)->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'admins' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'service', 'package_category', 'source', 'from', 'to', 'trashed', 'sort', 'dir']),
            'newCount' => Lead::query()->where('status', Lead::STATUS_NEW)->count(),
        ]);
    }

    public function show(Lead $lead): View
    {
        $lead->load('assignee');

        return view('admin.leads.show', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
            'admins' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Lead::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $before = $lead->only(['status', 'notes', 'follow_up_at', 'assigned_to']);
        $lead->update($data);

        ActivityLogger::log('update', 'leads', $lead, 'Lead updated', [
            'before' => $before,
            'after' => $lead->only(['status', 'notes', 'follow_up_at', 'assigned_to']),
        ]);

        return back()->with('success', 'Lead updated.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:leads,id'],
            'status' => ['required', 'in:'.implode(',', Lead::STATUSES)],
        ]);

        Lead::query()->whereIn('id', $data['ids'])->update(['status' => $data['status']]);
        ActivityLogger::log('bulk_status', 'leads', null, 'Bulk status update', $data);

        return back()->with('success', 'Selected leads updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();
        ActivityLogger::log('delete', 'leads', $lead, 'Lead soft-deleted');

        return redirect()->route('admin.leads.index')->with('success', 'Lead moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $lead->restore();
        ActivityLogger::log('restore', 'leads', $lead, 'Lead restored');

        return back()->with('success', 'Lead restored.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Lead::query()->withoutTrashed();

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        foreach (['status', 'service', 'package_category', 'source'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter));
            }
        }

        $filename = 'leads-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Business', 'Phone', 'Email', 'Service', 'Package', 'Status', 'Source', 'Created']);
            $query->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $lead) {
                    fputcsv($out, [
                        $lead->id,
                        $lead->name,
                        $lead->business_name,
                        $lead->phone,
                        $lead->email,
                        $lead->service,
                        $lead->package_category,
                        $lead->status,
                        $lead->source,
                        $lead->created_at,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
