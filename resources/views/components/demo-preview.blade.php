@props(['type' => 'website'])

@php
    $urls = [
        'store' => 'sarvix.demo/retail',
        'restaurant' => 'sarvix.demo/food',
        'website' => 'sarvix.demo/services',
        'ecommerce' => 'sarvix.demo/shop',
        'crm' => 'sarvix.demo/crm',
        'inventory' => 'sarvix.demo/ops',
        'ai' => 'sarvix.demo/automation',
    ];
@endphp

<div class="demo-window">
    <div class="demo-chrome">
        <span class="demo-dot bg-[#e8b4b4]"></span>
        <span class="demo-dot bg-[#e6d48a]"></span>
        <span class="demo-dot bg-[#9dcea8]"></span>
        <span class="demo-url">{{ $urls[$type] ?? 'sarvix.demo' }}</span>
    </div>
    <div class="p-3.5 sm:p-4">
        @switch($type)
            @case('store')
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-bold tracking-[0.14em] text-gold">KIRANA MART</span>
                    <span class="rounded bg-gold/90 px-2 py-0.5 text-[8px] font-bold text-navy-deep">CART 2</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['Atta 5kg', 'Oil 1L', 'Milk Pack', 'Rice 10kg', 'Soap', 'Tea'] as $item)
                        <div class="rounded-md border border-white/8 bg-white/[0.06] p-2">
                            <div class="mb-2 h-9 rounded-sm bg-white/12"></div>
                            <p class="truncate text-[9px] font-semibold text-white/75">{{ $item }}</p>
                            <p class="mt-1 text-[8px] font-bold text-gold">Enquire</p>
                        </div>
                    @endforeach
                </div>
                @break
            @case('restaurant')
                <div class="mb-3 flex items-center justify-between rounded-md bg-white/10 px-3 py-2">
                    <span class="text-[10px] font-bold tracking-wide text-white">Today's Menu</span>
                    <span class="text-[8px] font-semibold text-gold">Open now</span>
                </div>
                @foreach ([['Thali Special', '₹180'], ['Gujarati Combo', '₹220'], ['Tandoor Platter', '₹260'], ['Festival Thali', '₹199']] as $row)
                    <div class="mb-2 flex items-center justify-between rounded-md border border-white/8 bg-white/[0.06] px-3 py-2">
                        <span class="text-[11px] font-medium text-white/80">{{ $row[0] }}</span>
                        <span class="rounded bg-gold/80 px-2 py-0.5 text-[9px] font-bold text-navy-deep">{{ $row[1] }}</span>
                    </div>
                @endforeach
                @break
            @case('website')
                <div class="mb-3 flex items-center justify-between rounded-md bg-white/10 px-3 py-2">
                    <span class="text-[10px] font-bold tracking-[0.12em] text-white/80">ADVISORY FIRM</span>
                    <span class="h-4 w-16 rounded bg-gold/70"></span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-md bg-white/[0.07] p-2">
                        <p class="text-[9px] font-semibold text-white/70">Services</p>
                        <div class="mt-2 h-8 rounded bg-white/10"></div>
                    </div>
                    <div class="rounded-md bg-white/[0.07] p-2">
                        <p class="text-[9px] font-semibold text-white/70">About</p>
                        <div class="mt-2 h-8 rounded bg-white/10"></div>
                    </div>
                    <div class="rounded-md border border-gold/30 bg-gold/15 p-2">
                        <p class="text-[9px] font-semibold text-gold">Enquire</p>
                        <div class="mt-2 h-8 rounded bg-gold/20"></div>
                    </div>
                </div>
                <div class="mt-2 rounded-md bg-white/[0.06] px-3 py-2 text-[10px] text-white/50">Clear offer. Direct enquiry. Mobile-first layout.</div>
                @break
            @case('ecommerce')
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-bold tracking-wide text-white/80">SHOP</span>
                    <span class="text-[9px] text-white/40">Search products</span>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    @foreach (range(1, 4) as $i)
                        <div class="rounded-md border border-white/8 bg-white/[0.06] p-2">
                            <div class="mb-2 h-10 rounded-sm bg-white/12"></div>
                            <div class="h-1.5 w-3/4 rounded bg-white/30"></div>
                            <div class="mt-1 h-1.5 w-1/2 rounded bg-gold/50"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex h-8 items-center justify-between rounded-md bg-white/10 px-3">
                    <span class="text-[9px] font-medium text-white/45">Subtotal</span>
                    <span class="rounded bg-gold px-2 py-0.5 text-[8px] font-bold text-navy-deep">Checkout</span>
                </div>
                @break
            @case('crm')
                <div class="mb-2 flex items-center justify-between text-[9px] font-semibold uppercase tracking-wider text-white/40">
                    <span>Lead</span><span>Stage</span>
                </div>
                @foreach ([['New enquiry', 'Today'], ['Follow-up', 'In progress'], ['Proposal sent', 'Warm'], ['Site visit', 'Scheduled'], ['Closed won', 'Done']] as $i => $row)
                    <div class="mb-1.5 flex items-center gap-2 rounded-md border border-white/8 bg-white/[0.06] px-3 py-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $i === 0 ? 'bg-gold' : 'bg-white/20' }}"></span>
                        <span class="flex-1 text-[11px] font-medium text-white/80">{{ $row[0] }}</span>
                        <span class="text-[9px] text-white/40">{{ $row[1] }}</span>
                    </div>
                @endforeach
                @break
            @case('inventory')
                <div class="mb-2 grid grid-cols-4 gap-1 text-[8px] font-bold uppercase tracking-wider text-white/40">
                    <span>Item</span><span>Stock</span><span>Value</span><span>Status</span>
                </div>
                @foreach ([['SKU-104', '82', '₹24k', 'OK'], ['SKU-221', '14', '₹9k', 'Low'], ['SKU-318', '240', '₹61k', 'OK'], ['SKU-405', '6', '₹3k', 'Reorder']] as $row)
                    <div class="mb-1.5 grid grid-cols-4 items-center gap-1 rounded-md border border-white/8 bg-white/[0.06] px-3 py-2 text-[10px] text-white/70">
                        <span class="font-semibold">{{ $row[0] }}</span>
                        <span>{{ $row[1] }}</span>
                        <span>{{ $row[2] }}</span>
                        <span class="{{ $row[3] !== 'OK' ? 'font-bold text-gold' : 'text-white/50' }}">{{ $row[3] }}</span>
                    </div>
                @endforeach
                @break
            @default
                <div class="flex gap-2">
                    <div class="w-[32%] space-y-2">
                        <div class="rounded-md bg-gold/20 px-2 py-2 text-[9px] font-bold text-gold">Inbox</div>
                        <div class="rounded-md bg-white/[0.07] px-2 py-2 text-[9px] text-white/50">Workflow</div>
                        <div class="rounded-md bg-white/[0.07] px-2 py-2 text-[9px] text-white/50">WhatsApp</div>
                    </div>
                    <div class="flex-1 rounded-md border border-white/8 bg-white/[0.06] p-3">
                        <p class="text-[10px] font-bold text-gold">Lead captured</p>
                        <p class="mt-1.5 text-[10px] leading-relaxed text-white/55">Enquiry routed. Follow-up queued. Team notified.</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded bg-white/10 px-2 py-2 text-center text-[8px] font-semibold text-white/60">Chatbot</div>
                            <div class="rounded bg-gold/25 px-2 py-2 text-center text-[8px] font-semibold text-gold">Send</div>
                        </div>
                    </div>
                </div>
        @endswitch
    </div>
</div>
