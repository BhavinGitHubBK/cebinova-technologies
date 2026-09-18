@extends('layouts.app')

@php
    $email = config('cebinova.contact.email');
    $phone = cebinova_phone();
    $phoneDigits = preg_replace('/\D+/', '', (string) ($phone ?: config('cebinova.contact.whatsapp')));
    $phoneLabel = $phone ?: 'Ahmedabad, Gujarat';
    $waHref = filled(config('cebinova.contact.whatsapp'))
        ? whatsapp_url()
        : solution_enquiry_url(['solution' => 'Digital Store', 'source' => 'CEBINOVA Pricing', 'business_type' => 'Retail']);
    $telHref = $phone ? 'tel:'.preg_replace('/\s+/', '', $phone) : route('contact');
    $scheduleHref = filled(config('cebinova.contact.whatsapp'))
        ? whatsapp_url("Hi CEBINOVA,\nI want to schedule a free 30-minute consultation about a digital solution for my business.")
        : consultation_url('Business Solution');
    $enquiry = solution_enquiry_url([
        'solution' => 'Digital Store',
        'source' => 'CEBINOVA Pricing',
        'business_type' => 'Retail',
    ]);
@endphp

@section('title', 'Pricing | CEBINOVA Technologies')
@section('description', 'Know your exact investment in under 2 minutes. Transparent website, app, domain and hosting pricing for practical digital business solutions.')

@push('head')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/soft-3d.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/compare-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/apps-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/domain-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/hosting-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/support-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/cta-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/footer-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/estimate-premium.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/packages-pricing.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/print-invoice.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/solutions/kirana-pricing/css/cebinova-brand.css') }}">
@endpush

@section('content')
    @include('sections.pricing.hero')

