@php
    $promoTab = match (true) {
        request()->routeIs('admin.promotions.coupons.*') => 'coupons',
        request()->routeIs('admin.promotions.usage') => 'usage',
        request()->routeIs('admin.promotions.analytics') => 'analytics',
        default => 'campaigns',
    };
    $promoTabs = [
        'campaigns' => ['Campaigns', route('admin.promotions.campaigns.index')],
        'coupons' => ['Coupons', route('admin.promotions.coupons.index')],
        'usage' => ['Usage', route('admin.promotions.usage')],
        'analytics' => ['Analytics', route('admin.promotions.analytics')],
    ];
@endphp
<nav class="promo-admin-nav" aria-label="Promotions">
    @foreach ($promoTabs as $key => [$label, $href])
        <a href="{{ $href }}" @class(['is-current' => $promoTab === $key])>{{ $label }}</a>
    @endforeach
    @can('admin.promotions.manage')
        @if ($promoTab === 'coupons')
            <a class="is-create" href="{{ route('admin.promotions.coupons.create') }}">New coupon</a>
        @else
            <a class="is-create" href="{{ route('admin.promotions.campaigns.create') }}">New campaign</a>
        @endif
    @endcan
</nav>
<style>
.promo-admin-nav{display:flex;flex-wrap:wrap;align-items:center;gap:.4rem;margin:0 0 1rem;padding:.45rem;border:1px solid #e2e8f0;background:#fff;box-shadow:0 8px 24px rgba(1,58,157,.05)}
.promo-admin-nav a{position:relative;isolation:isolate;overflow:hidden;display:inline-flex;align-items:center;min-height:2.45rem;padding:.4rem 1rem;border:1px solid transparent;color:#013a9d;font-weight:700;text-decoration:none;transition:background-color .28s ease,color .28s ease,transform .28s cubic-bezier(.22,1,.36,1),box-shadow .28s ease}
.promo-admin-nav a::before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:#fbb50b;transform:scaleY(0);transform-origin:bottom;transition:transform .32s cubic-bezier(.22,1,.36,1);z-index:1}
.promo-admin-nav a::after{content:"";position:absolute;inset:0;background:linear-gradient(115deg,transparent 16%,rgba(255,255,255,.4) 48%,transparent 74%);transform:translateX(-130%);transition:transform .7s ease;pointer-events:none;z-index:1}
.promo-admin-nav a:hover::before,.promo-admin-nav a:focus-visible::before{transform:scaleY(1)}
.promo-admin-nav a:hover::after,.promo-admin-nav a:focus-visible::after{transform:translateX(130%)}
.promo-admin-nav a:hover:not(.is-current):not(.is-create),.promo-admin-nav a:focus-visible:not(.is-current):not(.is-create){background:#013a9d;color:#fff;transform:translateY(-3px);box-shadow:0 14px 28px rgba(1,58,157,.22)}
.promo-admin-nav a.is-current{background:#013a9d;color:#fff;box-shadow:inset 0 -3px 0 #fbb50b}
.promo-admin-nav a.is-create{margin-left:auto;background:#fbb50b;color:#010b24;box-shadow:0 8px 18px rgba(251,181,11,.28)}
.promo-admin-nav a.is-create:hover,.promo-admin-nav a.is-create:focus-visible{background:#ffc62a;color:#010b24;transform:translateY(-3px);box-shadow:0 16px 32px rgba(251,181,11,.45)}
.promo-admin-table{width:100%;font-size:.84rem}
.promo-admin-table th,.promo-admin-table td{white-space:nowrap;vertical-align:middle;padding:.5rem .35rem}
.promo-admin-table tbody tr{transition:background-color .25s ease,box-shadow .25s ease}
.promo-admin-table tbody tr:hover{background:rgba(251,181,11,.1);box-shadow:inset 3px 0 0 #fbb50b}
.promo-admin-table .promo-name{color:#013a9d;font-weight:800;text-decoration:none}
.promo-admin-table .promo-name:hover{color:#0246b8}
.promo-edit{display:inline-flex;align-items:center;min-height:1.8rem;padding:.2rem .55rem;border:1px solid rgba(1,58,157,.18);background:#fff;color:#013a9d;font-weight:700;text-decoration:none;transition:background-color .25s ease,color .25s ease,transform .25s ease,box-shadow .25s ease}
.promo-edit:hover,.promo-edit:focus-visible{background:#013a9d;border-color:#013a9d;color:#fff;transform:translateY(-2px);box-shadow:0 10px 20px rgba(1,58,157,.2);text-decoration:none}
.promo-row-actions{display:flex;flex-wrap:nowrap;gap:.4rem;align-items:center}
.promo-row-actions form{margin:0}
.promo-delete{display:inline-flex;align-items:center;min-height:1.8rem;padding:.2rem .55rem;border:1px solid rgba(185,28,28,.28);background:#fff;color:#b91c1c;font-weight:700;cursor:pointer;transition:background-color .25s ease,color .25s ease,transform .25s ease,box-shadow .25s ease}
.promo-delete:hover,.promo-delete:focus-visible{background:#b91c1c;border-color:#b91c1c;color:#fff;transform:translateY(-2px);box-shadow:0 10px 20px rgba(185,28,28,.22)}
.promo-pill{display:inline-flex;align-items:center;padding:.16rem .45rem;background:#e8f0fb;color:#013a9d;font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.promo-pill.is-active{background:rgba(251,181,11,.22);color:#8a6400}
.promo-pill.is-draft,.promo-pill.is-scheduled{background:#fff7e6;color:#92400e}
.promo-pill.is-expired,.promo-pill.is-disabled,.promo-pill.is-off{background:#f1f5f9;color:#64748b}
.promo-admin-note{margin:0 0 1rem;color:#5c6168;line-height:1.5}
.promo-admin-actions{display:flex;flex-wrap:wrap;gap:.5rem;margin:0 0 1rem}
@media (max-width:720px){.promo-admin-nav a.is-create{margin-left:0}}
</style>
