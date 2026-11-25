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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $open = CareerJob::query()->open();
        $employmentTypes = (clone $open)->distinct()->pluck('employment_type')->all();
        $workModes = (clone $open)->distinct()->pluck('work_mode')->all();
        $openCount = (clone $open)->count();
        $jobs = $open->with('department')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('summary', 'like', $term)->orWhere('required_skills', 'like', $term));
            })
            ->when($request->filled('department'), fn ($q) => $q->whereHas('department', fn ($d) => $d->where('slug', $request->string('department'))))
            ->when($request->filled('employment_type'), fn ($q) => $q->where('employment_type', $request->string('employment_type')))
            ->when($request->filled('work_mode'), fn ($q) => $q->where('work_mode', $request->string('work_mode')))
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->paginate(12)->withQueryString();
        $linkedin = config('cebinova.contact.social.linkedin');
        $linkedinHost = strtolower((string) parse_url((string) $linkedin, PHP_URL_HOST));
        $linkedin = parse_url((string) $linkedin, PHP_URL_SCHEME) === 'https' && in_array($linkedinHost, ['linkedin.com', 'www.linkedin.com'], true) ? $linkedin : null;
        return view('careers.index', [
            'jobs' => $jobs, 'openCount' => $openCount, 'employmentTypes' => $employmentTypes, 'workModes' => $workModes,
            'linkedin' => $linkedin,
            'departments' => CareerDepartment::query()->whereHas('jobs', fn ($q) => $q->open())->orderBy('sort_order')->orderBy('name')->get(),
            'settings' => SiteSetting::current(),
        ]);
    }

    public function profile(): View
    {
        return view('careers.apply', ['job' => null]);
    }

    public function storeProfile(StoreCareerApplicationRequest $request): RedirectResponse|JsonResponse
    {
        return $this->saveApplication($request);
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

    public function store(StoreCareerApplicationRequest $request, CareerJob $job): RedirectResponse|JsonResponse
    {
        abort_unless($job->acceptsApplications(), 422, 'This position is no longer accepting applications.');
        return $this->saveApplication($request, $job);
    }

    private function saveApplication(StoreCareerApplicationRequest $request, ?CareerJob $job = null): RedirectResponse|JsonResponse
    {
        $file = $request->file('resume');
        try {
            $path = $file->storeAs('career-resumes/'.now()->format('Y/m'), Str::uuid().'.'.$file->getClientOriginalExtension(), 'local');
        } catch (\Throwable $exception) {
            report($exception);
            $path = false;
        }
        if (! $path) {
            throw \Illuminate\Validation\ValidationException::withMessages(['resume' => 'We could not save your resume. Please try again.']);
        }
        $data = $request->safe()->except(['resume', 'consent', 'website', 'location_permission', 'location_latitude', 'location_longitude', 'location_accuracy']);
        $location = app(\App\Services\ApplicationLocation::class)->capture($request);
        try {
            $application = DB::transaction(function () use ($data, $job, $file, $path, $request, $location) {
                $application = CareerApplication::create($data + $location + [
                    'career_job_id' => $job?->id,
                    'resume_disk' => 'local', 'resume_path' => $path, 'resume_original_name' => $file->getClientOriginalName(),
                    'resume_mime' => $file->getMimeType() ?: 'application/octet-stream', 'resume_size' => $file->getSize(),
                    'status' => 'new', 'consent_at' => now(), 'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                ]);
                CareerApplicationStatusHistory::query()->create(['career_application_id' => $application->id, 'to_status' => 'new', 'note' => 'Application submitted']);

                return $application;
            });
        } catch (\Throwable $exception) {
            try { Storage::disk('local')->delete($path); } catch (\Throwable $cleanupException) { report($cleanupException); }
            report($exception);
            throw \Illuminate\Validation\ValidationException::withMessages(['application' => 'We could not save your application. Please try submitting it again.']);
        }
        $confirmation = null;
        try {
            $settings = SiteSetting::current();
            $confirmation = $settings->application_confirmation_text;
            $recipient = $settings->recruitment_email ?: $settings->lead_notify_email ?: $settings->email ?: config('cebinova.contact.email');
            if ($recipient) Mail::to($recipient)->queue(new CareerApplicationReceived($application));
            Mail::to($application->email)->queue(new CareerApplicationAcknowledgement($application));
        } catch (\Throwable $exception) {
            Log::error('Career application mail could not be queued.', ['application_id' => $application->id, 'error' => $exception->getMessage()]);
        }

        $message = $confirmation ?: ($job
            ? 'Thank you for applying to CEBINOVA! Your application and resume have been received. Our recruitment team will review your profile and contact you if there is a suitable match.'
            : 'Thank you for introducing yourself! Your profile and resume have been received. Our recruitment team will keep them in mind for suitable opportunities.');
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'title' => $job ? 'Application received!' : 'Profile received!', 'message' => $message], 201);
        }
        return ($job ? redirect()->route('careers.show', $job) : redirect()->route('careers.profile'))->with('career_success', $message);
    }
}
