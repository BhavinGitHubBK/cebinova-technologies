@php
    $visitorBoot = [
        'visit' => route('analytics.visit'),
        'identify' => route('analytics.identify'),
        'locate' => route('analytics.location'),
        'csrf' => csrf_token(),
        'path' => '/'.ltrim(request()->path(), '/'),
        'loggedOut' => session()->has('logged_out'),
    ];
@endphp
<script type="application/json" id="cebinova-analytics">@json($visitorBoot)</script>
<div class="cn-visitor-popup" data-visitor-popup hidden>
    <form data-visitor-form role="dialog" aria-modal="true" aria-labelledby="cnVisitorTitle">
        <p class="cn-visitor-kicker"><span class="cn-visitor-dot" aria-hidden="true"></span>CEBINOVA</p>
        <h2 id="cnVisitorTitle">How can we reach you?</h2>
        <p class="cn-visitor-lead">Leave your name and mobile number so we can help you with the page you are viewing. Your browser will also ask to share your location.</p>
        <label>
            Name
            <input name="name" type="text" maxlength="80" autocomplete="name" required placeholder="Your name">
        </label>
        <label>
            Mobile number
            <input name="mobile" type="tel" inputmode="numeric" maxlength="14" autocomplete="tel" required placeholder="10-digit mobile">
        </label>
        <p class="cn-visitor-error" data-visitor-error hidden></p>
        <button type="submit" data-visitor-submit>
            <span class="cn-visitor-spinner" aria-hidden="true"></span>
            <span data-visitor-label>Continue</span>
        </button>
    </form>
</div>
<style>
.cn-visitor-popup{position:fixed;z-index:80;inset:0;display:grid;place-items:center;padding:1.25rem;background:rgba(1,11,36,.46);font-family:Outfit,ui-sans-serif,system-ui,sans-serif}
.cn-visitor-popup[hidden]{display:none}
.cn-visitor-popup form{width:min(26.5rem,100%);padding:1.45rem 1.4rem 1.35rem;border-radius:0;border-top:3px solid #fbb50b;background:radial-gradient(ellipse 70% 42% at 100% 0%,rgba(251,181,11,.14),transparent 58%),radial-gradient(ellipse 55% 36% at 0% 18%,rgba(1,58,157,.08),transparent 55%),#fff;color:#0b1220;box-shadow:0 28px 70px rgba(1,11,36,.28)}
.cn-visitor-kicker{display:inline-flex;align-items:center;gap:.45rem;margin:0;padding:.28rem .75rem .28rem .5rem;border:1px solid rgba(1,58,157,.12);border-radius:999px;background:linear-gradient(90deg,rgba(251,181,11,.1),#fff 46%);color:#3d4a5c;font-size:.68rem;font-weight:700;letter-spacing:.12em;line-height:1.3;text-transform:uppercase}
.cn-visitor-dot{width:.42rem;height:.42rem;border-radius:999px;background:radial-gradient(circle at 30% 30%,#ffe08a,#fbb50b 55%,#c48e08);box-shadow:0 0 0 4px rgba(251,181,11,.16)}
.cn-visitor-popup h2{margin:.85rem 0 0;color:#013a9d;font-family:"Plus Jakarta Sans",Outfit,sans-serif;font-size:1.7rem;font-weight:800;line-height:1.15;letter-spacing:.01em}
.cn-visitor-popup h2::after{content:"";display:block;width:3.4rem;height:3px;margin-top:.55rem;border-radius:99px;background:linear-gradient(90deg,#fbb50b,#013a9d)}
.cn-visitor-lead{margin:.75rem 0 1.05rem;color:#5b6573;font-size:.95rem;line-height:1.5}
.cn-visitor-popup label{display:grid;gap:.4rem;margin-bottom:.8rem;color:#013a9d;font-size:.875rem;font-weight:600}
.cn-visitor-popup input{width:100%;box-sizing:border-box;min-height:3rem;border:1px solid #e2e8f0;border-radius:0;padding:.7rem .9rem;background:#f8fafc;color:#013a9d;font:inherit;font-weight:500;outline:none}
.cn-visitor-popup input:focus{background:#fff;border-color:#013a9d;box-shadow:0 0 0 4px rgba(1,58,157,.12)}
.cn-visitor-popup button{width:100%;min-height:2.85rem;margin-top:.25rem;border:0;border-radius:0;padding:.7rem 1rem;background:#fbb50b;color:#010b24;box-shadow:0 1px 0 rgba(255,255,255,.38) inset,0 10px 22px rgba(251,181,11,.3);font:inherit;font-weight:700;cursor:pointer}
.cn-visitor-popup button:hover{background:#ffc62a}
.cn-visitor-error{margin:.1rem 0 .7rem !important;color:#9b1c1c !important;font-size:.9rem}
</style>
<script src="{{ asset('assets/analytics.js') }}?v=11" defer></script>
