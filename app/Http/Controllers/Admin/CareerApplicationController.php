<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\CareerApplicationStatusHistory;
use App\Models\CareerJob;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareerApplicationController extends Controller
{
    private function filtered(Request $request)
    {
        return CareerApplication::query()->with(['job.department'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q').'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search));
            })
            ->when($request->filled('job'), fn ($query) => $query->where('career_job_id', $request->integer('job')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('employment_type'), fn ($query) => $query->whereHas('job', fn ($job) => $job->where('employment_type', $request->string('employment_type'))))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')));
    }

    public function index(Request $request): View
    {
        return view('admin.recruitment.applications.index', [
            'applications' => $this->filtered($request)->latest()->paginate(25)->withQueryString(),
            'jobs' => CareerJob::withTrashed()->orderBy('title')->get(),
            'counts' => CareerApplication::query()->selectRaw('status, count(*) total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(CareerApplication $application): View
    {
        $application->load(['job.department', 'statusHistory.user']);
        return view('admin.recruitment.applications.show', compact('application'));
    }

    public function update(Request $request, CareerApplication $application): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(CareerApplication::STATUSES))], 'internal_notes' => ['nullable', 'string', 'max:10000'], 'status_note' => ['nullable', 'string', 'max:2000']]);
        $old = $application->status;
        $application->update(['status' => $data['status'], 'internal_notes' => $data['internal_notes'] ?? null]);
        if ($old !== $data['status'] || filled($data['status_note'] ?? null)) {
            CareerApplicationStatusHistory::query()->create(['career_application_id' => $application->id, 'changed_by' => $request->user()->id, 'from_status' => $old, 'to_status' => $data['status'], 'note' => $data['status_note'] ?? null]);
        }
        ActivityLogger::log('update', 'recruitment', $application, 'Application reviewed');
        return back()->with('success', 'Application updated.');
    }

    public function resume(CareerApplication $application): BinaryFileResponse
    {
        abort_unless(Storage::disk($application->resume_disk)->exists($application->resume_path), 404);
        return response()->download(Storage::disk($application->resume_disk)->path($application->resume_path), $application->resume_original_name, ['Content-Type' => $application->resume_mime, 'X-Content-Type-Options' => 'nosniff']);
    }

    public function export(Request $request): StreamedResponse
    {
        $name = 'career-applications-'.now()->format('Y-m-d-His').'.csv';
        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['Applied at', 'Job', 'Employment type', 'Status', 'Name', 'Email', 'Phone', 'Location', 'Experience', 'Skills', 'Availability', 'Portfolio', 'GitHub', 'LinkedIn']);
            $this->filtered($request)->latest()->chunkById(200, function ($rows) use ($out) {
                foreach ($rows as $row) fputcsv($out, array_map([$this, 'csvSafe'], [$row->created_at?->toDateTimeString(), $row->job?->title ?? 'General profile: '.$row->area_of_interest, CareerJob::EMPLOYMENT_TYPES[$row->job?->employment_type] ?? '', CareerApplication::STATUSES[$row->status] ?? $row->status, $row->name, $row->email, $row->phone, $row->current_location, $row->experience, $row->skills, $row->availability, $row->portfolio_url, $row->github_url, $row->linkedin_url]));
            });
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function csvSafe(mixed $value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    public function destroy(CareerApplication $application): RedirectResponse
    {
        Storage::disk($application->resume_disk)->delete($application->resume_path);
        ActivityLogger::log('delete', 'recruitment', $application, 'Application and resume permanently deleted');
        $application->delete();
        return redirect()->route('admin.recruitment.applications.index')->with('success', 'Application and resume deleted.');
    }
}
