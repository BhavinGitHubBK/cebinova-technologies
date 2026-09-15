@php
    $steps = [
        ['label' => 'Customer', 'note' => 'Walk-in, call or message'],
        ['label' => 'Website / WhatsApp', 'note' => 'First digital touch'],
        ['label' => 'Lead / Order', 'note' => 'Captured in one place'],
        ['label' => 'CRM / Software', 'note' => 'Team follows through'],
        ['label' => 'Automation', 'note' => 'Reminders and routing'],
        ['label' => 'Reports / Growth', 'note' => 'What to do next'],
    ];
    $signals = [
        'New Lead',
        'Order Received',
        'Customer Follow-up',
        'Automation Completed',
    ];
@endphp

<section class="relative overflow-hidden bg-navy section-pad-lg">
    <div class="pointer-events-none absolute inset-0 bg-dots-light"></div>
    <div class="container-wide relative">
        <div class="max-w-3xl" data-reveal>
            <x-section-heading light eyebrow="Technology in Action" title="See How SARVIX Connects Your Business.">
                An illustrative workflow - not live client data - showing how a customer request can move from first contact to follow-up and growth.
            </x-section-heading>
        </div>

        <ol class="relative mt-12 grid gap-3 lg:grid-cols-6" data-stagger>
            @foreach ($steps as $index => $step)
                <li class="relative rounded-2xl border border-white/12 bg-white/[0.05] p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-gold">0{{ $index + 1 }}</p>
                    <h3 class="mt-3 text-[16px] font-extrabold text-white">{{ $step['label'] }}</h3>
                    <p class="mt-2 text-[13.5px] leading-relaxed text-white/55">{{ $step['note'] }}</p>
                    @if (! $loop->last)
                        <span class="mt-4 hidden text-gold/70 lg:block" aria-hidden="true">→</span>
                    @endif
                </li>
            @endforeach
        </ol>

        <div class="mt-10">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/35">Illustrative system events</p>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($signals as $index => $signal)
                    <li class="js-signal-card rounded-xl border border-white/10 bg-white/[0.06] px-4 py-3 text-[14px] font-semibold text-white/80" style="animation-delay: {{ $index * 0.55 }}s">
                        <span class="mr-2 inline-block h-1.5 w-1.5 rounded-full bg-gold"></span>{{ $signal }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
