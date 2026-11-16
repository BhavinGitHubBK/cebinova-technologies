<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCareerApplicationRequest;
use App\Mail\CareerApplicationAcknowledgement;
use App\Mail\CareerApplicationReceived;
use App\Models\CareerApplication;
use App\Models\CareerApplicationStatusHistory;
use App\Models\CareerDepartment;
use App\Models\CareerJob;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = CareerJob::query()->published()->with('department')
            ->when($request->filled('department'), fn ($query) => $query->whereHas('department', fn ($department) => $department->where('slug', $request->string('department'))))
            ->when($request->filled('employment_type'), fn ($query) => $query->where('employment_type', $request->string('employment_type')))
            ->when($request->filled('work_mode'), fn ($query) => $query->where('work_mode', $request->string('work_mode')))
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->paginate(12)->withQueryString();

        return view('careers.index', [
            'jobs' => $jobs,
            'departments' => CareerDepartment::query()->where('is_active', true)->whereHas('jobs', fn ($query) => $query->published())->orderBy('sort_order')->orderBy('name')->get(),
            'settings' => SiteSetting::current(),
        ]);
    }

    public function show(CareerJob $job): View
    {
        abort_unless($job->status === 'published' && $job->published_at?->lte(now()), 404);
        $job->load('department');
        return view('careers.show', compact('job'));
    }

    public function apply(CareerJob $job): View
    {
        abort_unless($job->acceptsApplications(), 404);
        $job->load('department');
        return view('careers.apply', compact('job'));
    }

    public function store(StoreCareerApplicationRequest $request, CareerJob $job): RedirectResponse
    {
        abort_unless($job->acceptsApplications(), 422, 'This position is no longer accepting applications.');
        $file = $request->file('resume');
        $path = $file->storeAs('career-resumes/'.now()->format('Y/m'), Str::uuid().'.'.$file->getClientOriginalExtension(), 'local');
        $data = $request->safe()->except(['resume', 'consent', 'website']);
        $application = $job->applications()->create($data + [
            'resume_disk' => 'local', 'resume_path' => $path, 'resume_original_name' => $file->getClientOriginalName(),
            'resume_mime' => $file->getMimeType() ?: 'application/octet-stream', 'resume_size' => $file->getSize(),
            'status' => 'new', 'consent_at' => now(), 'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);
        CareerApplicationStatusHistory::query()->create(['career_application_id' => $application->id, 'to_status' => 'new', 'note' => 'Application submitted']);

        $settings = SiteSetting::current();
        try {
            $recipient = $settings->recruitment_email ?: $settings->lead_notify_email ?: $settings->email ?: config('cebinova.contact.email');
            if ($recipient) Mail::to($recipient)->queue(new CareerApplicationReceived($application));
            Mail::to($application->email)->queue(new CareerApplicationAcknowledgement($application));
        } catch (\Throwable $exception) {
            Log::error('Career application mail could not be queued.', ['application_id' => $application->id, 'error' => $exception->getMessage()]);
        }

        return redirect()->route('careers.show', $job)->with('career_success', $settings->application_confirmation_text ?: 'Thank you. Your application has been received and our recruitment team will review it.');
    }
}
