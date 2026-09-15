/**
 * SARVIX Pricing - Interactive Project Cost Calculator
 */

(function () {
  "use strict";

  const root = document.querySelector(".kirana-pricing") || document.body;
  const WHATSAPP_NUMBER = window.SARVIX_WHATSAPP || root.getAttribute("data-wa") || "";
  const CONTACT_FALLBACK = window.SARVIX_CONTACT || root.getAttribute("data-contact") || "/contact";

  /** Easy to update later - marketing / plan meta */
  const PLAN_META = {
    starter: { support: "7 Days", training: "Free" },
    business: { support: "15 Days", training: "Free" },
    professional: { support: "30 Days", training: "Free" }
  };

  const APP_PRICES = {
    android: 24999,
    ios: 34999,
    bundle: 59998
  };

  const navToggle = document.getElementById("navToggle");
  const primaryNav = document.getElementById("primaryNav");
  const scrollProgress = document.getElementById("scrollProgress");
  const backToTop = document.getElementById("backToTop");
  const yearEl = document.getElementById("currentYear");
  const sections = root.querySelectorAll("section[id]");
  const revealEls = root.querySelectorAll(".reveal");
  const buttons = root.querySelectorAll(".btn");

  const form = document.getElementById("costCalculator");
  const summaryLines = document.getElementById("summaryLines");
  const invoiceBody = document.getElementById("invoiceBody");
  const grandTotalEl = document.getElementById("grandTotal");
  const grandTotalCard = document.getElementById("grandTotalCard");
  const subTotalEl = document.getElementById("subTotal");
  const priceSummaryRows = document.getElementById("priceSummaryRows");
  const estimateSavings = document.getElementById("estimateSavings");
  const estimateSavingsLabel = document.getElementById("estimateSavingsLabel");
  const estimateSavingsValue = document.getElementById("estimateSavingsValue");
  const selectedPlanBadge = document.getElementById("selectedPlanBadge");
  const bundleHint = document.getElementById("bundleHint");
  const appBothBanner = document.getElementById("appBothBanner");
  const appRecommend = document.getElementById("appRecommend");
  const appSelectStatus = document.getElementById("appSelectStatus");
  const appSelectGrid = document.querySelector(".app-select-grid");
  const domainRecommendedBadge = document.getElementById("domainRecommendedBadge");
  const hostingRecommend = document.getElementById("hostingRecommend");
  const androidInput = document.getElementById("optAndroid");
  const iosInput = document.getElementById("optIos");
  const whatsappEstimate = document.getElementById("whatsappEstimate");
  const contactWhatsapp = document.getElementById("contactWhatsapp");
  const floatWhatsapp = document.getElementById("floatWhatsapp");
  const downloadBtn = document.getElementById("downloadEstimate");
  const printBody = document.getElementById("printBody");
  const recommendedBadge = document.getElementById("recommendedBadge");
  const progressItems = document.querySelectorAll("#calcProgress li");
  const progressFill = document.getElementById("calcProgressFill");
  const progressPct = document.getElementById("calcProgressPct");
  const liveProgressSummary = document.getElementById("liveProgressSummary");

  const featureModal = document.getElementById("featureModal");

  let latestEstimate = { lines: [], total: 0, message: "" };
  let summaryAnimTimer = null;
  let totalAnimFrame = null;
  let displayedTotal = 0;

  if (yearEl) yearEl.textContent = String(new Date().getFullYear());

  function setNavOpen(isOpen) {
    if (!primaryNav || !navToggle) return;
    primaryNav.classList.toggle("open", isOpen);
    navToggle.setAttribute("aria-expanded", String(isOpen));
    navToggle.setAttribute("aria-label", isOpen ? "Close menu" : "Open menu");
  }

  if (navToggle) {
    navToggle.addEventListener("click", function () {
      setNavOpen(!primaryNav.classList.contains("open"));
    });
  }

  document.querySelectorAll(".nav-link, .nav-cta").forEach(function (link) {
    link.addEventListener("click", function () { setNavOpen(false); });
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      setNavOpen(false);
      closeModal();
    }
  });

  root.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      const id = anchor.getAttribute("href");
      if (!id || id === "#") return;
      const target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: "smooth", block: "start" });
      history.pushState(null, "", id);
    });
  });

  function onScroll() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    if (scrollProgress) {
      scrollProgress.style.width = (docHeight > 0 ? (scrollTop / docHeight) * 100 : 0) + "%";
    }
    if (backToTop) backToTop.classList.toggle("visible", scrollTop > 320);

    let currentId = "";
    sections.forEach(function (section) {
      if (scrollTop >= section.offsetTop - 100) currentId = section.getAttribute("id");
    });
    root.querySelectorAll(".nav-link").forEach(function (link) {
      link.classList.toggle("active", link.getAttribute("href") === "#" + currentId);
    });
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  if (backToTop) {
    backToTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -30px 0px" });
    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("visible"); });
  }

  buttons.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      const rect = btn.getBoundingClientRect();
      const ripple = document.createElement("span");
      const size = Math.max(rect.width, rect.height);
      ripple.className = "ripple";
      ripple.style.width = size + "px";
      ripple.style.height = size + "px";
      ripple.style.left = e.clientX - rect.left - size / 2 + "px";
      ripple.style.top = e.clientY - rect.top - size / 2 + "px";
      btn.appendChild(ripple);
      window.setTimeout(function () { ripple.remove(); }, 650);
    });
  });

  function openModal() {
    if (!featureModal) return;
    featureModal.hidden = false;
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    if (!featureModal) return;
    featureModal.hidden = true;
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".see-features").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      openModal();
    });
  });

  document.querySelectorAll("[data-close-modal]").forEach(function (el) {
    el.addEventListener("click", closeModal);
  });

  function formatINR(amount) {
    return "₹" + Number(amount || 0).toLocaleString("en-IN", {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
  }

  function parseMoney(value) {
    const n = parseInt(String(value).replace(/[^\d-]/g, ""), 10);
    return Number.isFinite(n) && n >= 0 ? n : 0;
  }

  function updateProgress() {
    if (!form || !progressItems.length) return;
    const website = form.querySelector('input[name="website"]:checked');
    const domain = form.querySelector('input[name="domainOption"]:checked');
    const hosting = form.querySelector('input[name="hostingOption"]:checked');
    const androidOn = !!(androidInput && androidInput.checked);
    const iosOn = !!(iosInput && iosInput.checked);
    // Apps is optional - viewing/choosing counts as complete once website is selected
    const done = [!!website, !!website, !!domain, !!hosting, !!website];

    let currentIndex = done.findIndex(function (d) { return !d; });
    if (currentIndex < 0) currentIndex = done.length - 1;

    progressItems.forEach(function (item, index) {
      item.classList.remove("active", "done", "current");
      if (done[index]) item.classList.add("done");
      if (index === currentIndex) item.classList.add("active", "current");
    });

    const completedCount = done.filter(Boolean).length;
    const pct = Math.round((completedCount / done.length) * 100);
    if (progressFill) progressFill.style.width = pct + "%";
    if (progressPct) progressPct.textContent = pct + "%";

    if (liveProgressSummary) {
      const chips = [];
      if (website) {
        chips.push("Website · " + (website.getAttribute("data-label") || "Selected"));
      }
      if (androidOn && iosOn) chips.push("Android + iOS Apps");
      else if (androidOn) chips.push("Android App Added");
      else if (iosOn) chips.push("iPhone App Added");
      else if (website) chips.push("Apps · Optional");

      if (domain) {
        const domainLabel = domain.getAttribute("data-label") || "Domain";
        chips.push(domainLabel === "Already have" ? "Existing Domain" : "New Domain");
      }
      if (hosting) {
        const hostLabel = hosting.getAttribute("data-label") || "Hosting";
        chips.push(hostLabel === "Already have" ? "Existing Hosting" : hostLabel);
      }
      if (website) {
        chips.push("Support · " + (website.getAttribute("data-support") || "Included"));
      }

      liveProgressSummary.innerHTML = chips.map(function (text) {
        return "<li><i class=\"fa-solid fa-check\" aria-hidden=\"true\"></i> " + text + "</li>";
      }).join("");
    }
  }

  function updateAppSelectorUI() {
    const website = form ? form.querySelector('input[name="website"]:checked') : null;
    const plan = website ? website.value : "starter";
    const androidOn = !!(androidInput && androidInput.checked);
    const iosOn = !!(iosInput && iosInput.checked);
    const anyApp = androidOn || iosOn;

    if (appBothBanner) appBothBanner.hidden = !(androidOn && iosOn);
    if (appSelectGrid) appSelectGrid.classList.toggle("both-selected", androidOn && iosOn);

    var androidUpsell = document.getElementById("androidUpsell");
    var iosUpsell = document.getElementById("iosUpsell");
    /* Contextual upsell: show on the opposite card when only one app is selected */
    if (androidUpsell) androidUpsell.hidden = !(iosOn && !androidOn);
    if (iosUpsell) iosUpsell.hidden = !(androidOn && !iosOn);

    if (appSelectStatus) {
      appSelectStatus.textContent = anyApp ? "Included in Estimate" : "Skip for Now";
      appSelectStatus.classList.toggle("is-included", anyApp);
    }

    if (appRecommend) {
      if (androidOn && iosOn) {
        appRecommend.textContent = "💡 Perfect - Maximum customer reach with Android + iPhone.";
      } else if (androidOn) {
        appRecommend.textContent = "💡 Tip: Add iPhone for only ₹10,000 more and complete your app bundle.";
      } else if (iosOn) {
        appRecommend.textContent = "💡 Tip: Add Android to reach 95%+ Indian users.";
      } else if (plan === "professional") {
        appRecommend.textContent = "💡 Recommendation: Most Professional businesses choose both Android & iPhone apps.";
      } else if (plan === "business") {
        appRecommend.textContent = "💡 Tip: Start with Android for reach, add iPhone for a premium brand edge.";
      } else {
        appRecommend.textContent = "💡 Android is enough to start for most small businesses.";
      }
    }

    const androidBadge = document.querySelector('.app-pick[data-app="android"] .app-badge');
    const iosBadge = document.querySelector('.app-pick[data-app="ios"] .app-badge');
    if (androidOn && iosOn) {
      if (androidBadge) androidBadge.innerHTML = '<i class="fa-solid fa-rocket" aria-hidden="true"></i> Maximum Reach';
      if (iosBadge) iosBadge.innerHTML = '<i class="fa-solid fa-rocket" aria-hidden="true"></i> Maximum Reach';
    } else {
      if (androidBadge) androidBadge.innerHTML = '<i class="fa-solid fa-star" aria-hidden="true"></i> Most Popular';
      if (iosBadge) iosBadge.innerHTML = '<i class="fa-solid fa-crown" aria-hidden="true"></i> Premium Choice';
    }

    if (domainRecommendedBadge) {
      domainRecommendedBadge.hidden = !(plan === "business" || plan === "professional");
    }

    // Hosting badges stay static (Affordable Choice / Best Performance).
    // Contextual tip bar removed in hosting v2 UI.
    if (hostingRecommend) hostingRecommend.hidden = true;

    document.querySelectorAll(".support-compare-item").forEach(function (item) {
      item.classList.toggle("is-active", item.getAttribute("data-select-plan") === plan);
    });
  }

  function setGrandTotalText(amount) {
    const text = formatINR(amount);
    if (grandTotalEl) grandTotalEl.textContent = text;
    const mobileTotal = document.getElementById("mobileGrandTotal");
    if (mobileTotal) mobileTotal.textContent = text;
  }

  function animateTotal(toAmount) {
    if (!grandTotalEl) return;
    const from = displayedTotal;
    const to = Number(toAmount) || 0;
    if (from === to) {
      setGrandTotalText(to);
      return;
    }
    if (totalAnimFrame) cancelAnimationFrame(totalAnimFrame);

    if (grandTotalCard) {
      grandTotalCard.classList.add("is-glow");
      window.setTimeout(function () {
        grandTotalCard.classList.remove("is-glow");
      }, 720);
    }

    const reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion) {
      displayedTotal = to;
      setGrandTotalText(to);
      return;
    }

    const start = performance.now();
    const duration = 420;
    function frame(now) {
      const t = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - t, 3);
      const value = Math.round(from + (to - from) * eased);
      displayedTotal = value;
      setGrandTotalText(value);
      if (t < 1) totalAnimFrame = requestAnimationFrame(frame);
      else displayedTotal = to;
    }
    totalAnimFrame = requestAnimationFrame(frame);
  }

  function buildEstimate() {
    const website = form.querySelector('input[name="website"]:checked');
    const websiteLabel = website ? website.getAttribute("data-label") : "Starter";
    const websiteKey = website ? website.value : "starter";
    const websitePrice = website ? parseMoney(website.getAttribute("data-price")) : 0;
    const delivery = website ? website.getAttribute("data-delivery") : "5–7 Days";
    const meta = PLAN_META[websiteKey] || PLAN_META.starter;

    const androidOn = !!(androidInput && androidInput.checked);
    const iosOn = !!(iosInput && iosInput.checked);

    const domainOpt = form.querySelector('input[name="domainOption"]:checked');
    const hostingOpt = form.querySelector('input[name="hostingOption"]:checked');

    const domain = domainOpt ? parseMoney(domainOpt.value) : 0;
    const hosting = hostingOpt ? parseMoney(hostingOpt.value) : 0;

    if (bundleHint) bundleHint.hidden = true;

    const lines = [];
    const displayRows = [];
    let total = websitePrice + domain + hosting;
    let appSavings = 0;
    let bundleSelected = false;
    let appsTotal = 0;
    let androidOnly = false;
    let iosOnly = false;

    lines.push({ label: "Package · " + websiteLabel, amount: websitePrice, billing: "fixed" });
    displayRows.push({
      id: "website",
      icon: "fa-desktop",
      iconTone: "",
      title: "Website",
      detail: websiteLabel + " Plan",
      amount: websitePrice,
      selected: true,
      billing: "fixed"
    });

    if (androidOn && iosOn) {
      const separate = APP_PRICES.android + APP_PRICES.ios;
      lines.push({ label: "Android + iOS Apps", amount: APP_PRICES.bundle, billing: "fixed" });
      total += APP_PRICES.bundle;
      appsTotal = APP_PRICES.bundle;
      appSavings = Math.max(0, separate - APP_PRICES.bundle);
      bundleSelected = true;
      displayRows.push({
        id: "apps-bundle",
        icon: "fa-mobile-screen-button",
        iconBrand: false,
        iconTone: "green",
        title: "Android + iPhone",
        detail: "Both selected · Bundle",
        amount: APP_PRICES.bundle,
        selected: true,
        billing: "fixed"
      });
    } else {
      lines.push({ label: "Android App", amount: androidOn ? APP_PRICES.android : null, billing: "fixed" });
      lines.push({ label: "iOS App", amount: iosOn ? APP_PRICES.ios : null, billing: "fixed" });
      if (androidOn) {
        total += APP_PRICES.android;
        appsTotal += APP_PRICES.android;
        androidOnly = true;
      }
      if (iosOn) {
        total += APP_PRICES.ios;
        appsTotal += APP_PRICES.ios;
        iosOnly = true;
      }
      displayRows.push({
        id: "android",
        icon: "fa-android",
        iconBrand: true,
        iconTone: androidOn ? "green" : "",
        title: "Android App",
        detail: androidOn ? "Selected" : "Not selected",
        amount: androidOn ? APP_PRICES.android : null,
        selected: androidOn,
        priceText: androidOn ? null : "-",
        billing: "fixed"
      });
      displayRows.push({
        id: "ios",
        icon: "fa-apple",
        iconBrand: true,
        iconTone: "",
        title: "iOS App",
        detail: iosOn ? "Selected" : "Not selected",
        amount: iosOn ? APP_PRICES.ios : null,
        selected: iosOn,
        priceText: iosOn ? null : "-",
        billing: "fixed"
      });
    }

    var domainHave = !!(domainOpt && domainOpt.getAttribute("data-label") === "Already have");
    var domainLabel = "Domain";
    var domainDetail = "Selected";
    if (domainHave) {
      domainLabel = "Domain (have)";
      domainDetail = "Already Have";
    } else if (domainOpt && domainOpt.getAttribute("data-provider")) {
      domainLabel = "Domain · " + domainOpt.getAttribute("data-provider");
      domainDetail = domainOpt.getAttribute("data-provider") + " · First year";
    } else if (domainOpt && domainOpt.getAttribute("data-label")) {
      domainDetail = domainOpt.getAttribute("data-label");
    }

    lines.push({
      label: domainLabel,
      amount: domain,
      billing: "yearly"
    });
    displayRows.push({
      id: "domain",
      icon: "fa-globe",
      iconTone: "gold",
      title: "Domain",
      detail: domainDetail,
      amount: domain,
      selected: true,
      included: domainHave,
      billing: "yearly"
    });

    var hostingHave = !!(hostingOpt && hostingOpt.value === "0");
    var hostingDetail = "Selected";
    if (hostingHave) {
      hostingDetail = "Already Have";
    } else if (hostingOpt && hostingOpt.getAttribute("data-label")) {
      hostingDetail = hostingOpt.getAttribute("data-label") + " · Per year";
    }

    lines.push({
      label: "Hosting" + (hostingHave ? " (have)" : ""),
      amount: hosting,
      billing: "yearly"
    });
    displayRows.push({
      id: "hosting",
      icon: "fa-cloud",
      iconTone: "",
      title: "Hosting",
      detail: hostingDetail,
      amount: hosting,
      selected: true,
      included: hostingHave,
      billing: "yearly"
    });

    let selection = websiteLabel + " Website package";
    if (androidOn && iosOn) selection += " with Android + iOS Apps";
    else if (androidOn) selection += " with Android App";
    else if (iosOn) selection += " with iOS App";

    const advance = Math.round(total * 0.4);

    const message =
      "Hello SARVIX, I selected the " +
      selection +
      ". My estimated investment is " +
      formatINR(total) +
      ". Expected delivery: " + delivery +
      ". Payment today (40% advance): " + formatINR(advance) +
      ". I would like to discuss this project.";

    const fixedTotal = websitePrice + appsTotal;
    const yearlyTotal = domain + hosting;

    latestEstimate = {
      lines: lines,
      displayRows: displayRows,
      breakdown: {
        website: websitePrice,
        apps: appsTotal,
        domain: domain,
        hosting: hosting,
        fixedTotal: fixedTotal,
        yearlyTotal: yearlyTotal
      },
      total: total,
      fixedTotal: fixedTotal,
      yearlyTotal: yearlyTotal,
      advance: advance,
      delivery: delivery,
      websiteLabel: websiteLabel,
      websiteKey: websiteKey,
      appSavings: appSavings,
      bundleSelected: bundleSelected,
      androidOnly: androidOnly,
      iosOnly: iosOnly,
      androidOn: androidOn,
      iosOn: iosOn,
      message: message,
      support: meta.support
    };

    return latestEstimate;
  }

  function pulseInvoice() {
    if (!invoiceBody) return;
    invoiceBody.classList.add("is-updating");
    if (summaryAnimTimer) window.clearTimeout(summaryAnimTimer);
    summaryAnimTimer = window.setTimeout(function () {
      invoiceBody.classList.remove("is-updating");
    }, 180);
  }

  function billingLabel(billing) {
    return billing === "yearly" ? "Yearly" : "Fixed";
  }

  function renderEstimateRows(rows) {
    if (!summaryLines) return;
    const prev = {};
    summaryLines.querySelectorAll("li[data-id]").forEach(function (li) {
      prev[li.getAttribute("data-id")] = li.getAttribute("data-sig") || "";
    });

    summaryLines.innerHTML = (rows || []).map(function (row) {
      const iconClass = (row.iconBrand ? "fa-brands " : "fa-solid ") + row.icon;
      const tone = row.iconTone ? " " + row.iconTone : "";
      const stateClass = row.selected && !row.included ? "is-selected" : (row.selected ? "is-selected" : "is-muted");
      const billing = row.billing === "yearly" ? "yearly" : "fixed";
      const chargeClass = billing === "yearly" ? "yearly" : "charge-fixed";
      const billingText = billingLabel(billing);
      let price = row.priceText;
      if (price == null) {
        if (row.amount === null) price = "-";
        else if (billing === "yearly" && row.amount > 0) price = formatINR(row.amount) + "<small>/yr</small>";
        else price = formatINR(row.amount);
      }
      const sig = row.detail + "|" + price + "|" + billing;
      const flash = prev[row.id] && prev[row.id] !== sig ? " is-flash" : "";
      return (
        '<li class="' + stateClass + flash + '" data-id="' + row.id + '" data-sig="' + sig + '">' +
          '<span class="est-line-icon' + tone + '" aria-hidden="true"><i class="' + iconClass + '"></i></span>' +
          '<span class="est-line-copy">' +
            "<strong>" + row.title + ' <span class="est-charge-tag ' + chargeClass + '">' + billingText + "</span></strong>" +
            "<em>" + row.detail + "</em>" +
          "</span>" +
          '<span class="est-line-price">' + price + "</span>" +
        "</li>"
      );
    }).join("");

    window.setTimeout(function () {
      summaryLines.querySelectorAll("li.is-flash").forEach(function (li) {
        li.classList.remove("is-flash");
      });
    }, 320);
  }

  function appsSummaryLabel(estimate) {
    if (estimate.androidOn && estimate.iosOn) return "Android + iPhone";
    if (estimate.androidOn) return "Android App";
    if (estimate.iosOn) return "iPhone App";
    return "Apps";
  }

  function renderPriceSummary(estimate) {
    if (!priceSummaryRows || !estimate.breakdown) return;
    const b = estimate.breakdown;
    const appsLabel = appsSummaryLabel(estimate);
    priceSummaryRows.innerHTML =
      '<div><span>Website <em class="est-charge-inline">Fixed</em></span><strong>' + formatINR(b.website) + "</strong></div>" +
      '<div><span>' + appsLabel + ' <em class="est-charge-inline">Fixed</em></span><strong>' + formatINR(b.apps) + "</strong></div>" +
      '<div><span>Domain <em class="est-charge-inline yearly">Yearly</em></span><strong>' + formatINR(b.domain) + (b.domain > 0 ? "<small>/yr</small>" : "") + "</strong></div>" +
      '<div><span>Hosting <em class="est-charge-inline yearly">Yearly</em></span><strong>' + formatINR(b.hosting) + (b.hosting > 0 ? "<small>/yr</small>" : "") + "</strong></div>" +
      '<div class="est-price-split"><span>Fixed total</span><strong>' + formatINR(b.fixedTotal || 0) + "</strong></div>" +
      '<div class="est-price-split yearly"><span>Yearly total</span><strong>' + formatINR(b.yearlyTotal || 0) + (b.yearlyTotal > 0 ? "<small>/yr</small>" : "") + "</strong></div>";
  }

  function renderSummary(estimate) {
    if (!summaryLines || !grandTotalEl) return;

    pulseInvoice();
    renderEstimateRows(estimate.displayRows || []);
    renderPriceSummary(estimate);

    if (subTotalEl) subTotalEl.textContent = formatINR(estimate.total);
    animateTotal(estimate.total);

    if (estimateSavings) {
      if (estimate.bundleSelected) {
        estimateSavings.hidden = false;
        if (estimateSavingsLabel) {
          estimateSavingsLabel.textContent = "Android + iOS Bundle Selected";
        }
        if (estimateSavingsValue) {
          estimateSavingsValue.textContent = estimate.appSavings > 0 ? "Save " + formatINR(estimate.appSavings) : "";
        }
      } else if (estimate.androidOnly) {
        estimateSavings.hidden = false;
        if (estimateSavingsLabel) estimateSavingsLabel.textContent = "Android App Added";
        if (estimateSavingsValue) estimateSavingsValue.textContent = "";
      } else if (estimate.iosOnly) {
        estimateSavings.hidden = false;
        if (estimateSavingsLabel) estimateSavingsLabel.textContent = "iOS App Added";
        if (estimateSavingsValue) estimateSavingsValue.textContent = "";
      } else {
        estimateSavings.hidden = true;
      }
    }

    if (selectedPlanBadge) {
      selectedPlanBadge.textContent = "Selected: " + estimate.websiteLabel;
    }

    const stickyName = document.querySelector(".js-price-sticky-name");
    if (stickyName) {
      stickyName.textContent = estimate.websiteLabel + " website · selected path";
    }

    if (recommendedBadge) {
      recommendedBadge.hidden = false;
      recommendedBadge.classList.remove("is-budget", "is-popular", "is-choice");
      if (estimate.websiteKey === "professional") {
        recommendedBadge.textContent = "Recommended Choice";
        recommendedBadge.classList.add("is-choice");
      } else if (estimate.websiteKey === "business") {
        recommendedBadge.textContent = "Most Popular Choice";
        recommendedBadge.classList.add("is-popular");
      } else {
        recommendedBadge.textContent = "Budget Friendly Choice";
        recommendedBadge.classList.add("is-budget");
      }
    }

    const waUrl = WHATSAPP_NUMBER
      ? "https://wa.me/" + WHATSAPP_NUMBER + "?text=" + encodeURIComponent(estimate.message)
      : CONTACT_FALLBACK;
    if (whatsappEstimate) whatsappEstimate.href = waUrl;
    if (contactWhatsapp) contactWhatsapp.href = waUrl;
    if (floatWhatsapp) floatWhatsapp.href = waUrl;

    if (printBody) {
      printBody.innerHTML = buildPremiumInvoiceHTML(estimate);
    }
  }

  function invoiceItemMeta(label) {
    var text = String(label || "");
    if (/Package/i.test(text)) {
      return {
        item: text,
        desc: "Website + Admin + Features",
        icon: "fa-desktop",
        tone: "blue"
      };
    }
    if (/Android \+ iOS/i.test(text) || /Android \+ iPhone/i.test(text)) {
      return {
        item: "Android + iOS Apps",
        desc: "Mobile Applications",
        icon: "fa-mobile-screen-button",
        tone: "green"
      };
    }
    if (/Android/i.test(text)) {
      return { item: "Android App", desc: "Mobile Application", icon: "fa-android", brand: true, tone: "green" };
    }
    if (/iOS|iPhone/i.test(text)) {
      return { item: "iPhone App", desc: "Mobile Application", icon: "fa-apple", brand: true, tone: "ink" };
    }
    if (/Domain/i.test(text)) {
      return {
        item: text.replace(/\s*\(have\)/i, ""),
        desc: /have/i.test(text) ? "Existing Domain" : "Premium Domain",
        icon: "fa-globe",
        tone: "gold"
      };
    }
    if (/Hosting/i.test(text)) {
      return {
        item: "Hosting",
        desc: /have/i.test(text) ? "Existing Hosting" : "Fast & Secure Hosting",
        icon: "fa-server",
        tone: "blue"
      };
    }
    return { item: text, desc: "Selected service", icon: "fa-cube", tone: "blue" };
  }

  function buildPremiumInvoiceHTML(estimate) {
    const now = new Date();
    const valid = new Date(now.getTime() + 15 * 24 * 60 * 60 * 1000);
    const dateOpts = { day: "2-digit", month: "short", year: "numeric" };
    const dateLabel = now.toLocaleDateString("en-IN", dateOpts);
    const validLabel = valid.toLocaleDateString("en-IN", dateOpts);
    const docId =
      "SV-" +
      now.getFullYear() +
      String(now.getMonth() + 1).padStart(2, "0") +
      String(now.getDate()).padStart(2, "0") +
      "-" +
      String(now.getHours()).padStart(2, "0") +
      String(now.getMinutes()).padStart(2, "0");

    var logoSvg =
      '<svg class="inv-logo" viewBox="0 0 36 36" aria-hidden="true">' +
      '<rect width="36" height="36" rx="8" fill="#2563EB"/>' +
      '<path d="M10 18.5c0-4.2 2.8-7 7.1-7 2.6 0 4.5 1 5.7 2.5l-2.3 2.1c-.8-.9-1.9-1.5-3.3-1.5-2.4 0-3.9 1.7-3.9 4 0 2.3 1.5 4 3.9 4 1.4 0 2.5-.6 3.3-1.5l2.3 2.1c-1.2 1.5-3.1 2.5-5.7 2.5-4.3-.1-7.1-2.9-7.1-7.2z" fill="#fff"/>' +
      '<circle cx="27" cy="11" r="3" fill="#FBBF24"/>' +
      "</svg>";

    var rowNo = 0;
    var rows = estimate.lines
      .filter(function (line) { return line.amount !== null; })
      .map(function (line) {
        rowNo += 1;
        var meta = invoiceItemMeta(line.label);
        var yearly = line.billing === "yearly";
        var badgeClass = yearly ? "inv-badge-yearly" : "inv-badge-once";
        var badgeText = yearly ? "Yearly" : "One-time";
        var amount =
          formatINR(line.amount) +
          (yearly && line.amount > 0 ? ' <small>/yr</small>' : "");
        var iconClass = (meta.brand ? "fa-brands " : "fa-solid ") + meta.icon;
        var itemTitle = String(meta.item || "").replace(/\s*·\s*/g, " • ");
        return (
          "<tr>" +
            '<td class="inv-col-no">' + rowNo + "</td>" +
            '<td class="inv-col-item">' +
              '<div class="inv-item">' +
                '<span class="inv-item-icon tone-' + meta.tone + '" aria-hidden="true"><i class="' + iconClass + '"></i></span>' +
                '<div class="inv-item-text">' +
                  "<strong>" + itemTitle + "</strong>" +
                  "<span>" + meta.desc + "</span>" +
                "</div>" +
              "</div>" +
            "</td>" +
            '<td class="inv-col-charge"><span class="inv-badge ' + badgeClass + '">' + badgeText + "</span></td>" +
            '<td class="inv-col-amount">' + amount + "</td>" +
          "</tr>"
        );
      }).join("");

    return (
      '<header class="inv-header">' +
        '<div class="inv-brand-block">' +
          '<div class="inv-brand">' +
            logoSvg +
            "<div>" +
              '<p class="inv-wordmark">SARVIX</p>' +
              '<p class="inv-tag">Technology for Every Business</p>' +
            "</div>" +
          "</div>" +
          '<div class="inv-title-block">' +
            "<h1>Project Estimate</h1>" +
            "<p>Transparent investment summary for your SARVIX build.</p>" +
          "</div>" +
        "</div>" +
        '<aside class="inv-quote-box">' +
          '<p class="inv-quote-id">Quotation #' + docId + "</p>" +
          '<ul class="inv-quote-meta">' +
            "<li><span>Date</span><strong>" + dateLabel + "</strong></li>" +
            "<li><span>Valid Till</span><strong>" + validLabel + "</strong></li>" +
            "<li><span>Prepared For</span><strong>Digital Store Project</strong></li>" +
          "</ul>" +
        "</aside>" +
      "</header>" +

      '<div class="inv-accent" aria-hidden="true"></div>' +

      '<section class="inv-parties">' +
        '<div class="inv-party inv-from">' +
          '<p class="inv-party-label">From</p>' +
          "<strong>SARVIX Technologies</strong>" +
          "<p>Ahmedabad, Gujarat, India</p>" +
          "<p>" + (window.SARVIX_EMAIL || "hello@sarvixtechnologies.com") + "</p>" +
        "</div>" +
        '<div class="inv-party inv-for">' +
          '<p class="inv-party-label">Prepared For</p>' +
          "<strong>Digital Store Project</strong>" +
          "<p>" + estimate.websiteLabel + " Package</p>" +
          '<p class="inv-delivery">Delivery: <b>' + estimate.delivery + "</b></p>" +
        "</div>" +
      "</section>" +

      '<section class="inv-table-wrap">' +
        '<table class="inv-table">' +
          "<thead><tr>" +
            "<th>#</th><th>Item</th><th>Charge</th><th>Amount</th>" +
          "</tr></thead>" +
          "<tbody>" + rows + "</tbody>" +
        "</table>" +
      "</section>" +

      '<section class="inv-bottom">' +
        '<div class="inv-totals">' +
          '<div class="inv-total-row"><span>One-time Charges</span><strong>' + formatINR(estimate.fixedTotal || 0) + "</strong></div>" +
          '<div class="inv-total-row"><span>Yearly Charges</span><strong>' + formatINR(estimate.yearlyTotal || 0) + (estimate.yearlyTotal > 0 ? " /yr" : "") + "</strong></div>" +
          '<div class="inv-total-row"><span>GST</span><strong class="inv-muted-val">Not Included</strong></div>' +
          '<div class="inv-total-row inv-grand"><span>Grand Total</span><strong>' + formatINR(estimate.total) + "</strong></div>" +
        "</div>" +
        '<aside class="inv-pay-card">' +
          '<p class="inv-pay-label">Payment Today (40%)</p>' +
          '<p class="inv-pay-amount">' + formatINR(estimate.advance) + "</p>" +
          "<p>40% advance to start your project.</p>" +
        "</aside>" +
      "</section>" +

      '<footer class="inv-footer">' +
        '<div class="inv-notes">' +
          "<ul>" +
            "<li>GST not included. Domain &amp; hosting renew yearly.</li>" +
            "<li>Final quotation may vary for custom requirements.</li>" +
            "<li>Support: " + (estimate.support || "Included") + " free after launch.</li>" +
          "</ul>" +
        "</div>" +
        '<div class="inv-sign">' +
          '<em class="inv-sign-script">SARVIX</em>' +
          "<span>Authorized Signature</span>" +
        "</div>" +
      "</footer>"
    );
  }

  function updateCalculator() {
    if (!form) return;
    renderSummary(buildEstimate());
    updateProgress();
    updateAppSelectorUI();
  }

  function initDomainProviders() {
    var newDomainInput = document.getElementById("domainOptionNew");
    var priceEl = document.getElementById("domainNewPrice");
    var nameEl = document.getElementById("domainProviderName");
    var buttons = document.querySelectorAll(".domain-provider");
    if (!newDomainInput || !buttons.length) return;

    buttons.forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        buttons.forEach(function (b) { b.classList.remove("is-active"); });
        btn.classList.add("is-active");

        var price = btn.getAttribute("data-price") || "799";
        var provider = btn.getAttribute("data-provider") || "Hostinger";

        newDomainInput.value = price;
        newDomainInput.setAttribute("data-provider", provider);
        newDomainInput.checked = true;

        if (priceEl) priceEl.textContent = formatINR(price);
        if (nameEl) nameEl.textContent = provider;

        updateCalculator();
      });
    });
  }

  if (form) {
    form.addEventListener("change", function (e) {
      if (e.target && (e.target.id === "optAndroid" || e.target.id === "optIos")) {
        form.dataset.appsTouched = "1";
      }
      updateCalculator();
    });
    initDomainProviders();
    updateCalculator();
  }

  if (downloadBtn) {
    downloadBtn.addEventListener("click", function () {
      updateCalculator();
      var prevTitle = document.title;
      document.title = "SARVIX Project Estimate";
      window.print();
      window.setTimeout(function () {
        document.title = prevTitle;
      }, 500);
    });
  }

  /* Soft-3D: package card tilt */
  (function initSoft3D() {
    if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    var tiltCards = document.querySelectorAll(".package-select");
    tiltCards.forEach(function (card) {
      var body = card.querySelector(".option-body");
      if (!body) return;

      card.addEventListener("mousemove", function (e) {
        var rect = card.getBoundingClientRect();
        var px = (e.clientX - rect.left) / rect.width;
        var py = (e.clientY - rect.top) / rect.height;
        var rotateY = (px - 0.5) * 10;
        var rotateX = (0.5 - py) * 10;
        rotateX = Math.max(-6, Math.min(6, rotateX));
        rotateY = Math.max(-6, Math.min(6, rotateY));
        card.classList.add("is-tilting");
        var lift = card.querySelector("input:checked") ? -6 : -8;
        var scale = card.querySelector("input:checked") ? 1.02 : 1;
        body.style.transform =
          "perspective(900px) translateY(" + lift + "px) scale(" + scale + ") rotateX(" +
          rotateX.toFixed(2) + "deg) rotateY(" + rotateY.toFixed(2) + "deg)";
      });

      card.addEventListener("mouseleave", function () {
        card.classList.remove("is-tilting");
        body.style.transform = "";
      });
    });
  })();

  function selectWebsitePlan(plan) {
    if (!form || !plan) return;
    var input = form.querySelector('input[name="website"][value="' + plan + '"]');
    if (!input) return;
    input.checked = true;
    updateCalculator();

    document.querySelectorAll(".support-compare-item").forEach(function (item) {
      item.classList.toggle("is-active", item.getAttribute("data-select-plan") === plan);
    });
  }

  document.querySelectorAll("[data-select-plan]").forEach(function (el) {
    el.addEventListener("click", function (e) {
      var plan = el.getAttribute("data-select-plan");
      if (!plan) return;
      if (el.tagName === "BUTTON") e.preventDefault();
      selectWebsitePlan(plan);
    });
  });

  document.querySelectorAll("[data-open-support-rates]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var target = document.getElementById("supportAfter");
      if (!target) return;
      target.scrollIntoView({ behavior: "smooth", block: "start" });
      target.classList.add("is-spotlight");
      window.setTimeout(function () {
        target.classList.remove("is-spotlight");
      }, 1600);
    });
  });
})();
