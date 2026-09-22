<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $testimonials = Testimonial::query()
            ->when($request->filled('q'), fn ($q) => $q->where('customer_name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('customer_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['rating' => 5])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $testimonial = Testimonial::query()->create($data);
        ActivityLogger::log('create', 'testimonials', $testimonial, 'Testimonial created');

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created.');
    }

    public function show(Testimonial $testimonial): View
    {
        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request));
        ActivityLogger::log('update', 'testimonials', $testimonial, 'Testimonial updated');

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();
        ActivityLogger::log('delete', 'testimonials', $testimonial, 'Testimonial deleted');

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:190'],
            'company' => ['nullable', 'string', 'max:190'],
            'position' => ['nullable', 'string', 'max:190'],
            'review' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'photo' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['rating'] = (int) ($data['rating'] ?? 5);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