<div class="kirana-pricing" data-wa="{{ $phoneDigits }}" data-email="{{ $email }}" data-contact="{{ $enquiry }}">
  <div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
  <div id="plans"></div>
    <section class="section calc-section" id="calculator">
      <div class="container">
        <div class="price-page-calc-head" data-reveal>
          <p class="price-page-kicker">
            <span class="price-page-dot" aria-hidden="true"></span>
            Live calculator
          </p>
          <h2 class="section-title">Know your exact investment.</h2>
          <p class="section-support">Pick a website package, then add apps, domain, hosting and support only if you need them.</p>
          <ul class="price-page-trust" aria-label="Why this calculator">
            <li>No Hidden Charges</li>
            <li>Live Calculator</li>
            <li>Transparent Pricing</li>
            <li>Expert Support</li>
          </ul>
        </div>

        <div class="calculator-layout">
          <div class="calculator-main">
          <div class="calc-progress-wrap reveal">
            <div class="calc-progress-meta">
              <span class="calc-progress-label">Your plan</span>
              <strong class="calc-progress-pct" id="calcProgressPct">25%</strong>
            </div>
            <div class="calc-progress-track" aria-hidden="true">
              <span class="calc-progress-fill" id="calcProgressFill"></span>
            </div>
            <ol class="calc-progress" id="calcProgress" aria-label="Calculator progress" aria-live="polite">
              <li class="active current" data-step="1">
                <span class="calc-step-num">1</span>
                <span class="calc-step-copy">
                  <strong class="calc-step-label">Website wd</strong>
                  <em class="calc-step-value" data-step-value>Choose</em>
                </span>
              </li>
              <li data-step="2">
                <span class="calc-step-num">2</span>
                <span class="calc-step-copy">
                  <strong class="calc-step-label">Apps</strong>
                  <em class="calc-step-value" data-step-value>Optional</em>
                </span>
              </li>
              <li data-step="3">
                <span class="calc-step-num">3</span>
                <span class="calc-step-copy">
                  <strong class="calc-step-label">Domain</strong>
                  <em class="calc-step-value" data-step-value>Choose</em>
                </span>
              </li>
              <li data-step="4">
                <span class="calc-step-num">4</span>
                <span class="calc-step-copy">
                  <strong class="calc-step-label">Hosting</strong>
                  <em class="calc-step-value" data-step-value>Choose</em>
                </span>
              </li>
              <li data-step="5">
                <span class="calc-step-num">5</span>
                <span class="calc-step-copy">
                  <strong class="calc-step-label">Support</strong>
                  <em class="calc-step-value" data-step-value>Included</em>
                </span>
              </li>
            </ol>
          </div>

          <form class="calculator-form calculator-panel" id="costCalculator" novalidate>

            <fieldset class="calc-step" data-step-panel="1">
              <legend>
                <span class="step-badge"><span class="step-num">01</span><span class="step-dot" aria-hidden="true"></span></span>
                Choose Your Website Package
              </legend>
              <p class="step-hint">Starter for WhatsApp selling. Business for growing shops. Professional for full online orders.</p>

              <div class="included-strip">
                <p class="included-title">Included in every website</p>
                <ul class="included-list">
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Responsive</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Admin</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Login</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Cart</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Wishlist</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Products</span></li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Categories</span></li>
                </ul>
              </div>

              <div class="package-options" role="radiogroup" aria-label="Website package">

                <label class="option-card package-select pkg-starter">
                  <input type="radio" name="website" value="starter" data-price="14999" data-label="Starter" data-delivery="5–7 Days" data-support="7 Days" data-training="Free">
                  <span class="option-body">
                    <span class="best-pill soft">Start Here</span>
                    <span class="package-head">
                      <span class="package-title-block">
                        <span class="option-top">
                          <span class="radio-ui" aria-hidden="true"></span>
                          <span class="option-name">Starter</span>
                        </span>
                        <span class="best-for">Perfect for small businesses starting their digital journey.</span>
                      </span>
                      <span class="package-price-block">
                        <span class="option-price">₹14,999</span>
                        <span class="price-cadence">One Time</span>
                      </span>
                    </span>
                    <span class="pkg-promo-slot">
                      <span class="pkg-trust-line">Affordable Entry Plan</span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> New shops starting digitally</span>
                      <span class="pkg-fact"><em>Main benefit</em> WhatsApp selling and a product catalogue</span>
                      <span class="pkg-fact"><em>Price</em> ₹14,999 one time</span>
                      <span class="pkg-fact"><em>Choose this if</em> Budget is tight and you only need a first website</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <span class="plan-meta">
                      <span class="plan-meta-item"><span class="plan-meta-label">Support</span><span class="plan-meta-value">7 Days Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Training</span><span class="plan-meta-value">Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Delivery</span><span class="plan-meta-value">5–7 Days</span></span>
                    </span>
                    <span class="ideal-for">
                      <span class="ideal-label">Ideal for</span>
                      <span class="ideal-tags">
                        <span>New Shop</span>
                        <span>Low Budget</span>
                        <span>WhatsApp Selling</span>
                      </span>
                    </span>
                    <span class="feature-group highlight-group">
                      <span class="feature-group-title">Highlights</span>
                      <ul class="mini-features highlight-features">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Ordering</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Basic Product Catalogue</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Contact &amp; Enquiry Form</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Google Map Integration</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Basic Dashboard</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Basic SEO Setup</li>
                      </ul>
                    </span>
                    </details>
                    <span class="package-footer">
                      <p class="plan-insight">Ideal if you only need a professional website with WhatsApp ordering and product catalogue.</p>
                    </span>
                  </span>
                </label>

                <label class="option-card package-select recommended pkg-business">
                  <input type="radio" name="website" value="business" data-price="24999" data-label="Business" data-delivery="7–10 Days" data-support="15 Days" data-training="Free" checked>
                  <span class="option-body">
                    <span class="best-pill"><i class="fa-solid fa-star" aria-hidden="true"></i> Most Popular</span>
                    <span class="package-head">
                      <span class="package-title-block">
                        <span class="option-top">
                          <span class="radio-ui" aria-hidden="true"></span>
                          <span class="option-name">Business</span>
                        </span>
                        <span class="best-for">Perfect for growing businesses</span>
                      </span>
                      <span class="package-price-block">
                        <span class="option-price">₹24,999</span>
                        <span class="price-cadence">One Time</span>
                      </span>
                    </span>
                    <span class="pkg-promo-slot">
                      <span class="pkg-trust-line">Chosen by most businesses · <strong>Best Value</strong></span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> Growing shops that update products often</span>
                      <span class="pkg-fact"><em>Main benefit</em> Offers, search and a clearer store</span>
                      <span class="pkg-fact"><em>Price</em> ₹24,999 one time</span>
                      <span class="pkg-fact"><em>Recommended because</em> Most shops choose this. 15 days free support included.</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <span class="plan-meta">
                      <span class="plan-meta-item"><span class="plan-meta-label">Support</span><span class="plan-meta-value">15 Days Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Training</span><span class="plan-meta-value">Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Delivery</span><span class="plan-meta-value">7–10 Days</span></span>
                    </span>
                    <span class="ideal-for">
                      <span class="ideal-label">Ideal for</span>
                      <span class="ideal-tags">
                        <span>Growing Stores</span>
                        <span>Product Updates</span>
                        <span>Promotions</span>
                      </span>
                    </span>
                    <span class="feature-group highlight-group">
                      <span class="feature-group-title">Highlights</span>
                      <ul class="mini-features highlight-features">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Everything in Starter</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Advanced Product Search</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Product Filters</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Banner &amp; Offer Management</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Featured Products</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Advanced Dashboard</li>
                      </ul>
                    </span>
                    </details>
                    <span class="package-footer">
                      <p class="plan-insight">Recommended if you update products and run offers often.</p>
                    </span>
                  </span>
                </label>

                <label class="option-card package-select complete pkg-pro">
                  <input type="radio" name="website" value="professional" data-price="29999" data-label="Professional" data-delivery="10–15 Days" data-support="30 Days" data-training="Free">
                  <span class="option-body">
                    <span class="best-pill soft"><i class="fa-solid fa-crown" aria-hidden="true"></i> Best Choice</span>
                    <span class="package-head">
                      <span class="package-title-block">
                        <span class="option-top">
                          <span class="radio-ui" aria-hidden="true"></span>
                          <span class="option-name">Professional</span>
                        </span>
                        <span class="best-for">Complete business solution for stores that want to grow faster.</span>
                      </span>
                      <span class="package-price-block">
                        <span class="option-price">₹29,999</span>
                        <span class="price-cadence">One Time</span>
                      </span>
                    </span>
                    <span class="pkg-promo-slot">
                      <span class="pkg-upgrade-badge" aria-label="Upgrade from Business for only 5000 more">
                        <span class="pkg-upgrade-top"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> Upgrade from Business</span>
                        <strong>Only ₹5,000 More</strong>
                      </span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> Stores that want full online orders</span>
                      <span class="pkg-fact"><em>Main benefit</em> Order tracking, coupons and sales reports</span>
                      <span class="pkg-fact"><em>Price</em> ₹29,999 one time</span>
                      <span class="pkg-fact"><em>Choose this if</em> You are ready to sell online, not only on WhatsApp</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <span class="plan-meta">
                      <span class="plan-meta-item"><span class="plan-meta-label">Support</span><span class="plan-meta-value">30 Days Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Training</span><span class="plan-meta-value">Free</span></span>
                      <span class="plan-meta-item"><span class="plan-meta-label">Delivery</span><span class="plan-meta-value">10–15 Days</span></span>
                    </span>
                    <span class="ideal-for">
                      <span class="ideal-label">Ideal for</span>
                      <span class="ideal-tags">
                        <span>Online Selling</span>
                        <span>Customer Accounts</span>
                        <span>Full Orders</span>
                      </span>
                    </span>
                    <span class="feature-group highlight-group">
                      <span class="feature-group-title">Highlights</span>
                      <ul class="mini-features highlight-features">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Everything in Business</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Online Order Management</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Order Status Tracking</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Coupon Management</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Sales Analytics</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Notification System</li>
                      </ul>
                    </span>
                    </details>
                    <span class="package-footer">
                      <p class="plan-insight">Best when you want full online selling and faster growth.</p>
                    </span>
                  </span>
                </label>
              </div>

              <div class="compare-cta">
                <button type="button" class="btn btn-outline see-features" id="comparePlansBtn">
                  Compare all features
                </button>
              </div>
            </fieldset>

            <fieldset class="calc-step" data-step-panel="2">
              <legend>
                <span class="step-badge"><span class="step-num">02</span><span class="step-dot" aria-hidden="true"></span></span>
                Want Mobile Apps?
              </legend>
              <p class="step-hint">Optional. Skip if you only need a website. Android is recommended later if most customers use phones.</p>
              <p class="pkg-skip-note">Recommended now: <strong>No app</strong> - add later when the website is live.</p>

              <div class="app-value-card">
                <div class="app-value-copy">
                  <p class="app-value-kicker">Mobile App Add-on</p>
                  <h4>Want customers to order from their phone?</h4>
                  <p>Add a native app experience with push alerts and store publishing support.</p>
                </div>
                <ul class="app-value-points">
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i> Android App</li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i> iPhone App</li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i> Push Notifications</li>
                  <li><i class="fa-solid fa-check" aria-hidden="true"></i> Play Store &amp; App Store Publishing</li>
                </ul>
              </div>

              <div class="app-select-grid">
                <label class="option-card check app-pick" data-app="android">
                  <input type="checkbox" name="android" id="optAndroid" data-price="24999" data-label="Android App">
                  <span class="option-body">
                    <span class="app-card-top">
                      <span class="app-identity">
                        <span class="app-icon android" aria-hidden="true"><i class="fa-brands fa-android"></i></span>
                        <span class="app-meta">
                          <span class="option-name">Android App</span>
                          <span class="app-badge popular"><i class="fa-solid fa-star" aria-hidden="true"></i> Most Popular</span>
                        </span>
                      </span>
                      <span class="check-ui" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                    </span>

                    <span class="app-price-block">
                      <span class="option-price"><span class="price-prefix">+</span> ₹24,999</span>
                      <span class="app-value-pill affordable">Affordable Choice</span>
                      <span class="app-price-meta">
                        <em>One-Time Cost</em>
                        <span class="app-no-monthly">No Monthly Charges</span>
                      </span>
                      <span class="app-subline">Recommended for 80% of businesses.</span>
                    </span>

                    <span class="app-upsell" id="androidUpsell" hidden>
                      <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                      Add Android &amp; Reach 95%+ Indian Users
                    </span>

                    <span class="app-share">
                      <span class="app-share-title">Perfect For</span>
                      <ul class="app-perfect-list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Most Indian Customers</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Maximum Market Reach</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Best Return on Investment</li>
                      </ul>
                    </span>

                    <ul class="app-benefits">
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Google Play Store</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Covers 95% Android Users</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Push Notifications</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Affordable Development</li>
                    </ul>

                    <span class="app-best">
                      <span class="app-best-label">Best for</span>
                      <span class="app-best-tags">
                        <em>Retail Stores</em>
                        <em>Local Shops</em>
                        <em>Growing Brands</em>
                      </span>
                    </span>
                  </span>
                </label>

                <span class="app-or" aria-hidden="true">OR</span>

                <label class="option-card check app-pick" data-app="ios">
                  <input type="checkbox" name="ios" id="optIos" data-price="34999" data-label="iOS App">
                  <span class="option-body">
                    <span class="app-card-top">
                      <span class="app-identity">
                        <span class="app-icon apple" aria-hidden="true"><i class="fa-brands fa-apple"></i></span>
                        <span class="app-meta">
                          <span class="option-name">iPhone App</span>
                          <span class="app-badge premium"><i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Choice</span>
                        </span>
                      </span>
                      <span class="check-ui" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                    </span>

                    <span class="app-price-block">
                      <span class="option-price"><span class="price-prefix">+</span> ₹34,999</span>
                      <span class="app-upgrade-pill" aria-label="Upgrade from Android for only 10000 more">
                        <span>Upgrade from Android</span>
                        <strong>Only ₹10,000 More</strong>
                      </span>
                      <span class="app-price-meta">
                        <em>One-Time Cost</em>
                        <span class="app-no-monthly">No Monthly Charges</span>
                      </span>
                      <span class="app-subline">Perfect for premium customers and stronger brand positioning.</span>
                    </span>

                    <span class="app-upsell" id="iosUpsell" hidden>
                      <i class="fa-solid fa-fire" aria-hidden="true"></i>
                      Complete App Bundle? Add iPhone for Only ₹10,000 More
                    </span>

                    <span class="app-share">
                      <span class="app-share-title">Perfect For</span>
                      <ul class="app-perfect-list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Premium Customers</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> High Purchase Value</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Strong Brand Image</li>
                      </ul>
                    </span>

                    <ul class="app-benefits">
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> App Store</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Premium Experience</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Better Brand Trust</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> High Spending Customers</li>
                    </ul>

                    <span class="app-best">
                      <span class="app-best-label">Best for</span>
                      <span class="app-best-tags">
                        <em>Luxury Stores</em>
                        <em>Premium Brands</em>
                        <em>Franchise</em>
                      </span>
                    </span>
                  </span>
                </label>
              </div>

              <div class="app-both-banner" id="appBothBanner" hidden>
                <span class="app-both-icon" aria-hidden="true">🚀</span>
                <div>
                  <strong>Maximum Market Reach</strong>
                  <p>Android + iPhone selected · App bundle <b>₹59,998</b></p>
                </div>
              </div>

              <p class="app-recommend" id="appRecommend" role="status"></p>
              <p class="app-select-status" id="appSelectStatus">Skip for Now</p>
              <p class="bundle-hint" id="bundleHint" hidden>Both selected · App bundle: <strong>₹59,998</strong></p>
            </fieldset>

            <fieldset class="calc-step domain-step domain-premium-v2" data-step-panel="3">
              <legend>
                <span class="step-badge"><span class="step-num">03</span><span class="step-dot" aria-hidden="true"></span></span>
                Choose Your Domain
              </legend>
              <p class="step-hint">Give your business a professional web address.</p>

              <p class="domain-info-chip">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                A domain is your website address - e.g. yourstore.com
              </p>

              <div class="domain-select-grid" role="radiogroup" aria-label="Website address option">
                <label class="option-card domain-pick" data-domain="existing">
                  <input type="radio" name="domainOption" value="0" data-label="Already have">
                  <span class="option-body">
                    <span class="domain-card-top">
                      <span class="domain-identity">
                        <span class="domain-icon existing" aria-hidden="true"><i class="fa-solid fa-globe"></i></span>
                        <span class="domain-meta">
                          <span class="option-name">Use Existing Domain</span>
                          <span class="domain-badge save">Save Money</span>
                        </span>
                      </span>
                      <span class="radio-ui" aria-hidden="true"></span>
                    </span>

                    <span class="domain-price-block">
                      <span class="option-price">₹0</span>
                      <span class="domain-price-meta">We'll connect your current address.</span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> Businesses that already have a web address</span>
                      <span class="pkg-fact"><em>Main benefit</em> No extra domain cost</span>
                      <span class="pkg-fact"><em>Price</em> ₹0</span>
                      <span class="pkg-fact"><em>Choose this if</em> You already own yourstore.com or similar</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <ul class="domain-benefits">
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> No additional domain cost</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Keep your current address</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Faster launch</li>
                    </ul>
                    </details>
                    <span class="domain-foot-badge">Best for Existing Websites</span>
                  </span>
                </label>

                <label class="option-card domain-pick is-new" data-domain="new">
                  <input type="radio" name="domainOption" id="domainOptionNew" value="799" data-label="New Domain" data-provider="Hostinger" checked>
                  <span class="option-body">
                    <span class="domain-card-top">
                      <span class="domain-identity">
                        <span class="domain-icon fresh" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                        <span class="domain-meta">
                          <span class="option-name">Register New Domain</span>
                          <span class="domain-badge fresh">Professional Identity</span>
                          <span class="domain-badge recommend" id="domainRecommendedBadge"><i class="fa-solid fa-star" aria-hidden="true"></i> Recommended</span>
                        </span>
                      </span>
                      <span class="radio-ui" aria-hidden="true"></span>
                    </span>

                    <span class="domain-price-block">
                      <span class="domain-price-row">
                        <span class="option-price" id="domainNewPrice">₹799</span>
                        <span class="domain-price-period">/year</span>
                      </span>
                      <span class="domain-price-meta">
                        Starting from · <span id="domainProviderName">Hostinger</span>
                      </span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> First-time online businesses</span>
                      <span class="pkg-fact"><em>Main benefit</em> A professional address customers can remember</span>
                      <span class="pkg-fact"><em>Price</em> From ₹799 / year</span>
                      <span class="pkg-fact"><em>Recommended because</em> New shops look more trusted with their own domain</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <span class="domain-providers" role="group" aria-label="Domain provider prices">
                      <span class="domain-best-label">Choose provider</span>
                      <span class="domain-provider-list">
                        <button type="button" class="domain-provider is-active" data-provider="Hostinger" data-price="799">
                          <strong>Hostinger</strong>
                          <span>₹799</span>
                        </button>
                        <button type="button" class="domain-provider" data-provider="GoDaddy" data-price="999">
                          <strong>GoDaddy</strong>
                          <span>₹999</span>
                        </button>
                        <button type="button" class="domain-provider" data-provider="BigRock" data-price="899">
                          <strong>BigRock</strong>
                          <span>₹899</span>
                        </button>
                      </span>
                      <span class="domain-provider-note">Approx. first-year .com / .in · Final cost depends on extension</span>
                    </span>

                    <ul class="domain-benefits">
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Professional brand address</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Pick your preferred provider</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Paid yearly to the provider</li>
                    </ul>

                    <span class="domain-examples">
                      <span class="domain-best-label">Examples</span>
                      <span class="domain-example-tags">
                        <em>yourstore.com</em>
                        <em>myshop.in</em>
                        <em>brand.in</em>
                      </span>
                    </span>

                    <span class="domain-upgrade-bar">
                      <strong><i class="fa-solid fa-rocket" aria-hidden="true"></i> Best for new businesses</strong>
                      <span>Build trust with a clean, professional web address</span>
                    </span>
                    </details>
                  </span>
                </label>
              </div>

              <div class="domain-why">
                <p class="domain-why-title">Why a domain matters</p>
                <div class="domain-why-grid">
                  <article>
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <strong>Looks Professional</strong>
                    <span>Customers trust a real address.</span>
                  </article>
                  <article>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <strong>Easy to Find</strong>
                    <span>Simple name, easier recall.</span>
                  </article>
                  <article>
                    <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                    <strong>Ready to Grow</strong>
                    <span>Your brand starts here.</span>
                  </article>
                </div>
              </div>
            </fieldset>

            <fieldset class="calc-step hosting-step hosting-premium-v2" data-step-panel="4">
              <legend>
                <span class="step-badge"><span class="step-num">04</span><span class="step-dot" aria-hidden="true"></span></span>
                Choose Your Hosting
              </legend>
              <p class="step-hint">Keep your website online with the right hosting plan.</p>

              <p class="hosting-info-chip">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                Hosting keeps your website online 24×7.
              </p>

              <div class="hosting-select-grid" role="radiogroup" aria-label="Hosting option">
                <label class="option-card hosting-pick" data-hosting="existing">
                  <input type="radio" name="hostingOption" value="0" data-label="Use Existing">
                  <span class="option-body">
                    <span class="hosting-card-top">
                      <span class="hosting-identity">
                        <span class="hosting-icon existing" aria-hidden="true"><i class="fa-solid fa-globe"></i></span>
                        <span class="hosting-meta">
                          <span class="option-name">Use Existing Hosting</span>
                          <span class="hosting-badge save">Save Money</span>
                        </span>
                      </span>
                      <span class="radio-ui" aria-hidden="true"></span>
                    </span>

                    <span class="hosting-price-block">
                      <span class="option-price">₹0</span>
                      <span class="hosting-price-meta">We'll connect your existing hosting.</span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> Businesses that already have hosting</span>
                      <span class="pkg-fact"><em>Main benefit</em> No extra yearly hosting fee</span>
                      <span class="pkg-fact"><em>Price</em> ₹0</span>
                      <span class="pkg-fact"><em>Choose this if</em> You already pay for hosting elsewhere</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <ul class="hosting-benefits">
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> No additional hosting cost</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> We configure everything</li>
                      <li><i class="fa-solid fa-check" aria-hidden="true"></i> Faster launch</li>
                    </ul>
                    </details>
                    <span class="hosting-foot-badge">Best for Existing Websites</span>
                  </span>
                </label>

                <label class="option-card hosting-pick" data-hosting="basic">
                  <input type="radio" name="hostingOption" value="3000" data-label="Basic Hosting" checked>
                  <span class="option-body">
                    <span class="hosting-card-top">
                      <span class="hosting-identity">
                        <span class="hosting-icon basic" aria-hidden="true"><i class="fa-solid fa-rocket"></i></span>
                        <span class="hosting-meta">
                          <span class="option-name">Basic Hosting</span>
                          <span class="hosting-badge popular" id="hostingBasicBadge">Affordable Choice</span>
                        </span>
                      </span>
                      <span class="radio-ui" aria-hidden="true"></span>
                    </span>

                    <span class="hosting-price-block">
                      <span class="hosting-price-row">
                        <span class="option-price">₹3,000</span>
                        <span class="hosting-price-period">/year</span>
                      </span>
                      <span class="hosting-price-meta">Good for new businesses.</span>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> New and small businesses</span>
                      <span class="pkg-fact"><em>Main benefit</em> Website stays online 24×7 with SSL</span>
                      <span class="pkg-fact"><em>Price</em> ₹3,000 / year</span>
                      <span class="pkg-fact"><em>Recommended because</em> Enough for most local shops to start</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <ul class="hosting-spec-list">
                      <li><span>Traffic</span><strong>~100 visitors/day</strong></li>
                      <li><span>Storage</span><strong>SSD Included</strong></li>
                      <li><span>SSL</span><strong>Included</strong></li>
                      <li><span>Backup</span><strong>Weekly</strong></li>
                    </ul>
                    <span class="hosting-best">
                      <span class="hosting-best-label">Best For</span>
                      <span class="hosting-best-tags">
                        <em>Retail</em>
                        <em>Clinic</em>
                        <em>Professional</em>
                        <em>Tutor</em>
                        <em>Restaurant</em>
                      </span>
                    </span>
                    </details>
                    <span class="hosting-reco-bar">Recommended for most small businesses</span>
                  </span>
                </label>

                <label class="option-card hosting-pick is-premium" data-hosting="premium">
                  <input type="radio" name="hostingOption" value="6000" data-label="Premium Hosting">
                  <span class="option-body">
                    <span class="hosting-card-top">
                      <span class="hosting-identity">
                        <span class="hosting-icon premium" aria-hidden="true"><i class="fa-solid fa-bolt"></i></span>
                        <span class="hosting-meta">
                          <span class="option-name">Premium Hosting</span>
                          <span class="hosting-badge performance" id="hostingPremiumBadge"><i class="fa-solid fa-crown" aria-hidden="true"></i> Best Performance</span>
                        </span>
                      </span>
                      <span class="radio-ui" aria-hidden="true"></span>
                    </span>

                    <span class="hosting-price-block">
                      <span class="hosting-price-row">
                        <span class="option-price">₹6,000</span>
                        <span class="hosting-price-period">/year</span>
                      </span>
                      <span class="hosting-price-meta">Built for growing businesses.</span>
                    </span>

                    <span class="hosting-upgrade-pill">
                      <span><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> Upgrade from Basic</span>
                      <strong>Only ₹3,000 More</strong>
                    </span>
                    <span class="pkg-simple">
                      <span class="pkg-fact"><em>For</em> Growing stores with more visitors</span>
                      <span class="pkg-fact"><em>Main benefit</em> Faster site, daily backup, priority support</span>
                      <span class="pkg-fact"><em>Price</em> ₹6,000 / year · about ₹250/month more</span>
                      <span class="pkg-fact"><em>Choose this if</em> You expect more traffic or run ads</span>
                    </span>
                    <details class="pkg-more">
                      <summary>View Details</summary>
                    <ul class="hosting-spec-list">
                      <li><span>Traffic</span><strong>~1000 visitors/day</strong></li>
                      <li><span>Storage</span><strong>High Speed SSD</strong></li>
                      <li><span>SSL</span><strong>Included</strong></li>
                      <li><span>Backup</span><strong>Daily</strong></li>
                      <li><span>CDN</span><strong>Included</strong></li>
                      <li><span>Support</span><strong>Priority</strong></li>
                    </ul>
                    <span class="hosting-upgrade-bar">
                      <strong><i class="fa-solid fa-rocket" aria-hidden="true"></i> Most Customers Choose Premium</strong>
                      <span class="hosting-month-pill">Only ₹250/month more</span>
                      <ul class="hosting-upgrade-points">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Faster Website</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Daily Backup</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Priority Support</li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> Better Performance</li>
                      </ul>
                    </span>
                    </details>
                  </span>
                </label>
              </div>

              <div class="hosting-mini-compare" aria-label="Basic vs Premium quick compare">
                <div class="hosting-mini-col">
                  <h5>Basic</h5>
                  <ul>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> SSD</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> SSL</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Weekly Backup</li>
                  </ul>
                </div>
                <div class="hosting-mini-col premium">
                  <h5>Premium</h5>
                  <ul>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> High-Speed SSD</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Daily Backup</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> CDN</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Priority Support</li>
                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Faster Loading</li>
                  </ul>
                </div>
              </div>

              <div class="hosting-why">
                <p class="hosting-why-title">Why hosting matters</p>
                <div class="hosting-why-grid">
                  <article>
                    <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                    <strong>Faster Website</strong>
                    <span>Visitors stay longer.</span>
                  </article>
                  <article>
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <strong>Better Security</strong>
                    <span>SSL + Backups.</span>
                  </article>
                  <article>
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                    <strong>Grow Easily</strong>
                    <span>Upgrade anytime.</span>
                  </article>
                </div>
              </div>

              <p class="hosting-recommend" id="hostingRecommend" hidden role="status"></p>
            </fieldset>
          </form>
          </div>

          <div class="estimate-slot" id="estimateSlot">
          <aside class="summary-card invoice-card estimate-premium" id="summaryCard" aria-live="polite">
            <div class="invoice-head est-head">
              <p class="est-kicker">Your Estimate</p>
              <h3>Live Project Estimate</h3>
              <div class="est-meta-row">
                <div class="est-badges">
                  <span class="est-badge est-badge-selected" id="selectedPlanBadge">Selected: Business</span>
                  <span class="est-badge est-badge-recommend" id="recommendedBadge">Most Popular Choice</span>
                </div>
                <span class="est-live" aria-hidden="true">
                  <span class="est-live-dot"></span> Updates instantly
                </span>
              </div>
            </div>

            <div class="invoice-body" id="invoiceBody">
              <ul class="summary-lines est-lines" id="summaryLines"></ul>

              <div class="estimate-savings est-app-status" id="estimateSavings" hidden>
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span id="estimateSavingsLabel">App bundle selected</span>
                <strong id="estimateSavingsValue"></strong>
              </div>

              <div class="est-price-summary">
                <p class="est-section-label">Price Summary</p>
                <div class="est-price-rows" id="priceSummaryRows"></div>
                <div class="est-price-total-row">
                  <span>Estimated Total</span>
                  <strong id="subTotal">₹0</strong>
                </div>
                <span class="est-gst-badge">GST extra, if applicable. No hidden package charges.</span>
              </div>
              <ul class="est-clarity" aria-label="What happens next">
                <li>Delivery: 5–15 working days after briefing, based on package.</li>
                <li>Payment: booking amount to start, balance before launch.</li>
                <li>Support: 7 / 15 / 30 free days included with the website plan.</li>
              </ul>

              <div class="summary-total est-grand" id="grandTotalCard">
                <span class="est-grand-label">Estimated Project Cost</span>
                <strong id="grandTotal">₹0</strong>
                <em class="est-grand-note">Fixed + yearly charges combined · see summary above</em>
              </div>
            </div>

            <ul class="est-value-row" aria-label="Pricing promises">
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> Transparent Pricing</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> No Hidden Charges</li>
            </ul>

            <p class="summary-disclaimer est-disclaimer">
              Final quotation may vary based on custom project requirements.
            </p>

            <div class="summary-actions est-actions">
              <a href="#contact" class="btn btn-primary est-btn-primary">
                Book Free Consultation
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </a>
              <button type="button" class="btn btn-outline est-btn-download" id="downloadEstimate">
                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                Download Estimate
              </button>
              <a class="btn btn-whatsapp est-btn-whatsapp" id="whatsappEstimate" href="{{ $waHref }}" target="_blank" rel="noopener noreferrer">
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                Send on WhatsApp
              </a>
            </div>

            <div class="est-trust-footer">
              <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Private Estimate</span>
              <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> No Payment Required</span>
            </div>
          </aside>
          </div>
        </div>
      </div>
    </section>

    <section class="section support-section support-premium support-premium-v2" id="support">
      <div class="container">
        <div class="support-intro reveal">
          <p class="support-kicker">After Launch</p>
          <h2>We Don't Just Deliver. We Help You Succeed.</h2>
          <p class="support-lead">From setup to training and long-term support, we stay with you after your website goes live.</p>
          <ul class="support-trust" aria-label="After launch promises">
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Website Setup</li>
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Free Training</li>
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Support</li>
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Future Assistance</li>
          </ul>
        </div>

        <div class="support-timeline reveal" aria-label="After launch journey">
          <div class="support-rail" aria-hidden="true">
            <span class="support-rail-track"></span>
            <span class="support-rail-progress"></span>
          </div>

          <ol class="support-milestones">
            <li class="support-milestone start">
              <span class="support-node"><i class="fa-solid fa-rocket" aria-hidden="true"></i></span>
              <span class="support-node-label">Website Ready</span>
            </li>
            <li class="support-milestone">
              <span class="support-node"><i class="fa-solid fa-gear" aria-hidden="true"></i></span>
              <span class="support-node-label">Installation</span>
              <span class="support-node-meta">1 Day</span>
            </li>
            <li class="support-milestone">
              <span class="support-node"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
              <span class="support-node-label">Free Training</span>
            </li>
            <li class="support-milestone">
              <span class="support-node"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
              <span class="support-node-label">Free Support</span>
            </li>
            <li class="support-milestone end">
              <span class="support-node"><i class="fa-solid fa-heart" aria-hidden="true"></i></span>
              <span class="support-node-label">Lifetime Care</span>
            </li>
          </ol>

          <ol class="support-journey">
            <li class="support-journey-step step-install">
              <span class="support-journey-icon" aria-hidden="true"><i class="fa-solid fa-rocket"></i></span>
              <div class="support-journey-head">
                <h3>Installation</h3>
                <span class="support-journey-badge">Included in Every Package</span>
              </div>
              <ul>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Website Live Setup</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Domain Connection</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Hosting Setup</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Final Testing</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Go Live</li>
              </ul>
              <span class="support-journey-foot">Completed in 1 Day</span>
            </li>

            <li class="support-journey-step step-train">
              <span class="support-journey-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
              <div class="support-journey-head">
                <h3>Free Training</h3>
                <span class="support-journey-badge gold">Free Training</span>
              </div>
              <ul>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Admin Panel Training</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Product Upload Guide</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Banner Update Guide</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Orders</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Live Q&amp;A Session</li>
              </ul>
              <span class="support-journey-foot">1-on-1 Online Session Included</span>
            </li>

            <li class="support-journey-step step-support">
              <span class="support-journey-icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
              <div class="support-journey-head">
                <h3>Free Support</h3>
                <span class="support-journey-badge green">After Launch Support</span>
              </div>
              <ul>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Support</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Bug Fixes</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Technical Help</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Minor Updates</li>
              </ul>
              <span class="support-journey-foot">Included Free After Launch</span>
            </li>
          </ol>
        </div>

        <div class="support-plans support-plans-v2 reveal">
          <div class="support-plans-intro">
            <p class="support-plans-kicker">Free Support</p>
            <h3 class="support-plans-title">Choose Your Free Support Period</h3>
            <p class="support-plans-lead">Every package includes free after-launch support. Pick how many free days you want - no charges during this period.</p>
            <ul class="support-plans-chips" aria-label="Support highlights">
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Support</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> Training Included</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i> Completely Free</li>
            </ul>
          </div>

          <div class="support-plans-grid">
            <article class="support-plan-card plan-starter">
              <p class="support-plan-tag">Budget Friendly</p>
              <h4 class="support-plan-name">Starter Care</h4>
              <div class="support-counter" aria-label="7 days free support">
                <strong>7</strong>
                <span>Days</span>
              </div>
              <p class="support-plan-free">Free Support</p>
              <p class="support-plan-pitch">Perfect for launching your website.</p>
              <ul class="support-plan-benefits">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> WhatsApp Support</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Minor Bug Fixes</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Basic Guidance</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Setup Assistance</li>
              </ul>
              <div class="support-plan-ideal">
                <span>Ideal For</span>
                <em>New Businesses</em>
                <em>Small Stores</em>
              </div>
              <p class="support-plan-included"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Included with your package</p>
              <a class="support-plan-cta" href="#calculator" data-select-plan="starter">Basic Coverage</a>
            </article>

            <article class="support-plan-card plan-business is-featured">
              <p class="support-plan-tag popular">Most Popular</p>
              <h4 class="support-plan-name">Business Care</h4>
              <div class="support-counter" aria-label="15 days free support">
                <strong>15</strong>
                <span>Days</span>
              </div>
              <p class="support-plan-free">Free Support</p>
              <p class="support-plan-pitch">Extra time to help you manage your business confidently.</p>
              <ul class="support-plan-benefits">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Priority WhatsApp</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Minor Updates</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Admin Guidance</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Product Upload Help</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Banner Update Help</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Store Management Help</li>
              </ul>
              <div class="support-plan-ideal">
                <span>Ideal For</span>
                <em>Growing Businesses</em>
              </div>
              <div class="support-plan-social">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                8 out of 10 customers choose Business Care
              </div>
              <p class="support-plan-included"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Included with your package</p>
              <a class="support-plan-cta primary" href="#calculator" data-select-plan="business">Recommended Choice</a>
            </article>

            <article class="support-plan-card plan-pro">
              <p class="support-plan-tag best">Best Coverage</p>
              <h4 class="support-plan-name">Priority Care</h4>
              <div class="support-counter" aria-label="30 days free support">
                <strong>30</strong>
                <span>Days</span>
              </div>
              <p class="support-plan-free">Free Support</p>
              <p class="support-plan-pitch">Complete assistance while your business grows.</p>
              <ul class="support-plan-benefits">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Priority Assistance</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Technical Consultation</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Performance Guidance</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Faster Resolution</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Advanced Troubleshooting</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Store Optimization Tips</li>
              </ul>
              <div class="support-plan-ideal">
                <span>Ideal For</span>
                <em>Growing Online Stores</em>
                <em>Multi-branch Business</em>
              </div>
              <div class="support-plan-upgrade">
                <strong><i class="fa-solid fa-rocket" aria-hidden="true"></i> Only ₹5,000 more than Business</strong>
                <span>Double free support days + priority assistance</span>
              </div>
              <p class="support-plan-included"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Included with your package</p>
              <a class="support-plan-cta gold" href="#calculator" data-select-plan="professional">Complete Support Experience</a>
            </article>
          </div>

          <div class="support-compare-strip" aria-label="Support days comparison">
            <button type="button" class="support-compare-item" data-select-plan="starter">
              <span>Starter</span>
              <strong>7 Days Free</strong>
            </button>
            <button type="button" class="support-compare-item is-active" data-select-plan="business">
              <span>Business</span>
              <strong>15 Days Free</strong>
            </button>
            <button type="button" class="support-compare-item" data-select-plan="professional">
              <span>Priority</span>
              <strong>30 Days Free</strong>
            </button>
          </div>

          <div class="support-later-note">
            <p>
              <strong>Need help after your free support ends?</strong>
              Additional support is always available - completely optional.
            </p>
            <button type="button" class="support-later-link" id="openSupportRates" data-open-support-rates>
              View Optional Support <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <div class="support-after reveal" id="supportAfter">
          <div class="support-after-card">
            <div class="support-after-intro">
              <p class="support-plans-kicker optional">Additional Support (Optional)</p>
              <h3>Support After Free Period</h3>
              <p>Only if you need help later. These optional services are not part of your free support period.</p>
            </div>

            <div class="support-after-meta">
              <div>
                <span>Response Time</span>
                <strong>Usually within a few hours</strong>
              </div>
              <div>
                <span>Availability</span>
                <strong>Mon–Sat · 10 AM – 7 PM</strong>
              </div>
              <div>
                <span>How it works</span>
                <strong>Pay only when you need help</strong>
              </div>
            </div>

            <div class="support-rate-table">
              <div class="support-rate-block">
                <h4><i class="fa-solid fa-laptop" aria-hidden="true"></i> Online Support</h4>
                <p class="support-rate-hint">Remote help via WhatsApp / screen share</p>
                <ul>
                  <li><span>Starter</span><strong>₹499 <em>/ request</em></strong></li>
                  <li><span>Business</span><strong>₹399 <em>/ request</em></strong></li>
                  <li><span>Professional</span><strong>₹299 <em>/ request</em></strong></li>
                </ul>
              </div>
              <div class="support-rate-block">
                <h4><i class="fa-solid fa-location-dot" aria-hidden="true"></i> On-site Visit</h4>
                <p class="support-rate-hint">In-person visit when remote help isn’t enough</p>
                <ul>
                  <li><span>Starter</span><strong>₹1,499 <em>/ visit</em></strong></li>
                  <li><span>Business</span><strong>₹1,199 <em>/ visit</em></strong></li>
                  <li><span>Professional</span><strong>₹999 <em>/ visit</em></strong></li>
                </ul>
              </div>
            </div>

            <p class="support-after-foot">No obligation · Request only when you need it</p>
          </div>
        </div>

      </div>
    </section>

    <section class="section pricing-faq-section" id="pricing-faq">
      <div class="container">
        <p class="support-kicker">Simple answers</p>
        <h2>Questions clients ask before they book.</h2>
        <p class="pricing-faq-lead">Short answers. No jargon.</p>
        <div class="pricing-faq-list">
          <details>
            <summary>Which website should I start with?<span aria-hidden="true">+</span></summary>
            <p>Most shops start with Business. It has offers, search and 15 days free support. Starter is enough if you only need WhatsApp selling. Professional is for full online orders.</p>
          </details>
          <details>
            <summary>Is GST included in this total?<span aria-hidden="true">+</span></summary>
            <p>No. GST is extra, if applicable. Domain and hosting yearly fees are shown separately. There are no hidden package charges.</p>
          </details>
          <details>
            <summary>When do I pay?<span aria-hidden="true">+</span></summary>
            <p>Booking amount starts the project. Balance is due before launch. Exact milestones are confirmed on consultation.</p>
          </details>
          <details>
            <summary>How long does a website take?<span aria-hidden="true">+</span></summary>
            <p>Starter 5–7 days, Business 7–10 days, Professional 10–15 days after we have your content and briefing.</p>
          </details>
          <details>
            <summary>Do I need an app now?<span aria-hidden="true">+</span></summary>
            <p>No. Start with the website. Add Android later if most customers order from phones.</p>
          </details>
        </div>
        <div class="pricing-faq-cta">
          <a class="btn btn-primary" href="#contact">Book Free Consultation</a>
          <a class="btn btn-whatsapp" href="{{ $waHref }}" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a>
        </div>
      </div>
    </section>

    <section class="section cta-section cta-premium-v2" id="contact">
      <div class="container cta-container">
        <div class="cta-panel reveal">
          <span class="cta-orb cta-orb-a" aria-hidden="true"></span>
          <span class="cta-orb cta-orb-b" aria-hidden="true"></span>

          <div class="cta-panel-grid">
            <div class="cta-copy">
              <span class="cta-badge"><i class="fa-solid fa-comments" aria-hidden="true"></i> Let's Build Your Business</span>
              <h2>Let's Build Your Business Online - Starting Today.</h2>
              <p>
                Whether you're planning a new website, need technical support,
                or want to grow your online store, our experts are ready to help.
              </p>

              <ul class="cta-trust" aria-label="Why contact us">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Free Consultation</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Quick Response</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Expert Guidance</li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Transparent Pricing</li>
              </ul>

              <div class="cta-meta">
                <div>
                  <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                  <strong>30 min</strong>
                  <span>Average Response</span>
                </div>
                <div>
                  <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                  <strong>Mon–Sat</strong>
                  <span>Working Days</span>
                </div>
                <div>
                  <i class="fa-regular fa-clock" aria-hidden="true"></i>
                  <strong>10AM–7PM</strong>
                  <span>Working Hours</span>
                </div>
              </div>
            </div>

            <div class="cta-actions-grid" aria-label="Contact options">
              <a class="cta-action whatsapp is-primary" id="contactWhatsapp" href="{{ $waHref }}" target="_blank" rel="noopener noreferrer">
                <span class="cta-action-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
                <span class="cta-action-body">
                  <span class="cta-action-title">
                    <strong>WhatsApp</strong>
                    <span class="cta-rec-badge"><i class="fa-solid fa-circle" aria-hidden="true"></i> Recommended</span>
                  </span>
                  <em>Usually replies in 5–10 minutes</em>
                </span>
                <span class="cta-action-btn">Chat Now</span>
              </a>

              <a class="cta-action call" href="{{ $telHref }}" aria-label="Call CEBINOVA">
                <span class="cta-action-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                <span class="cta-action-body">
                  <strong>Call Us</strong>
                  <em>Talk directly · immediate discussion</em>
                </span>
                <span class="cta-action-btn">Call</span>
              </a>

              <a class="cta-action email" href="mailto:{{ $email }}?subject=Project%20Cost%20Estimate%20Discussion">
                <span class="cta-action-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                <span class="cta-action-body">
                  <strong>Email</strong>
                  <em>Share project requirements in detail</em>
                </span>
                <span class="cta-action-btn">Email</span>
              </a>

              <a class="cta-action schedule" href="{{ $scheduleHref }}" target="_blank" rel="noopener noreferrer">
                <span class="cta-action-icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></span>
                <span class="cta-action-body">
                  <strong>Schedule Meeting</strong>
                  <em>30-minute free consultation</em>
                </span>
                <span class="cta-action-btn">Schedule</span>
              </a>
            </div>
          </div>

          <ul class="cta-strip" aria-label="Trust points">
            <li>
              <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
              <strong>Trusted</strong>
              <span>Transparent pricing &amp; guidance</span>
            </li>
            <li>
              <i class="fa-solid fa-handshake" aria-hidden="true"></i>
              <strong>Long-term Support</strong>
              <span>No pressure. Honest advice.</span>
            </li>
            <li>
              <i class="fa-solid fa-bolt" aria-hidden="true"></i>
              <strong>Fast Response</strong>
              <span>Most replies within 30 minutes</span>
            </li>
          </ul>
        </div>
      </div>
    </section>
