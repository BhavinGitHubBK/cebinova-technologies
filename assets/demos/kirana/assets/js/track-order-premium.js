(function (window, document) {
  "use strict";
  const root = document.querySelector('[data-portal-root]');
  if (!root) return;
  const store = window.FBStorage, money = window.FBUI.money;
  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];
  const esc = value => String(value ?? "").replace(/[&<>"']/g, char => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;" }[char]));
  const profile = store.get("fb-profile", { name:"Aarav Patel", mobile:"9876543210", email:"aarav@freshbasket.demo" });
  const storedOrders = store.get("fb-order-history", []);
  const demoProducts = (window.PRODUCTS || []).slice(0, 8);
  const demoOrder = {
    number:"FBM2026277772", displayDate:"30 Jul 2026", createdAt:"2026-07-30T09:30:00.000Z",
    status:"Out for delivery", date:"30 Jul 2026", slot:"7–9 PM", deliveryType:"Home delivery",
    paymentMethod:"UPI", paymentStatus:"Paid", fulfilment:"delivery",
    address:{ house:"B-204", building:"Green Residency", area:"Gota", city:"Ahmedabad", pin:"380060" },
    products:demoProducts.map(item => ({ id:item.id, name:item.name, image:item.image, weight:item.weight, qty:1, price:item.sellingPrice, mrp:item.mrp })),
    totals:{
      finalTotal:demoProducts.reduce((sum,item) => sum + item.sellingPrice,0),
      totalSavings:demoProducts.reduce((sum,item) => sum + item.savings,0)
    }
  };
  const orders = [demoOrder, ...storedOrders.filter(order => order.number !== demoOrder.number)];
  const requested = new URLSearchParams(location.search).get("order");
  const initialOrder = orders.find(order => order.number === requested) || demoOrder;

  function statusIndex(order) {
    const states = ["Placed","Confirmed","Packed","Out for delivery","Delivered"];
    const normalized = String(order.status || "Confirmed").toLowerCase();
    const found = states.findIndex(state => state.toLowerCase() === normalized);
    return found < 0 ? 1 : found;
  }

  function address(order) {
    if (order.fulfilment === "pickup") return `${order.pickup?.point || "FreshBasket Gota Store"} pickup point`;
    const item = order.address || {};
    return [item.house,item.building,item.area,item.city,item.pin].filter(Boolean).join(", ");
  }

  function timeline(order) {
    const states = [
      ["fa-receipt","Order placed","Order received successfully","29 Jul","8:20 PM"],
      ["fa-circle-check","Confirmed","Payment verified","29 Jul","8:24 PM"],
      ["fa-box-open","Packed","Quality checked and securely packed","30 Jul","7:30 AM"],
      ["fa-motorcycle","Out for delivery","Rider is heading to your address","30 Jul","7:48 AM"],
      ["fa-house-circle-check","Delivered","Order delivered successfully","30 Jul","8:15 AM"]
    ], current = statusIndex(order);
    return `<ol class="to-timeline" aria-label="Order tracking progress">${states.map(([icon,label,copy,date,time],index) => `<li class="${index < current ? "is-done" : index === current ? "is-current" : ""}"><span><i class="fa-solid ${icon}"></i></span><div><b>${label}</b><time><strong>${date}</strong><span>${time}</span></time><small>${copy}</small>${index === current ? `<em>Current status</em>` : index < current ? `<em>Completed</em>` : ""}</div></li>`).join("")}</ol>`;
  }

  function productRows(order) {
    return (order.products || []).map(item => `<div class="to-product"><img src="${item.image}" alt="${esc(item.name)}" onerror="imageFallback(this)"><span><b>${esc(item.name)}</b><small>${esc(item.weight)} · Qty ${item.qty}</small></span><strong>${money(item.price * item.qty)}</strong></div>`).join("");
  }

  function result(order) {
    const current = ["Placed","Confirmed","Packed","Out for delivery","Delivered"][statusIndex(order)];
    const totalMrp = (order.products || []).reduce((sum,item) => sum + Number(item.mrp || item.price) * item.qty,0);
    const finalTotal = Number(order.totals?.finalTotal || (order.products || []).reduce((sum,item) => sum + item.price * item.qty,0));
    const savings = Number(order.totals?.totalSavings || Math.max(0,totalMrp-finalTotal));
    return `<section class="to-result" id="tracking-result" aria-live="polite">
      <header class="to-result-head"><div><span>Order ${esc(order.number)}</span><h2>${esc(current)}</h2><p>Estimated ${order.fulfilment === "pickup" ? "pickup" : "delivery"}: <b>${esc(order.date || "Today")} · ${esc(order.slot || "7–9 PM")}</b></p></div><span class="to-status"><i class="fa-solid fa-circle"></i> ${esc(current)}</span></header>
      <div class="to-tracking-grid"><article class="to-card to-timeline-card"><div class="to-card-head"><div><span class="eyebrow">Live progress</span><h3>Order journey</h3></div><span>${statusIndex(order)+1} of 5 steps</span></div>${timeline(order)}</article>
      <article class="to-map-card"><div class="to-map-top"><span><i class="fa-solid fa-location-arrow"></i> Live delivery view</span><b>${statusIndex(order) === 3 ? "2.4 km away" : current}</b></div><div class="to-map"><i class="fa-solid fa-store to-store"></i><i class="fa-solid fa-house to-home"></i><span class="to-route"></span><i class="fa-solid fa-motorcycle to-rider"></i><span class="to-map-pin one"></span><span class="to-map-pin two"></span></div><div class="to-arrival"><i class="fa-regular fa-clock"></i><span><small>Expected arrival</small><b>${esc(order.slot || "7–9 PM")}</b></span></div></article></div>
      <div class="to-meta-grid"><article class="to-card to-fact-card"><div class="to-card-head"><h3>Order details</h3><i class="fa-solid fa-receipt"></i></div><dl><div><dt>Order number</dt><dd>${esc(order.number)}</dd></div><div><dt>Items</dt><dd>${(order.products || []).reduce((sum,item)=>sum+item.qty,0)} products</dd></div><div><dt>Payment</dt><dd>${esc(order.paymentMethod || "Recorded")}</dd></div><div><dt>Amount</dt><dd>${money(finalTotal)}</dd></div><div><dt>Coupon</dt><dd>${esc(order.coupon || "WELCOME100")}</dd></div></dl></article>
      <article class="to-card to-partner-card"><div class="to-card-head"><h3>Delivery partner</h3><span class="to-partner-online">Online</span></div><div class="to-partner"><span>RS</span><div><b>Rahul Sharma</b><small>★★★★☆ · FreshBasket Rider</small></div></div><dl><div><dt>Vehicle</dt><dd>GJ01 XX 1234</dd></div><div><dt>ETA</dt><dd>8:15 AM</dd></div></dl><div><button type="button"><i class="fa-solid fa-phone"></i> Call</button><button type="button"><i class="fa-solid fa-message"></i> Chat</button></div></article>
      <article class="to-card to-eta-card"><span class="eyebrow">Estimated arrival</span><h3>Today, 8:15 AM</h3><div class="to-countdown" data-track-countdown><span><b>00</b><small>HRS</small></span><i>:</i><span><b>12</b><small>MIN</small></span><i>:</i><span><b>00</b><small>SEC</small></span></div><p><i class="fa-solid fa-bolt"></i> Your order is arriving soon.</p></article></div>
      <div class="to-details-grid"><article class="to-card"><div class="to-card-head"><h3>Delivery details</h3></div><div class="to-detail-list"><div><i class="fa-solid fa-location-dot"></i><span><small>Delivering to</small><b>${esc(profile.name || "Customer")}<br>${esc(address(order))}</b></span></div><div><i class="fa-solid fa-credit-card"></i><span><small>Payment method</small><b>${esc(order.paymentMethod || "Payment recorded")} · ${esc(order.paymentStatus || "Recorded")}</b></span></div><div><i class="fa-solid fa-user-shield"></i><span><small>Delivery partner</small><b>${statusIndex(order) >= 3 ? "Rahul Sharma · Contact available" : "Assigned after dispatch"}</b></span></div><div><i class="fa-solid fa-box"></i><span><small>Items</small><b>${(order.products || []).reduce((sum,item)=>sum+item.qty,0)} products</b></span></div></div><div class="to-detail-actions"><button type="button" data-track-invoice="${esc(order.number)}"><i class="fa-solid fa-download"></i> Download invoice</button><a href="contact.html"><i class="fa-solid fa-headset"></i> Get support</a></div></article>
      <article class="to-card to-summary"><div class="to-card-head"><h3>Order summary</h3></div><div class="to-products">${productRows(order)}</div><div class="to-price-row"><span>Total MRP</span><b>${money(totalMrp)}</b></div><div class="to-price-row saving"><span>Total savings</span><b>− ${money(savings)}</b></div><div class="to-price-row"><span>Delivery</span><b>FREE</b></div><div class="to-price-row total"><span>Final total</span><b>${money(finalTotal)}</b></div></article></div>
    </section>`;
  }

  function recentOrders() {
    return orders.slice(0,3).map(order => `<article class="to-recent-order"><div class="to-recent-images">${(order.products || []).slice(0,2).map(item => `<img src="${item.image}" alt="" onerror="imageFallback(this)">`).join("")}</div><div><span>${esc(order.status || "Confirmed")}</span><h3>${esc(order.number)}</h3><p>${esc(order.displayDate || new Date(order.createdAt).toLocaleDateString("en-IN"))} · <b>${money(order.totals?.finalTotal || 0)}</b></p></div><div><button type="button" data-track-order="${esc(order.number)}">Track order</button><button type="button" data-track-repeat="${esc(order.number)}">Repeat order</button></div></article>`).join("");
  }

  root.className = "track-page";
  root.innerHTML = `<section class="to-hero"><div class="container to-hero-grid"><div class="to-hero-copy"><span class="eyebrow">Order tracking</span><h1>Track your grocery order</h1><p>Stay updated with real-time delivery progress, estimated arrival and complete order details.</p><div class="to-trust"><span><i class="fa-solid fa-satellite-dish"></i> Live tracking</span><span><i class="fa-solid fa-shield-halved"></i> Secure delivery</span><span><i class="fa-solid fa-bell"></i> Instant updates</span></div></div><div class="to-hero-art" aria-hidden="true"><span class="to-orbit one"><i class="fa-solid fa-box"></i></span><span class="to-orbit two"><i class="fa-solid fa-location-dot"></i></span><div class="to-art-map"><i class="fa-solid fa-store"></i><span></span><i class="fa-solid fa-motorcycle"></i><span></span><i class="fa-solid fa-house"></i></div><div class="to-art-caption"><b>Fresh groceries are on the way</b><small>Live updates from store to doorstep</small></div></div></div></section>
  <div class="container to-content"><section class="to-search-card"><form id="premium-track-form" novalidate><label><span>Order ID</span><div><i class="fa-solid fa-receipt"></i><input name="order" required value="${esc(requested || demoOrder.number)}" placeholder="e.g. FBM2026277772"></div><small data-order-error>Please enter your Order ID.</small></label><label><span>Registered mobile number</span><div><i class="fa-solid fa-mobile-screen"></i><input name="mobile" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" required value="${esc(profile.mobile || "9876543210")}" placeholder="10-digit mobile number"></div><small data-mobile-error>Enter a valid 10-digit mobile number.</small></label><button type="submit"><span>Track order</span><i class="fa-solid fa-arrow-right"></i></button></form><div class="to-lookup"><span>Track using</span>${[["fa-receipt","Order ID"],["fa-mobile-screen","Registered mobile"],["fa-envelope","Email"],["fa-file-invoice","Invoice number"]].map(([icon,label])=>`<button type="button"><i class="fa-solid ${icon}"></i>${label}</button>`).join("")}</div></section>
  <section class="to-section"><div class="to-section-head"><div><span class="eyebrow">Your orders</span><h2>Recent orders</h2><p>Quickly continue tracking orders saved in this browser.</p></div><a href="orders.html">View all orders <i class="fa-solid fa-arrow-right"></i></a></div><div class="to-recent-grid">${recentOrders()}</div></section>
  <div data-premium-track-result>${result(initialOrder)}</div>
  <section class="to-section"><div class="to-section-head"><div><span class="eyebrow">We're here to help</span><h2>Need help with your order?</h2></div></div><div class="to-support-grid">${[["fa-comments","Live chat","Get help from FreshBasket Care","#"],["fa-brands fa-whatsapp","WhatsApp","Message our support team","#"],["fa-phone","Call support","+91 79 4000 1234","tel:+917940001234"],["fa-envelope","Email us","care@freshbasket.demo","mailto:care@freshbasket.demo"],["fa-circle-question","FAQs","Find quick answers","#track-faqs"],["fa-rotate-left","Return policy","Understand returns","return-policy.html"]].map(([icon,title,copy,href])=>`<a href="${href}"><i class="${icon.startsWith("fa-brands")?icon:`fa-solid ${icon}`}"></i><span><b>${title}</b><small>${copy}</small></span><i class="fa-solid fa-arrow-right"></i></a>`).join("")}</div></section>
  <section class="to-section to-faqs" id="track-faqs"><div class="to-section-head"><div><span class="eyebrow">Common questions</span><h2>Order tracking FAQs</h2></div></div>${[["Where is my order?","Enter your Order ID and registered mobile number above to see the latest status."],["How long does delivery take?","The delivery estimate and selected slot appear in your tracking details."],["Can I cancel my order?","Orders can be cancelled before packing from the Orders section where permitted."],["How do I contact the delivery partner?","Contact details appear after your order is out for delivery."],["Can I change the delivery address?","Contact support before packing to check whether an address update is possible."],["When will I receive my refund?","Eligible refunds are usually reflected in the original payment method after processing."]].map(([question,answer],index)=>`<details ${index===0?"open":""}><summary>${question}<i class="fa-solid fa-plus"></i></summary><p>${answer}</p></details>`).join("")}</section></div>`;

  function showOrder(order) {
    $("[data-premium-track-result]").innerHTML = result(order);
    countdownSeconds = 12 * 60;
    updateCountdown();
    $("#tracking-result")?.scrollIntoView({ behavior:matchMedia("(prefers-reduced-motion: reduce)").matches?"auto":"smooth", block:"start" });
  }

  function notFound() {
    $("[data-premium-track-result]").innerHTML = `<section class="to-not-found"><div><i class="fa-solid fa-box-open"></i><span></span></div><h2>No order found</h2><p>Please verify your Order ID or mobile number and try again.</p><div><a class="btn btn-primary" href="shop.html">Go shopping</a><a class="btn btn-outline" href="contact.html">Contact support</a></div></section>`;
  }

  $("#premium-track-form").addEventListener("submit", event => {
    event.preventDefault();
    const form = event.currentTarget, data = new FormData(form), orderNumber = String(data.get("order")).trim(), mobile = String(data.get("mobile")).trim();
    const orderField = form.elements.order, mobileField = form.elements.mobile;
    orderField.closest("label").classList.toggle("has-error", !orderNumber);
    mobileField.closest("label").classList.toggle("has-error", !/^[6-9]\d{9}$/.test(mobile));
    if (!orderNumber || !/^[6-9]\d{9}$/.test(mobile)) return;
    const button = $('button[type="submit"]', form);
    button.classList.add("is-loading"); button.disabled = true;
    requestAnimationFrame(() => {
      const order = orders.find(item => item.number.toLowerCase() === orderNumber.toLowerCase());
      order ? showOrder(order) : notFound();
      button.classList.remove("is-loading"); button.disabled = false;
    });
  });

  document.addEventListener("click", event => {
    const track = event.target.closest("[data-track-order]");
    if (track) { const order = orders.find(item => item.number === track.dataset.trackOrder); if (order) showOrder(order); }
    const repeat = event.target.closest("[data-track-repeat]");
    if (repeat) {
      const order = orders.find(item => item.number === repeat.dataset.trackRepeat), cart = store.get("fb-cart",[]);
      (order?.products || []).forEach(item => { const row=cart.find(entry=>Number(entry.id)===Number(item.id)); row?row.qty+=item.qty:cart.push({id:Number(item.id),qty:item.qty}); });
      store.set("fb-cart",cart); window.FreshBasketHeader?.syncCounts(); window.FBUI.toast("Order items added to basket");
    }
    const invoice = event.target.closest("[data-track-invoice]");
    if (invoice) {
      const order = orders.find(item => item.number === invoice.dataset.trackInvoice);
      const blob = new Blob([`FreshBasket Invoice\r\nOrder: ${order.number}\r\nTotal: ${money(order.totals.finalTotal)}`],{type:"text/plain"}), url=URL.createObjectURL(blob), link=document.createElement("a");
      link.href=url;link.download=`${order.number}-invoice.txt`;link.click();URL.revokeObjectURL(url);
    }
  });

  let countdownSeconds = 12 * 60;
  function updateCountdown() {
    const countdown = $("[data-track-countdown]");
    if (!countdown) return;
    const values = $$("b", countdown);
    const hours = Math.floor(countdownSeconds / 3600);
    const minutes = Math.floor((countdownSeconds % 3600) / 60);
    const seconds = countdownSeconds % 60;
    [hours, minutes, seconds].forEach((value, index) => {
      if (values[index]) values[index].textContent = String(value).padStart(2, "0");
    });
    countdownSeconds = Math.max(0, countdownSeconds - 1);
  }
  updateCountdown();
  window.setInterval(updateCountdown, 1000);
})(window, document);
