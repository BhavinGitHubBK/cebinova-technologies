<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\Admin\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public const PAGES = [
        'general', 'home', 'about', 'contact', 'services', 'solutions', 'portfolio', 'demos', 'marketing',
    ];

    public function index(Request $request): View
    {
        $faqs = Faq::query()
            ->when($request->filled('page'), fn ($q) => $q->where('page', $request->page))
            ->when($request->filled('q'), fn ($q) => $q->where('question', 'like', '%'.$request->q.'%'))
            ->orderBy('page')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.faqs.index', [
            'faqs' => $faqs,
            'pages' => self::PAGES,
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', [
            'faq' => new Faq(['page' => 'general']),
            'pages' => self::PAGES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $faq = Faq::query()->create($data);
        ActivityLogger::log('create', 'faqs', $faq, 'FAQ created');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created.');
    }

    public function show(Faq $faq): View
    {
        return view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', [
            'faq' => $faq,
            'pages' => self::PAGES,
        ]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));
        ActivityLogger::log('update', 'faqs', $faq, 'FAQ updated');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        ActivityLogger::log('delete', 'faqs', $faq, 'FAQ deleted');

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'page' => ['required', 'in:'.implode(',', self::PAGES)],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
