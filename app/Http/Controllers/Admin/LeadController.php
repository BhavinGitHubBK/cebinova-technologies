<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use App\Support\MarketingPackages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
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

        [$sort, $dir] = $this->listingOrder($request);
        $leads = $query->orderBy($sort, $dir)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'admins' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'service', 'package_category', 'source', 'from', 'to', 'trashed', 'sort', 'dir']),
            'sort' => $sort,
            'dir' => $dir,
            'newCount' => Lead::query()->where('status', Lead::STATUS_NEW)->count(),
            'serviceOptions' => $this->filterChoices('service', Schema::hasTable('services') ? Service::query()->orderBy('name')->pluck('name') : collect()),
            'packageOptions' => $this->filterChoices('package_category', collect(array_keys(MarketingPackages::categoryKeys()))),
            'sourceOptions' => $this->filterChoices('source', collect(config('cebinova.leads.sources', []))),
        ]);
    }

    private function listingOrder(Request $request): array
    {
        $columns = ['name', 'phone', 'service', 'status', 'source', 'created_at'];
        $sort = in_array($request->get('sort'), $columns, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        return [$sort, $dir];
    }

    private function filterChoices(string $column, $known)
    {
        $used = Lead::query()->whereNotNull($column)->where($column, '!=', '')->distinct()->orderBy($column)->pluck($column);

        return $known->merge($used)->map(fn ($value) => trim((string) $value))->filter()->unique()->sort()->values();
    }

    public function show(Lead $lead): View
    {
        $lead->load(['assignee', 'tasks']);

        return view('admin.leads.show', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
            'admins' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Lead::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $before = $lead->only(['status', 'notes', 'follow_up_at', 'assigned_to']);
        $sameFollowUp = blank($before['follow_up_at']) && blank($data['follow_up_at'] ?? null)
            || filled($before['follow_up_at']) && filled($data['follow_up_at'] ?? null)
            && $before['follow_up_at']->format('Y-m-d H:i') === Carbon::parse($data['follow_up_at'])->format('Y-m-d H:i');
        $unchanged = $before['status'] === $data['status']
            && (string) ($before['notes'] ?? '') === (string) ($data['notes'] ?? '')
            && (string) ($before['assigned_to'] ?? '') === (string) ($data['assigned_to'] ?? '')
            && $sameFollowUp;

        if ($unchanged) {
            $message = 'This lead is already '.$data['status'].'.';
            if ($request->expectsJson() || $request->headers->has('X-Admin-Ajax')) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

        $lead->update($data);

        ActivityLogger::log('update', 'leads', $lead, 'Lead updated', [
            'before' => $before,
            'after' => $lead->only(['status', 'notes', 'follow_up_at', 'assigned_to']),
        ]);

        return back()->with('success', 'Lead updated.');
    }

    public function bulkStatus(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:leads,id'],
            'status' => ['required', 'in:'.implode(',', Lead::STATUSES)],
        ]);

        $leads = Lead::query()->whereIn('id', $data['ids'])->get();
        $pending = $leads->reject(fn (Lead $lead) => $lead->status === $data['status'])->values();
        $already = $leads->count() - $pending->count();

        if ($pending->isEmpty()) {
            $message = 'Selected leads are already '.$data['status'].'.';
            if ($request->expectsJson() || $request->headers->has('X-Admin-Ajax')) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['status' => $message]);
        }

        Lead::query()->whereIn('id', $pending->pluck('id'))->update(['status' => $data['status']]);
        ActivityLogger::log('bulk_status', 'leads', null, 'Bulk status update', [
            'ids' => $pending->pluck('id')->all(),
            'status' => $data['status'],
        ]);

        $message = $pending->count().' '.($pending->count() === 1 ? 'lead' : 'leads').' updated to '.$data['status'].'.';
        if ($already > 0) {
            $message .= ' '.$already.' '.($already === 1 ? 'was' : 'were').' already '.$data['status'].'.';
        }

        if ($request->expectsJson() || $request->headers->has('X-Admin-Ajax')) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'status' => $data['status'],
                'ids' => $pending->pluck('id')->values(),
            ]);
        }

        return back()->with('success', $message);
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

        [$sort, $dir] = $this->listingOrder($request);
        $filename = 'leads-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $sort, $dir) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Business', 'Phone', 'Email', 'Service', 'Package', 'Status', 'Source', 'Created']);
            $query->orderBy($sort, $dir)->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
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
