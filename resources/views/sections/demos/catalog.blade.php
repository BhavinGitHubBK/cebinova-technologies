<section class="demo-page-catalog section-pad-lg" aria-label="Live demos catalog">
<div class="container-wide">
<div class="demo-page-head" data-reveal><p class="demo-page-kicker">Live demos</p><h2 class="section-title">Business solutions built for real businesses.</h2><p class="section-support">These are live solution demos and concepts. They are not presented as live client case studies.</p></div>
@if($demos->isNotEmpty())<div class="demo-page-filters" data-filter-group="[data-demo-card]" aria-label="Demo categories"><button type="button" class="demo-page-chip is-active" data-filter="all">All</button>@foreach($demos as $demo)<button type="button" class="demo-page-chip" data-filter="{{ $demo->slug }}">{{ $demo->name }}</button>@endforeach</div>@endif
<div class="demo-page-grid" data-stagger>
@forelse($demos as $demo)
<article id="{{ $demo->slug }}" data-demo-card data-category="{{ $demo->slug }}" class="demo-page-card scroll-mt-28{{ $demo->is_featured ? ' is-featured' : '' }}" style="overflow-wrap:anywhere">
<div class="demo-page-media">
@if($demo->thumbnail)<img src="{{ asset($demo->thumbnail) }}" alt="{{ $demo->name }} demo preview" class="demo-page-image" loading="lazy">@else<div class="demo-page-placeholder"><x-demo-preview :type="$demo->slug === 'business-management' ? 'crm' : 'ai'" /></div>@endif
<span class="demo-page-flag{{ $demo->badge === 'LIVE DEMO' ? ' is-live' : ($demo->badge === 'SOLUTION DEMO' ? ' is-demo' : ' is-concept') }}">{{ $demo->badge }}</span></div>
<div class="demo-page-body"><div class="demo-page-card-top"><span class="demo-page-icon"><x-mark name="layers" class="h-5 w-5" /></span><p class="demo-page-industry">{{ $demo->industries->pluck('name')->implode(', ') }}</p></div>
<h3 class="demo-page-card-title">CEBINOVA {{ $demo->name }} Solution</h3><p class="demo-page-card-text">{{ $demo->short_description }}</p>
<ul class="demo-page-tags">@foreach($demo->features ?? [] as $feature)@if($feature['is_active'] ?? false)<li title="{{ $feature['description'] ?? '' }}">{{ $feature['name'] }}</li>@endif @endforeach</ul>
@if($demo->description || $demo->technologies || $demo->screenshots)<details class="my-4"><summary>Explore this demonstration</summary><p class="my-3 whitespace-pre-line">{{ $demo->description }}</p>@if($demo->technologies)<p class="my-3">Technologies: {{ implode(', ', $demo->technologies) }}</p>@endif
@foreach($demo->screenshots ?? [] as $shot)<figure class="my-4"><img src="{{ asset($shot['image']) }}" alt="{{ $shot['alt_text'] }}" loading="lazy" class="w-full rounded-lg">@if($shot['caption'])<figcaption>{{ $shot['caption'] }}</figcaption>@endif</figure>@endforeach</details>@endif
<div class="demo-page-actions">@if($demo->liveLink())<x-button href="{{ $demo->liveLink() }}" size="sm" target="_blank" rel="noopener noreferrer">View Demo</x-button>@endif
@foreach($demo->solutions as $solution)<x-button href="{{ solution_page_url(['slug' => $solution->slug]) }}" variant="outline" size="sm">View solution</x-button>@endforeach
<x-button href="{{ $demo->enquiryUrl() }}" variant="outline" size="sm">Build Something Similar</x-button></div></div></article>
@empty<p>No demos are currently available. Please contact us to discuss your requirements.</p>@endforelse
</div></div></section>
