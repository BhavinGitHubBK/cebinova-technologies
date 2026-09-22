<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Package;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $media = Media::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('original_name', 'like', '%'.$request->q.'%')
                    ->orWhere('filename', 'like', '%'.$request->q.'%');
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
            'alt' => ['nullable', 'string', 'max:190'],
        ]);

        $file = $request->file('file');
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/'.now()->format('Y/m'), $filename, 'public');

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $request->input('alt'),
            'uploaded_by' => $request->user()->id,
        ]);

        ActivityLogger::log('create', 'media', $media, 'Media uploaded');

        return back()->with('success', 'File uploaded.')->with('uploaded_url', $media->url());
    }

    public function destroy(Media $medium): RedirectResponse
    {
        $urlPath = $medium->path;
        $inUse = Service::query()->where('featured_image', 'like', '%'.$urlPath.'%')->exists()
            || SiteSetting::query()
                ->where('logo_path', 'like', '%'.$urlPath.'%')
                ->orWhere('favicon_path', 'like', '%'.$urlPath.'%')
                ->orWhere('og_image_path', 'like', '%'.$urlPath.'%')
                ->exists();

        if ($inUse) {
            return back()->withErrors(['file' => 'This media is still referenced and cannot be deleted.']);
        }

        Storage::disk($medium->disk)->delete($medium->path);
        $medium->delete();
        ActivityLogger::log('delete', 'media', $medium, 'Media deleted');

        return back()->with('success', 'Media deleted.');
    }
}