<div class="modal" id="featureModal" hidden>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <button type="button" class="modal-close" data-close-modal aria-label="Close">&times;</button>

      <div class="compare-modal-head">
        <div class="compare-modal-title-row">
          <h3 id="modalTitle">Feature Comparison</h3>
          <ul class="compare-trust" aria-label="Comparison benefits">
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> 74 Features</li>
            <li><i class="fa-solid fa-check" aria-hidden="true"></i> No Hidden Charges</li>
          </ul>
        </div>
      </div>

      <div class="compare-table-wrap">
        <table class="compare-table">
          <thead>
            <tr>
              <th scope="col">Feature</th>
              <th scope="col">
                <span class="plan-col-head">
                  <span class="plan-col-name">Starter</span>
                  <span class="plan-col-price">₹14,999</span>
                </span>
              </th>
              <th scope="col" class="col-business">
                <span class="plan-col-head">
                  <span class="plan-col-badge popular"><i class="fa-solid fa-star" aria-hidden="true"></i> Most Popular</span>
                  <span class="plan-col-name">Business</span>
                  <span class="plan-col-price">₹24,999</span>
                </span>
              </th>
              <th scope="col" class="col-pro">
                <span class="plan-col-head">
                  <span class="plan-col-badge complete"><i class="fa-solid fa-crown" aria-hidden="true"></i> Best Choice</span>
                  <span class="plan-col-name">Professional</span>
                  <span class="plan-col-price">₹29,999</span>
                  <span class="plan-col-upgrade">Only ₹5,000 more than Business</span>
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="group-start">
              <td><span class="feature-cell" data-tip="Your store looks sharp on every screen size."><span class="feature-group-label"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Core Features</span><strong>Responsive Website</strong><span class="feature-tags"><em>Mobile</em><em>Tablet</em><em>Desktop</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Update products and store settings without coding."><strong>Admin Panel</strong><span class="feature-tags"><em>Control</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Customers order via WhatsApp in one tap."><strong>WhatsApp Orders</strong><span class="feature-tags"><em>Orders</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Returning customers can log in securely."><strong>Customer Login</strong><span class="feature-tags"><em>Accounts</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Customers can add items and checkout easily."><strong>Shopping Cart</strong><span class="feature-tags"><em>Checkout</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Customers can save products for later."><strong>Wishlist</strong><span class="feature-tags"><em>Engagement</em></span><span class="feature-importance optional"><span class="stars" aria-hidden="true">★★★</span> Optional</span></span></td>
              <td><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>

            <tr class="group-start">
              <td><span class="feature-cell" data-tip="Customers find products in seconds."><span class="feature-group-label"><i class="fa-solid fa-store" aria-hidden="true"></i> Store Management</span><strong>Product Search</strong><span class="feature-tags"><em>Search</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Filter products by category and price."><strong>Product Filters</strong><span class="feature-tags"><em>Browse</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Change homepage offers anytime."><strong>Banner Management</strong><span class="feature-tags"><em>Offers</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Showcase your best-selling products."><strong>Featured Products</strong><span class="feature-tags"><em>Showcase</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>

            <tr class="group-start">
              <td><span class="feature-cell" data-tip="Manage all online orders in one dashboard."><span class="feature-group-label"><i class="fa-solid fa-box" aria-hidden="true"></i> Orders &amp; Growth</span><strong>Online Order Management</strong><span class="feature-tags"><em>Orders</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Share live order status with customers."><strong>Order Tracking</strong><span class="feature-tags"><em>Status</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Run discount codes for promotions."><strong>Coupons</strong><span class="feature-tags"><em>Marketing</em></span><span class="feature-importance optional"><span class="stars" aria-hidden="true">★★★</span> Optional</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="See what sells and where growth comes from."><strong>Sales Analytics</strong><span class="feature-tags"><em>Insights</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Get alerts for new orders and updates."><strong>Notifications</strong><span class="feature-tags"><em>Alerts</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>

            <tr class="group-start">
              <td><span class="feature-cell" data-tip="Quick training so you can manage the store yourself."><span class="feature-group-label"><i class="fa-solid fa-headset" aria-hidden="true"></i> Support &amp; Security</span><strong>Admin Panel Training</strong><span class="feature-tags"><em>Training</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Technical help for 15 days after launch."><strong>15 Days Technical Support</strong><span class="feature-tags"><em>Support</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Small text/image updates during support."><strong>Minor Content Updates</strong><span class="feature-tags"><em>Updates</em></span><span class="feature-importance optional"><span class="stars" aria-hidden="true">★★★</span> Optional</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Protect store data with basic backups."><strong>Basic Backup Setup</strong><span class="feature-tags"><em>Safety</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Faster WhatsApp replies when you need help."><strong>Priority WhatsApp Support</strong><span class="feature-tags"><em>WhatsApp</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge biz-plus" aria-label="Available in Business Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Business+</span></td>
              <td class="col-business"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Full admin walkthrough for your team."><strong>Complete Admin Training</strong><span class="feature-tags"><em>Training</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Priority support for 30 days after launch."><strong>30 Days Priority Support</strong><span class="feature-tags"><em>Support</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Faster pages for better customer experience."><strong>Performance Optimization</strong><span class="feature-tags"><em>Speed</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Scheduled backups for safer operations."><strong>Regular Backup Configuration</strong><span class="feature-tags"><em>Backup</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Extra security layers for your store."><strong>Security Hardening</strong><span class="feature-tags"><em>Security</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Faster fixes when something breaks."><strong>Priority Bug Fixes</strong><span class="feature-tags"><em>Fixes</em></span><span class="feature-importance recommended"><span class="stars" aria-hidden="true">★★★★</span> Recommended</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Hands-on help when you go live."><strong>Launch Assistance</strong><span class="feature-tags"><em>Launch</em></span><span class="feature-importance essential"><span class="stars" aria-hidden="true">★★★★★</span> Essential</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
            <tr>
              <td><span class="feature-cell" data-tip="Expert guidance for next growth steps."><strong>Technical Consultation</strong><span class="feature-tags"><em>Advice</em></span><span class="feature-importance growth"><span class="stars" aria-hidden="true">★★★★★</span> Business Growth</span></span></td>
              <td><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-business"><span class="status-badge pro-only" aria-label="Available in Professional Plan"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pro Only</span></td>
              <td class="col-pro"><span class="status-yes" aria-label="Included"><i class="fa-solid fa-check"></i></span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="compare-modal-foot">
        <div class="compare-modal-cta">
          <p class="compare-modal-cta-copy">Still not sure? We'll recommend the best plan.</p>
          <div class="support-cta-actions">
            <a class="btn btn-whatsapp" href="{{ $waHref }}" target="_blank" rel="noopener noreferrer">
              <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp
            </a>
            <a href="{{ $telHref }}" class="btn btn-secondary">
              <i class="fa-solid fa-phone" aria-hidden="true"></i> Book Free Call
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="print-estimate" id="printEstimate" aria-hidden="true">
    <div class="print-sheet print-sheet-v2" id="printBody">
      <!-- Filled dynamically by script.js for a sharp, premium quotation PDF -->
    </div>
  </section>

  <button type="button" class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
  </button>

  <div class="js-price-sticky price-sticky" aria-label="Selected estimate">
    <div class="price-sticky-copy">
      <p class="js-price-sticky-name price-sticky-name">Business website · recommended path</p>
      <p class="js-price-sticky-price price-sticky-price" id="mobileGrandTotal">₹28,798</p>
    </div>
    <div class="price-sticky-actions">
      <a class="price-sticky-cta" href="#contact">Book Free Consultation</a>
      <a class="price-sticky-wa" href="{{ $waHref }}" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a>
    </div>
  </div>
</div>

    @include('sections.home.cta')
@endsection

@push('scripts')
  <script>
    window.CEBINOVA_WHATSAPP = @json($phoneDigits);
    window.CEBINOVA_EMAIL = @json($email);
    window.CEBINOVA_CONTACT = @json($enquiry);
  </script>
  <script src="{{ asset('assets/solutions/kirana-pricing/js/script.js') }}"></script>
@endpush