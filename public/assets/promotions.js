(function () {
  var root = document.getElementById("cebinova-promotions");
  if (!root) return;
  var surfaceUrl = root.getAttribute("data-surface");
  var quoteUrl = root.getAttribute("data-quote");
  var eventsUrl = root.getAttribute("data-events");
  var csrf = root.getAttribute("data-csrf");
  var token = document.querySelector('meta[name="csrf-token"]');
  if (token) csrf = token.getAttribute("content") || csrf;

  function post(url, body) {
    return fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf, Accept: "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(body || {})
    });
  }

  function track(event, campaign) {
    if (!eventsUrl || !campaign) return;
    post(eventsUrl, { event: event, promotion_id: campaign.id });
  }

  function pad(value) { return String(value).padStart(2, "0"); }

  function paintCountdown(node, until) {
    if (!node || !until) return;
    var end = Date.parse(until);
    node.setAttribute("data-until", until);
    function tick() {
      if (node.getAttribute("data-until") !== until) return;
      var left = end - Date.now();
      var total = left <= 0 ? 0 : Math.floor(left / 1000);
      var days = Math.floor(total / 86400);
      var hours = Math.floor((total % 86400) / 3600);
      var minutes = Math.floor((total % 3600) / 60);
      var seconds = total % 60;
      if (node.classList.contains("promo-timer")) {
        node.querySelector("[data-d]").textContent = pad(days);
        node.querySelector("[data-h]").textContent = pad(hours);
        node.querySelector("[data-m]").textContent = pad(minutes);
        node.querySelector("[data-s]").textContent = pad(seconds);
      } else if (node.hasAttribute("data-compact")) {
        node.textContent = days + "d " + pad(hours) + "h " + pad(minutes) + "m " + pad(seconds) + "s";
      } else {
        node.textContent = pad(days) + " Days " + pad(hours) + " Hours " + pad(minutes) + " Minutes " + pad(seconds) + " Seconds";
      }
      if (left <= 0) {
        if (!node.getAttribute("data-reloading")) {
          node.setAttribute("data-reloading", "1");
          window.setTimeout(function () { window.location.reload(); }, 800);
        }
        return;
      }
      window.setTimeout(tick, 1000);
    }
    tick();
  }

  function dismissalKey(campaign) { return "cebinova-promo-" + campaign.id; }

  function repeatMs(campaign) {
    if (campaign.popup_repeat_minutes === undefined || campaign.popup_repeat_minutes === null || campaign.popup_repeat_minutes === "") return 60000;
    var minutes = Number(campaign.popup_repeat_minutes);
    return Number.isFinite(minutes) && minutes > 0 ? minutes * 60000 : 0;
  }

  function closedRecently(campaign) {
    var raw = campaign.popup_frequency === "session" ? sessionStorage.getItem(dismissalKey(campaign)) : localStorage.getItem(dismissalKey(campaign));
    if (!raw) return false;
    if (campaign.popup_frequency === "session" || !campaign.popup_frequency) {
      var repeat = repeatMs(campaign);
      return repeat === 0 || Date.now() < Date.parse(raw) + repeat;
    }
    if (campaign.popup_frequency === "campaign") return true;
    var at = Date.parse(raw);
    if (campaign.popup_frequency === "cooldown") {
      return Date.now() < at + (Number(campaign.popup_cooldown_hours) || 24) * 3600000;
    }
    var closed = new Date(at);
    var now = new Date();
    return closed.getFullYear() === now.getFullYear() && closed.getMonth() === now.getMonth() && closed.getDate() === now.getDate();
  }

  function rememberClose(campaign) {
    var store = !campaign.popup_frequency || campaign.popup_frequency === "session" ? sessionStorage : localStorage;
    store.setItem(dismissalKey(campaign), new Date().toISOString());
  }

  function mount(payload) {
    var campaign = payload && payload.campaign;
    if (!campaign || payload.mode === "none") return;
    var offer = payload.active_offer || (campaign.upcoming ? null : campaign);
    track("promotion_impression", campaign);
    document.querySelectorAll("[data-promo-countdown]").forEach(function (node) {
      paintCountdown(node, node.getAttribute("data-promo-until"));
    });

    var running = (payload.offers || [])
      .filter(function (item) { return !item.upcoming; })
      .sort(function (a, b) { return Date.parse(a.ends_at) - Date.parse(b.ends_at); });
    renderDock(running.length ? running : [offer || campaign]);

    var popupOffers = (payload.live_offers && payload.live_offers.length ? payload.live_offers : [campaign])
      .filter(function (item) { return item.show_popup || item.type === "festival"; });
    if (popupOffers.length) queuePopup(popupOffers, Math.max(0, Number(popupOffers[0].popup_delay_seconds) || 4) * 1000);

    paintPrices();
  }

  function waitFor(campaign) {
    var sessionMode = !campaign.popup_frequency || campaign.popup_frequency === "session";
    if (!sessionMode) return closedRecently(campaign) ? null : 0;
    var closedAt = sessionStorage.getItem(dismissalKey(campaign));
    if (!closedAt) return 0;
    var repeat = repeatMs(campaign);
    if (repeat === 0) return null;
    return Math.max(0, Date.parse(closedAt) + repeat - Date.now());
  }

  function queuePopup(offers, minDelay) {
    var next = null;
    var wait = null;
    offers.forEach(function (offer) {
      var ms = waitFor(offer);
      if (ms === null) return;
      if (wait === null || ms < wait) {
        wait = ms;
        next = offer;
      }
    });
    if (!next) return;
    window.setTimeout(function () { openPopup(offers, next); }, Math.max(minDelay || 0, wait));
  }

  function renderDock(offers) {
    if (document.querySelector(".promo-dock")) return;
    var dock = document.createElement("aside");
    dock.className = "promo-dock";
    dock.setAttribute("aria-label", offers.length > 1 ? "Current offers" : "Current offer");
    dock.setAttribute("aria-live", "polite");
    var title = document.createElement("p");
    title.className = "promo-dock-kicker";
    var name = document.createElement("strong");
    var code = document.createElement("button");
    code.type = "button";
    code.className = "promo-code-chip";
    var count = document.createElement("span");
    count.className = "promo-countdown";
    count.setAttribute("data-compact", "1");
    var link = document.createElement("a");
    link.className = "promo-dock-link";
    link.textContent = "View offer";
    [title, name, code, count, link].forEach(function (node) { dock.appendChild(node); });

    var dots = [];
    if (offers.length > 1) {
      var pager = document.createElement("span");
      pager.className = "promo-dock-dots";
      offers.forEach(function (offer, index) {
        var dot = document.createElement("button");
        dot.type = "button";
        dot.setAttribute("aria-label", "Show " + (offer.headline || offer.name));
        dot.addEventListener("click", function () { show(index); });
        dots.push(dot);
        pager.appendChild(dot);
      });
      dock.appendChild(pager);
    }

    var here = window.location.pathname.replace(/\/$/, "");
    var current = 0;

    function fill(campaign) {
      title.textContent = campaign.upcoming ? "Festival offer coming soon" : "Discount available";
      name.textContent = (campaign.headline || campaign.name) + " · " + (campaign.discount_label || "") + (campaign.upcoming ? "" : " off");
      code.hidden = !campaign.coupon_code;
      code.textContent = campaign.coupon_code ? "Coupon " + campaign.coupon_code : "";
      code.onclick = function () {
        if (navigator.clipboard) navigator.clipboard.writeText(campaign.coupon_code);
        var input = document.querySelector("[name='coupon_code'], #promoCoupon");
        if (input) input.value = campaign.coupon_code;
        code.textContent = "Copied " + campaign.coupon_code;
      };
      count.hidden = campaign.show_countdown === false;
      if (count.hidden) count.removeAttribute("data-until");
      else paintCountdown(count, campaign.upcoming ? campaign.starts_at : campaign.ends_at);
      var target = campaign.cta_url || (campaign.slug ? "/offers/" + campaign.slug : "/offers");
      var offerPath = target.replace(/^https?:\/\/[^/]+/, "").replace(/\/$/, "");
      link.hidden = here === offerPath;
      link.href = target;
      link.onclick = function () { track("promotion_cta_click", campaign); };
      dots.forEach(function (dot, index) { dot.classList.toggle("is-active", index === current); });
    }

    function show(index) {
      if (index === current && dock.hasAttribute("data-ready")) return;
      current = (index + offers.length) % offers.length;
      dock.setAttribute("data-ready", "1");
      if (offers.length < 2 || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        fill(offers[current]);
        return;
      }
      dock.classList.add("is-swapping");
      window.setTimeout(function () {
        fill(offers[current]);
        dock.classList.remove("is-swapping");
      }, 220);
    }

    fill(offers[current]);
    dock.setAttribute("data-ready", "1");
    document.body.prepend(dock);

    if (offers.length > 1) {
      var paused = false;
      dock.addEventListener("mouseenter", function () { paused = true; });
      dock.addEventListener("mouseleave", function () { paused = false; });
      dock.addEventListener("focusin", function () { paused = true; });
      dock.addEventListener("focusout", function () { paused = false; });
      window.setInterval(function () {
        if (paused || document.hidden) return;
        show(current + 1);
      }, 6000);
    }

    function placeDock() {
      document.documentElement.style.setProperty("--promo-dock-h", dock.offsetHeight + "px");
    }
    placeDock();
    window.addEventListener("resize", placeDock);
  }

  function openPopup(offers, campaign) {
    if (document.querySelector(".promo-dialog")) return;
    var visitor = document.querySelector("[data-visitor-popup]");
    if (visitor && !visitor.hidden) {
      var watcher = new MutationObserver(function () {
        if (visitor.hidden) {
          watcher.disconnect();
          openPopup(offers, campaign);
        }
      });
      watcher.observe(visitor, { attributes: true, attributeFilter: ["hidden"] });
      return;
    }
    var dialog = document.createElement("div");
    dialog.className = "promo-dialog";
    dialog.innerHTML = '<div class="promo-dialog-card" role="dialog" aria-modal="true" aria-labelledby="promoTitle"><button type="button" class="promo-close" aria-label="Close offer"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></button><div class="promo-dialog-body"><div class="promo-dialog-main"><p class="promo-kicker"><span class="promo-kicker-dot" aria-hidden="true"></span><span class="promo-kicker-text"></span></p><h2 id="promoTitle"></h2><p class="promo-sub"></p><p class="promo-off"></p><p class="promo-note"></p><div class="promo-actions"><a class="promo-cta"></a><a class="promo-brochure"></a></div></div><div class="promo-dialog-side"><p class="promo-timer-label">Offer ends in</p><div class="promo-timer" data-live-countdown aria-live="off"><span><b data-d>00</b><small>Days</small></span><span><b data-h>00</b><small>Hours</small></span><span><b data-m>00</b><small>Minutes</small></span><span><b data-s>00</b><small>Seconds</small></span></div><button type="button" class="promo-ticket" hidden></button></div></div></div>';
    var card = dialog.querySelector(".promo-dialog-card");
    var current = campaign;
    var tabs = [];
    if (offers.length > 1) {
      dialog.classList.add("has-switcher");
      var nav = document.createElement("nav");
      nav.className = "promo-switcher";
      nav.setAttribute("aria-label", "Live offers");
      var head = document.createElement("p");
      head.className = "promo-switcher-head";
      head.textContent = offers.length + " offers live now";
      nav.appendChild(head);
      offers.forEach(function (offer) {
        var tab = document.createElement("button");
        tab.type = "button";
        tab.className = "promo-switch";
        var kind = document.createElement("small");
        kind.textContent = offer.festival_name || offer.type_label || "Offer";
        var title = document.createElement("strong");
        title.textContent = offer.headline || offer.name;
        var meta = document.createElement("span");
        meta.textContent = (offer.discount_label || "") + " off · ends " + new Date(offer.ends_at).toLocaleDateString("en-IN", { day: "numeric", month: "short" });
        tab.appendChild(kind);
        tab.appendChild(title);
        tab.appendChild(meta);
        tab.addEventListener("click", function () {
          if (current.id !== offer.id) render(offer);
        });
        tabs.push({ id: offer.id, node: tab });
        nav.appendChild(tab);
      });
      var hint = document.createElement("p");
      hint.className = "promo-switcher-hint";
      hint.textContent = "Close this offer to see the next one.";
      nav.appendChild(hint);
      card.insertBefore(nav, card.querySelector(".promo-dialog-body"));
    }

    function render(offer) {
      current = offer;
      var designs = ["split", "night", "editorial", "rail", "horizon"];
      dialog.className = dialog.className.replace(/\s*promo-design-\S+/g, "") + " promo-design-" + (designs.indexOf(offer.popup_design) >= 0 ? offer.popup_design : "split");
      dialog.querySelector(".promo-kicker-text").textContent = offer.upcoming ? "Nearest festival offer" : (offer.type_label || "Offer");
      dialog.querySelector("#promoTitle").textContent = offer.headline || offer.name;
      dialog.querySelector(".promo-sub").textContent = offer.subheadline || "Website pricing and marketing packages";
      dialog.querySelector(".promo-off").innerHTML = offer.upcoming
        ? "<b></b><span>starts soon</span>"
        : "<b></b><span>off</span>";
      dialog.querySelector(".promo-off b").textContent = offer.discount_label || "";
      dialog.querySelector(".promo-timer-label").textContent = offer.upcoming ? "Starts in" : "Offer ends in";
      dialog.querySelector(".promo-note").textContent = offer.upcoming
        ? "Regular prices stay in place until this offer starts."
        : "Applied on website and marketing prices until the timer ends.";
      var ticket = dialog.querySelector(".promo-ticket");
      ticket.hidden = !offer.coupon_code;
      ticket.textContent = offer.coupon_code ? "Use code " + offer.coupon_code : "";
      ticket.onclick = function () {
        if (navigator.clipboard) navigator.clipboard.writeText(offer.coupon_code);
        var input = document.querySelector("[name='coupon_code'], #promoCoupon");
        if (input) input.value = offer.coupon_code;
        ticket.textContent = "Copied " + offer.coupon_code;
      };
      var brochure = dialog.querySelector(".promo-brochure");
      brochure.href = offer.cta_url || "/offers";
      brochure.textContent = "View offer";
      var cta = dialog.querySelector(".promo-cta");
      cta.href = offer.cta_url || "/offers";
      cta.textContent = offer.cta_text || "Explore Offer";
      cta.onclick = function () { track("promotion_cta_click", offer); };
      var timer = dialog.querySelector("[data-live-countdown]");
      if (offer.show_countdown) paintCountdown(timer, offer.upcoming ? offer.starts_at : offer.ends_at);
      else timer.removeAttribute("data-until");
      tabs.forEach(function (tab) {
        var active = tab.id === offer.id;
        tab.node.classList.toggle("is-active", active);
        if (active) tab.node.setAttribute("aria-current", "true");
        else tab.node.removeAttribute("aria-current");
      });
      startTheme(dialog, offer.theme || (offer.type === "festival" ? "crackers" : "none"));
      track("promotion_popup_view", offer);
    }

    function close() {
      if (dialog.classList.contains("is-closing")) return;
      dialog.classList.add("is-closing");
      var closed = current;
      window.setTimeout(function () {
        rememberClose(closed);
        track("promotion_popup_close", closed);
        dialog.remove();
        queuePopup(offers, 450);
      }, 240);
    }
    dialog.querySelector(".promo-close").addEventListener("click", close);
    document.body.appendChild(dialog);
    render(campaign);
  }

  function startTheme(dialog, theme) {
    dialog.querySelectorAll(".promo-fx").forEach(function (node) { node.remove(); });
    dialog.classList.remove("is-festival", "is-crackers", "is-garba");
    var run = String(Date.now()) + Math.random();
    dialog.setAttribute("data-fx", run);
    if (theme !== "crackers" && theme !== "garba") return;
    dialog.classList.add("is-festival", "is-" + theme);
    var still = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    function alive() {
      return dialog.isConnected && !dialog.classList.contains("is-closing") && dialog.getAttribute("data-fx") === run;
    }
    if (theme === "garba") playGarba(dialog, alive, still);
    else if (!still) blastCrackers(dialog, alive);
  }

  var garbaColors = ["#e91e63", "#ff9800", "#ffc107", "#4caf50", "#9c27b0", "#f44336", "#00bcd4"];

  function dandiyaPair(className, delay) {
    var pair = document.createElement("span");
    pair.className = className;
    pair.setAttribute("aria-hidden", "true");
    pair.style.setProperty("--d", delay + "s");
    pair.innerHTML = '<i class="is-left"></i><i class="is-right"></i><b></b>';
    return pair;
  }

  function playGarba(dialog, alive, still) {
    var card = dialog.querySelector(".promo-dialog-card");
    var toran = document.createElement("div");
    toran.className = "promo-toran promo-fx";
    toran.setAttribute("aria-hidden", "true");
    for (var flag = 0; flag < 22; flag++) {
      var piece = document.createElement("span");
      piece.style.setProperty("--c", garbaColors[flag % garbaColors.length]);
      piece.style.animationDelay = (flag % 6) * 0.12 + "s";
      toran.appendChild(piece);
    }
    card.appendChild(toran);

    var off = dialog.querySelector(".promo-off");
    if (off) off.appendChild(dandiyaPair("promo-dandiya is-mini promo-fx", 0));

    var floor = document.createElement("div");
    floor.className = "promo-garba promo-fx";
    floor.setAttribute("aria-hidden", "true");
    [[6, 20, 1.5, 0], [87, 16, 1.3, 0.35], [3, 66, 1.2, 0.7], [90, 62, 1.6, 0.2], [18, 86, 1, 0.5], [76, 88, 1.15, 0.9]].forEach(function (spot) {
      var pair = dandiyaPair("promo-dandiya", spot[3]);
      pair.style.left = spot[0] + "%";
      pair.style.top = spot[1] + "%";
      pair.style.setProperty("--s", spot[2]);
      floor.appendChild(pair);
    });
    dialog.insertBefore(floor, dialog.firstChild);
    if (still) return;

    function drop() {
      var dot = document.createElement("span");
      var size = 6 + Math.random() * 9;
      var life = 5 + Math.random() * 3;
      dot.className = "promo-bandhani";
      dot.style.left = Math.random() * 100 + "%";
      dot.style.width = size + "px";
      dot.style.height = size + "px";
      dot.style.setProperty("--c", garbaColors[Math.floor(Math.random() * garbaColors.length)]);
      dot.style.setProperty("--x", (Math.random() * 80 - 40) + "px");
      dot.style.animationDuration = life + "s";
      floor.appendChild(dot);
      window.setTimeout(function () { dot.remove(); }, life * 1000);
    }
    for (var first = 0; first < 10; first++) window.setTimeout(drop, first * 140);
    var timer = window.setInterval(function () {
      if (!alive()) {
        window.clearInterval(timer);
        return;
      }
      if (!document.hidden) drop();
    }, 380);
  }

  function blastCrackers(dialog, alive) {
    var sky = document.createElement("div");
    sky.className = "promo-crackers promo-fx";
    sky.setAttribute("aria-hidden", "true");
    var colors = ["#fbb50b", "#ff5a36", "#fff4c2", "#ffffff", "#ff8a1e", "#7dff6b"];
    var count = 0;
    function burst(delay) {
      var left = 6 + Math.random() * 88;
      var top = 10 + Math.random() * 75;
      var shade = count++;
      var pieces = [];
      var trail = document.createElement("span");
      trail.className = "is-trail";
      trail.style.left = left + "%";
      trail.style.top = top + "%";
      trail.style.background = colors[shade % colors.length];
      trail.style.animationDelay = Math.max(0, delay - 180) + "ms";
      pieces.push(trail);
      for (var spark = 0; spark < 16; spark++) {
        var bit = document.createElement("span");
        var angle = (Math.PI * 2 * spark) / 16 + Math.random() * 0.35;
        var dist = 36 + Math.random() * 78;
        bit.style.left = left + "%";
        bit.style.top = top + "%";
        bit.style.background = colors[(shade + spark) % colors.length];
        bit.style.boxShadow = "0 0 8px " + bit.style.background;
        bit.style.setProperty("--x", Math.cos(angle) * dist + "px");
        bit.style.setProperty("--y", Math.sin(angle) * dist + "px");
        bit.style.animationDelay = delay + "ms";
        pieces.push(bit);
      }
      pieces.forEach(function (piece) { sky.appendChild(piece); });
      window.setTimeout(function () {
        pieces.forEach(function (piece) { piece.remove(); });
      }, delay + 1200);
    }
    dialog.appendChild(sky);
    for (var first = 0; first < 5; first++) burst(80 + first * 220);
    var timer = window.setInterval(function () {
      if (!alive()) {
        window.clearInterval(timer);
        return;
      }
      if (document.hidden) return;
      burst(0);
      if (Math.random() < 0.5) burst(160 + Math.random() * 200);
    }, 520);
  }

  function paintPrices() {
    var plansNode = document.getElementById("cebinova-website-plans");
    if (!plansNode) return;
    var plans = [];
    try { plans = JSON.parse(plansNode.textContent || "[]"); } catch (error) { return; }
    plans.forEach(function (plan) {
      if (!plan || !plan.key || !plan.promotion) return;
      var input = document.querySelector('input[name="website"][value="' + plan.key + '"]');
      if (!input) return;
      var card = input.closest("label");
      var price = card ? card.querySelector(".option-price") : null;
      if (!price) return;
      price.innerHTML = "";
      var was = document.createElement("s");
      was.textContent = "₹" + Number(plan.promotion.original).toLocaleString("en-IN");
      var now = document.createElement("b");
      now.textContent = " ₹" + Number(plan.promotion.final).toLocaleString("en-IN");
      price.appendChild(was);
      price.appendChild(now);
      var slot = card.querySelector(".pkg-promo-slot");
      if (slot && !slot.querySelector(".promo-badge")) {
        var badge = document.createElement("span");
        badge.className = "promo-badge";
        badge.textContent = (plan.promotion.name || "Special Offer") + " · Save ₹" + Number(plan.promotion.savings).toLocaleString("en-IN");
        slot.prepend(badge);
      }
    });
  }

  function selection() {
    var website = document.querySelector('input[name="website"]:checked');
    var plansNode = document.getElementById("cebinova-website-plans");
    var plans = [];
    try { plans = JSON.parse((plansNode && plansNode.textContent) || "[]"); } catch (error) { plans = []; }
    var plan = plans.find(function (row) { return website && row.key === website.value; });
    var optionIds = [];
    document.querySelectorAll("[data-option-id]:checked").forEach(function (input) {
      optionIds.push(Number(input.getAttribute("data-option-id")));
    });
    return { pricing_package_id: plan && plan.id ? plan.id : null, pricing_option_ids: optionIds };
  }

  document.addEventListener("cebinova:estimate", function () {
    var box = document.getElementById("promotionBreakdown");
    if (!box || !quoteUrl) return;
    var payload = selection();
    var coupon = document.getElementById("promoCoupon");
    if (coupon && coupon.value) payload.coupon_code = coupon.value.trim();
    if (!payload.pricing_package_id) return;
    post(quoteUrl, payload).then(function (response) { return response.json().then(function (data) { data.ok = response.ok; return data; }); }).then(function (data) {
      box.innerHTML = "";
      if (!data || !data.ok && data.message) {
        var error = document.createElement("p");
        error.textContent = (data && (data.message || (data.errors && data.errors.coupon_code && data.errors.coupon_code[0]))) || "Coupon could not be applied.";
        box.appendChild(error);
        return;
      }
      (data.adjustments || []).forEach(function (row) {
        var line = document.createElement("p");
        line.textContent = (row.label || row.name || row.code) + " −₹" + row.amount;
        box.appendChild(line);
      });
      if (data.discount_total && Number(data.discount_total) > 0 && data.discounted_subtotal) {
        var total = document.getElementById("grandTotal");
        if (total) total.textContent = "₹" + Number(data.discounted_subtotal).toLocaleString("en-IN");
      }
    }).catch(function () {});
  });

  fetch(surfaceUrl, { headers: { Accept: "application/json" }, credentials: "same-origin" })
    .then(function (response) { return response.json(); })
    .then(mount)
    .catch(function () {});

  window.CebinovaPromotions = { paintPrices: paintPrices };
})();
