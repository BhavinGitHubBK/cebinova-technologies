@php
    $team = $team ?? \App\Support\CmsContent::team();
@endphp

@if (count($team))
    <section class="section-pad" id="about-team">
        <div class="container-wide">
            <x-section-heading :wrap="true" eyebrow="Team" title="People behind CEBINOVA.">
                Practitioners who build websites, software and growth systems for real businesses.
            </x-section-heading>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-stagger>
                @foreach ($team as $member)
                    <article class="card-surface p-6 sm:p-7">
                        <h3 class="text-lg text-navy">{{ $member->name }}</h3>
                        @if ($member->designation)
                            <p class="mt-1 text-[13px] font-semibold uppercase tracking-[0.12em] text-navy/40">{{ $member->designation }}</p>
                        @endif
                        @if ($member->bio)
                            <p class="mt-3 text-[15px] leading-relaxed text-muted">{{ $member->bio }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
