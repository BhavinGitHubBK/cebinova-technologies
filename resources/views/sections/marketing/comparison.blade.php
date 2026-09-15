@php
    $matrix = config('sarvix.marketing.matrix.rows', []);
    $growth = config('sarvix.marketing.growth');
@endphp
<section class="section-pad bg-white" id="choose-plan">
    <div class="container-wide">
        <x-section-heading eyebrow="Start here" title="Which plan is for you?">
            Regular is everyday posts. Festival is occasions only. Complete Growth is both - and only ₹1,000 more than Regular each month.
        </x-section-heading>

        <div class="mt-10 overflow-x-auto rounded-[1.2rem] border border-line">
            <table class="pack-matrix min-w-[640px] w-full text-left">
                <thead>
                    <tr class="border-b border-line bg-mist">
                        <th class="px-4 py-4 text-[13px] font-bold uppercase tracking-[0.12em] text-navy/45 sm:px-6">What you get</th>
                        <th class="px-4 py-4 text-[15px] font-extrabold text-navy sm:px-6">Regular</th>
                        <th class="px-4 py-4 text-[15px] font-extrabold text-navy sm:px-6">Festival</th>
                        <th class="px-4 py-4 text-[15px] font-extrabold text-navy sm:px-6">
                            Complete Growth
                            <span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.14em] text-gold-dark">Recommended</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matrix as $row)
                        <tr class="border-b border-line last:border-b-0">
                            <th class="px-4 py-3.5 text-[14.5px] font-semibold text-navy sm:px-6">{{ $row['label'] }}</th>
                            <td class="px-4 py-3.5 text-[14.5px] text-navy/75 sm:px-6">{{ $row['regular'] }}</td>
                            <td class="px-4 py-3.5 text-[14.5px] text-navy/75 sm:px-6">{{ $row['festival'] }}</td>
                            <td class="px-4 py-3.5 text-[14.5px] font-semibold text-navy sm:px-6">{{ $row['growth'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            @foreach (config('sarvix.marketing.comparison') as $item)
                @php $recommended = ! empty($item['recommended']); @endphp
                <a href="{{ $item['key'] === 'growth' ? '#complete-growth' : '#marketing-plans' }}" class="js-pack-jump lift-card rounded-xl border px-5 py-4 {{ $recommended ? 'border-gold/45 bg-navy text-white' : 'border-line bg-white' }}" data-cat="{{ $item['key'] }}">
                    <p class="text-[12px] font-bold uppercase tracking-[0.14em] {{ $recommended ? 'text-gold' : 'text-navy/45' }}">{{ $item['title'] }}</p>
                    <p class="mt-1 text-[16px] font-extrabold {{ $recommended ? 'text-white' : 'text-navy' }}">{{ $item['if'] }}</p>
                    <p class="mt-2 text-[14px] {{ $recommended ? 'text-white/70' : 'text-muted' }}">{{ $item['gets'] }} →</p>
                </a>
            @endforeach
        </div>

        <article class="mt-8 rounded-[1.2rem] border border-gold/40 bg-gold/10 px-6 py-6 sm:px-8">
            <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-gold-dark">Why Complete Growth</p>
            <h3 class="mt-2 text-xl font-extrabold text-navy">₹1,000 more than Regular. Festival creatives included.</h3>
            <p class="mt-3 max-w-3xl text-[15.5px] leading-relaxed text-navy/70">{{ $growth['why'] }}</p>
            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                <x-button href="#complete-growth" class="js-pack-jump" data-cat="growth">See Complete Growth</x-button>
                <x-button href="{{ package_whatsapp_url('Complete Growth', 'Yearly') }}" variant="outline">Ask on WhatsApp</x-button>
            </div>
        </article>
    </div>
</section>
