(function (window, document) {
  "use strict";
  const page = document.querySelector('[data-shopping-page="cart"]');
  if (!page || !window.FBShopping) return;
  const store = window.FBStorage, money = window.FBUI.money;
  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];
  document.body.classList.add("cart-premium");

  function cartRows() {
    return store.get("fb-cart", []).map(row => ({ id:Number(row.id), qty:Number(row.qty) || 1, item:window.getProductById(Number(row.id)) })).filter(row => row.item);
  }

  function enhanceHero() {
    const hero = $(".page-hero");
    if (!hero) return;
    hero.classList.add("cart-hero");
    const container = $(".container", hero);
    if (!container.dataset.cartHero) {
      container.dataset.cartHero = "true";
      const breadcrumb = $(".breadcrumb", container);
      breadcrumb.insertAdjacentHTML("afterend", `<span class="eyebrow">Your shopping basket</span>`);
      $("h1", container).textContent = "Review your basket";
      $("p", container).textContent = "Check your items, apply available offers and choose delivery before checkout.";
      container.insertAdjacentHTML("beforeend", `<div class="cart-hero-chips" data-cart-hero-chips></div><ol class="cart-progress" aria-label="Checkout progress"><li class="is-active"><span>1</span><b>Basket</b></li><li><span>2</span><b>Delivery</b></li><li><span>3</span><b>Payment</b></li><li><span>4</span><b>Confirmation</b></li></ol>`);
    }
    updateHero();
  }

  function updateHero() {
    const rows = cartRows(), totalItems = rows.reduce((sum, row) => sum + row.qty, 0), totals = window.FBShopping.totals();
    const chips = $("[data-cart-hero-chips]");
    if (chips) chips.innerHTML = `<span><i class="fa-solid fa-basket-shopping"></i> ${totalItems} ${totalItems === 1 ? "item" : "items"}</span><span><i class="fa-solid fa-tags"></i> You save ${money(totals.totalSavings)}</span><span><i class="fa-solid fa-truck-fast"></i> ${totals.fulfilment === "pickup" ? "Pickup available" : "Delivery available today"}</span>`;
  }

  function enhanceItems(host) {
    const rows = cartRows();
    const head = $(".shopping-panel:not(.shopping-summary) .shopping-panel-head", host);
    if (head) {
      const count = rows.reduce((sum, row) => sum + row.qty, 0);
      $("h2", head).innerHTML = `Your basket <span>${count} ${count === 1 ? "item" : "items"}</span>`;
      const link = $("a", head);
      if (link) link.innerHTML = `<i class="fa-solid fa-arrow-left"></i> Continue shopping`;
    }
    $$(".shopping-item", host).forEach(item => {
      if (item.dataset.premium) return;
      item.dataset.premium = "true";
      const qtyButton = $("[data-cart-qty]", item), id = Number(qtyButton?.dataset.cartQty), product = window.getProductById(id);
      const row = rows.find(entry => entry.id === id);
      if (!product || !row) return;
      const content = item.children[1], meta = $(".shopping-meta", content), title = $("h3", content);
      if (meta && title) {
        meta.textContent = product.weight;
        title.insertAdjacentHTML("beforebegin", `<span class="shopping-brand">${product.brand}</span>`);
        title.insertAdjacentHTML("afterend", `<div class="shopping-rating"><i class="fa-solid fa-star"></i> ${product.rating} <span>(${product.reviewCount})</span></div>`);
      }
      $(".shopping-price", content)?.insertAdjacentHTML("beforeend", `<span class="shopping-discount">${product.discount}% OFF</span>`);
      const actions = $(".shopping-actions", content);
      if (actions) {
        const labels = [
          ["[data-cart-remove]","fa-trash-can"],["[data-cart-wish]","fa-heart"],["[data-cart-save]","fa-bookmark"]
        ];
        labels.forEach(([selector, icon]) => { const button = $(selector, actions); if (button) button.insertAdjacentHTML("afterbegin", `<i class="fa-regular ${icon}"></i>`); });
      }
      $(".shopping-qty", item)?.insertAdjacentHTML("beforebegin", `<div class="shopping-line-total"><small>Item total</small><strong>${money(product.sellingPrice * row.qty)}</strong></div>`);
    });
  }

  function enhanceSummary(host) {
    const summary = $(".shopping-summary", host);
    if (!summary || summary.dataset.premium) return;
    summary.dataset.premium = "true";
    const totals = window.FBShopping.totals(), rows = cartRows();
    $("h2", summary).insertAdjacentHTML("beforeend", `<span>${rows.reduce((sum,row) => sum + row.qty,0)} items</span>`);
    const fulfilment = $(".fulfilment-options", summary);
    $$(".fulfilment-option", fulfilment).forEach(label => {
      const input = $("input", label), icon = input.value === "delivery" ? "fa-truck-fast" : "fa-store";
      input.insertAdjacentHTML("afterend", `<i class="fa-solid ${icon}"></i>`);
      $("span", label)?.insertAdjacentHTML("beforeend", `<em>${input.value === "delivery" ? (totals.delivery ? `${money(totals.delivery)} delivery` : "Free delivery") : "Free pickup"}</em>`);
    });
    const location = store.get("fb-location", { area:"Gota", city:"Ahmedabad", pin:"380060", label:"Gota, Ahmedabad 380060" });
    fulfilment.insertAdjacentHTML("afterend", `<div class="cart-location-summary"><i class="fa-solid fa-location-dot"></i><span><small>${totals.fulfilment === "pickup" ? "Pickup from" : "Delivering to"}</small><b>${totals.fulfilment === "pickup" ? "FreshBasket Gota Store" : (location.label || `${location.area}, ${location.city} ${location.pin}`)}</b></span><button type="button" data-cart-location>${totals.fulfilment === "pickup" ? "Change pickup" : "Change address"}</button></div>`);
    const suggestions = $(".coupon-suggestions", summary);
    if (suggestions) {
      suggestions.insertAdjacentHTML("beforebegin", `<button class="coupon-toggle" type="button" data-coupon-toggle aria-expanded="false"><span><i class="fa-solid fa-ticket"></i> View available coupons</span><i class="fa-solid fa-chevron-down"></i></button>`);
      suggestions.hidden = true;
      $$(".coupon-suggestion", suggestions).forEach(card => {
        card.insertAdjacentHTML("afterbegin", `<i class="fa-solid fa-ticket"></i>`);
        card.insertAdjacentHTML("beforeend", `<span class="coupon-apply">Apply</span>`);
      });
    }
    const couponMessage = $("#coupon-message", summary);
    if (couponMessage) couponMessage.setAttribute("aria-live","polite");
    const firstPriceRow = $(".summary-row", summary);
    firstPriceRow?.insertAdjacentHTML("beforebegin", `<div class="cart-price-heading"><h3>Price details</h3><span>${rows.length} products</span></div>`);
    const saving = $(".summary-saving", summary);
    if (saving) {
      const percent = totals.totalMrp ? Math.round(totals.totalSavings / totals.totalMrp * 100) : 0;
      saving.innerHTML = `<i class="fa-solid fa-piggy-bank"></i><span><b>You saved ${money(totals.totalSavings)}</b><small>That's ${percent}% saved on this basket.</small></span>`;
    }
    const checkout = $('a[href="checkout.html"]', summary);
    if (checkout) {
      checkout.innerHTML = `<i class="fa-solid fa-lock"></i> Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>`;
      checkout.insertAdjacentHTML("afterend", `<p class="cart-secure-note"><i class="fa-solid fa-shield-halved"></i> Secure checkout · Multiple payment options</p>`);
    }
    const remaining = Math.max(0, 499 - totals.subtotal), progress = Math.min(100, totals.subtotal / 499 * 100);
    summary.insertAdjacentHTML("afterbegin", `<div class="cart-delivery-progress"><span>${remaining ? `Add ${money(remaining)} more for free delivery` : "You unlocked free delivery"}</span><a href="shop.html">Shop products</a><div><i style="width:${progress}%"></i></div></div>`);
  }

  function renderSaved(host) {
    const ids = store.get("fb-saved", []).map(Number), products = ids.map(window.getProductById).filter(Boolean);
    const current = $(".cart-saved-section", host), signature = ids.join(",");
    if (!products.length) { current?.remove(); return; }
    if (current?.dataset.savedSignature === signature) return;
    current?.remove();
    host.insertAdjacentHTML("beforeend", `<section class="cart-saved-section"><div class="shopping-panel-head"><h2>Saved for later <span>${products.length}</span></h2></div><div class="cart-saved-grid">${products.map(item => `<article><a href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt || item.name}" onerror="imageFallback(this)"></a><div><span>${item.brand}</span><h3>${item.name}</h3><strong>${money(item.sellingPrice)}</strong><div><button type="button" data-saved-cart="${item.id}">Move to basket</button><button type="button" data-saved-remove="${item.id}">Remove</button></div></div></article>`).join("")}</div></section>`);
    $(".cart-saved-section", host).dataset.savedSignature = signature;
  }

  function recommendations() {
    const target = $("#cart-recommendations");
    if (!target) return;
    const rows = cartRows(), cartIds = new Set(rows.map(row => row.id)), categories = new Set(rows.map(row => row.item.categorySlug));
    let products = window.PRODUCTS.filter(item => !cartIds.has(item.id) && item.stock > 0 && categories.has(item.categorySlug));
    const homeCart = rows.some(row => /appliance|home|laundry|clean/i.test(`${row.item.categorySlug} ${row.item.tags?.join(" ")}`));
    if (homeCart) products = window.PRODUCTS.filter(item => !cartIds.has(item.id) && item.stock > 0 && /appliance|home|laundry|clean/i.test(`${item.categorySlug} ${item.tags?.join(" ")}`));
    const extras = window.PRODUCTS.filter(item => !cartIds.has(item.id) && item.stock > 0 && (item.bestseller || item.featured));
    products = [...new Map([...products, ...extras].map(item => [item.id, item])).values()].slice(0, 6);
    target.innerHTML = products.map(window.FreshBasket.renderProductCard).join("");
    target.closest(".section").hidden = !products.length;
  }

  function mobileBar(host) {
    $(".cart-mobile-checkout")?.remove();
    if (!cartRows().length) return;
    const totals = window.FBShopping.totals();
    document.body.insertAdjacentHTML("beforeend", `<div class="cart-mobile-checkout"><span><strong>${money(totals.finalTotal)}</strong><small>You save ${money(totals.totalSavings)}</small></span><a href="checkout.html"><i class="fa-solid fa-lock"></i> Checkout</a></div>`);
  }

  function emptyState(host) {
    const empty = $(".shopping-empty", host);
    if (!empty || empty.dataset.premium) return;
    empty.dataset.premium = "true";
    empty.innerHTML = `<div class="cart-empty-art"><i class="fa-solid fa-basket-shopping"></i><span></span></div><span class="eyebrow">Your shopping basket</span><h2>Your basket is waiting</h2><p>Add fresh groceries and household essentials to start your order.</p><div><a class="btn btn-primary" href="shop.html">Shop groceries</a><a class="btn btn-outline" href="offers.html">Browse offers</a></div>`;
  }

  function enhance() {
    const host = $("#shopping-cart");
    if (!host) return;
    enhanceHero();
    if (cartRows().length) {
      enhanceItems(host); enhanceSummary(host); renderSaved(host);
    } else emptyState(host);
    recommendations();
    mobileBar(host);
  }

  document.addEventListener("click", event => {
    const toggle = event.target.closest("[data-coupon-toggle]");
    if (toggle) {
      const list = toggle.nextElementSibling, open = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!open)); list.hidden = open;
      toggle.classList.toggle("is-open", !open);
    }
    if (event.target.closest("[data-cart-location]")) window.FBUI.toast("Choose your delivery location from the header");
  });

  document.addEventListener("click", event => {
    const remove = event.target.closest("[data-cart-remove]");
    if (!remove || remove.dataset.confirmed) return;
    event.preventDefault(); event.stopImmediatePropagation();
    const id = Number(remove.dataset.cartRemove), item = window.getProductById(id);
    const modal = window.FBUI.openModal({ title:"Remove item?", content:`<div class="cart-remove-confirm"><img src="${item.image}" alt=""><p>Remove <strong>${item.name}</strong> from your basket?</p><div><button class="btn btn-outline" type="button" data-remove-cancel>Cancel</button><button class="btn btn-primary" type="button" data-remove-confirm="${id}">Remove item</button></div></div>` });
    $("[data-remove-cancel]", modal).onclick = () => window.FBUI.closeModal(modal);
    $("[data-remove-confirm]", modal).onclick = () => { window.removeCart(id); window.FBUI.closeModal(modal); };
  }, true);

  const host = $("#shopping-cart");
  new MutationObserver(() => requestAnimationFrame(enhance)).observe(host, { childList:true });
  enhance();
})(window, document);
