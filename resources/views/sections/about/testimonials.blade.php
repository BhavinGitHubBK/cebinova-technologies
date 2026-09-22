@php
    $testimonials = $testimonials ?? \App\Support\CmsContent::testimonials();
@endphp

@if (count($testimonials))
    <section class="section-pad section-soft" id="about-testimonials">
        <div class="container-wide">
            <x-section-heading :wrap="true" eyebrow="Testimonials" title="What partners say.">
                Short notes from businesses we have helped move forward.
            </x-section-heading>
            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
                @foreach ($testimonials as $item)
                    <blockquote class="card-surface flex h-full flex-col p-6 sm:p-7">
                        <p class="flex-1 text-[15.5px] leading-relaxed text-muted">“{{ $item->review }}”</p>
                        <footer class="mt-5">
                            <cite class="not-italic font-semibold text-navy">{{ $item->customer_name }}</cite>
                            @if ($item->company || $item->position)
                                <p class="mt-1 text-[13px] text-navy/45">
                                    {{ collect([$item->position, $item->company])->filter()->implode(' · ') }}
                                </p>
                            @endif
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>
@endif
