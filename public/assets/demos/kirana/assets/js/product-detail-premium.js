(function (window, document) {
  "use strict";
  const root = document.querySelector("[data-product-details-page]");
  if (!root || !root.querySelector(".pd-above")) return;
  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];
  const params = new URLSearchParams(location.search);
  const product = params.get("slug") ? window.getProductBySlug(params.get("slug")) : window.getProductById(Number(params.get("id")));
  if (!product) return;
  const money = window.FBUI.money;

  function uniqueThumbs() {
    const seen = new Set();
    $$(".pd-thumb").forEach(button => {
      if (seen.has(button.dataset.thumb)) button.remove();
      else seen.add(button.dataset.thumb);
    });
  }

  function gallery() {
    uniqueThumbs();
    const stage = $(".pd-main-image");
    stage.insertAdjacentHTML("beforeend", `<button class="pd-zoom-button" type="button" data-pd-fullscreen aria-label="Open fullscreen gallery"><i class="fa-solid fa-expand"></i></button>`);
    const images = $$(".pd-thumb").map(node => node.dataset.thumb);
    if (!images.length) images.push(product.image);
    document.body.insertAdjacentHTML("beforeend", `<div class="pd-lightbox" role="dialog" aria-modal="true" aria-label="${product.name} image gallery" hidden><button class="pd-lightbox-close" type="button" data-lightbox-close aria-label="Close gallery"><i class="fa-solid fa-xmark"></i></button><button class="pd-lightbox-nav is-prev" type="button" data-lightbox-prev aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button><img src="${images[0]}" alt="${product.imageAlt || product.name}" data-lightbox-image onerror="imageFallback(this)"><button class="pd-lightbox-nav is-next" type="button" data-lightbox-next aria-label="Next image"><i class="fa-solid fa-chevron-right"></i></button><span data-lightbox-count>1 / ${images.length}</span></div>`);
    let index = 0;
    const lightbox = $(".pd-lightbox");
    const show = next => {
      index = (next + images.length) % images.length;
      $("[data-lightbox-image]").src = images[index];
      $("[data-lightbox-count]").textContent = `${index + 1} / ${images.length}`;
    };
    const open = () => { lightbox.hidden = false; document.body.classList.add("scroll-lock"); show(images.indexOf($("#pd-main-image").src) >= 0 ? images.indexOf($("#pd-main-image").src) : 0); $("[data-lightbox-close]").focus(); };
    const close = () => { lightbox.hidden = true; document.body.classList.remove("scroll-lock"); $("[data-pd-fullscreen]").focus(); };
    document.addEventListener("click", event => {
      if (event.target.closest("[data-pd-fullscreen]")) open();
      if (event.target.closest("[data-lightbox-close]")) close();
      if (event.target.closest("[data-lightbox-prev]")) show(index - 1);
      if (event.target.closest("[data-lightbox-next]")) show(index + 1);
    });
    $("#pd-main-image")?.addEventListener("click", () => open());
    document.addEventListener("keydown", event => {
      if (lightbox.hidden) return;
      if (event.key === "Escape") close();
      if (event.key === "ArrowLeft") show(index - 1);
      if (event.key === "ArrowRight") show(index + 1);
    });
    let touchX = 0;
    lightbox.addEventListener("touchstart", event => touchX = event.changedTouches[0].clientX, { passive: true });
    lightbox.addEventListener("touchend", event => {
      const delta = event.changedTouches[0].clientX - touchX;
      if (Math.abs(delta) > 50) show(index + (delta < 0 ? 1 : -1));
    }, { passive: true });
  }

  function enrichProductInfo() {
    const info = $(".pd-info");
    const unit = parseFloat(product.weight);
    const unitLabel = /kg/i.test(product.weight) && unit ? `${money(product.sellingPrice / unit)} per kg` : "";
    $(".pd-price", info)?.insertAdjacentHTML("afterend", `<div class="pd-price-support"><strong>You Save ${money(product.savings)}</strong>${unitLabel ? `<span>${unitLabel}</span>` : ""}<small>Inclusive of all taxes</small></div>`);
    $(".pd-tax", info)?.remove();
    const badgeRow = document.createElement("div");
    badgeRow.className = "pd-badge-row";
    badgeRow.innerHTML = `${product.bestseller ? '<span><i class="fa-solid fa-award"></i> Best seller</span>' : ""}${product.featured ? '<span><i class="fa-solid fa-check"></i> Featured</span>' : ""}${product.stock < 10 ? '<span class="is-warning">Limited stock</span>' : ""}`;
    info.insertBefore(badgeRow, info.firstChild);
    $$(".pd-pack").forEach(link => {
      const id = Number(new URL(link.href).searchParams.get("id"));
      const item = window.getProductById(id);
      if (!item) return;
      link.innerHTML = `<b>${item.weight}</b><strong>${money(item.sellingPrice)}</strong><small>Save ${money(item.savings)}</small>${link.classList.contains("is-active") ? '<i class="fa-solid fa-circle-check"></i>' : ""}`;
    });
    $(".pd-secondary-actions", info)?.insertAdjacentHTML("afterend", `<div class="pd-trust-strip"><span><i class="fa-solid fa-circle-check"></i> Quality checked</span><span><i class="fa-solid fa-box"></i> Secure packaging</span><span><i class="fa-solid fa-rotate-left"></i> Easy returns</span><span><i class="fa-solid fa-truck-fast"></i> Same-day delivery</span></div>`);
  }

  function offers() {
    $$(".pd-offer").forEach(offer => {
      const code = $("b", offer)?.textContent.trim();
      if (!code) return;
      const benefit = offer.textContent.replace(code, "").replace(/^[\s--]+/, "");
      offer.innerHTML = `<i class="fa-solid fa-ticket"></i><span><b>${code}</b><small>${benefit}</small></span><button type="button" data-copy-offer="${code}">Copy</button>`;
    });
    document.addEventListener("click", event => {
      const button = event.target.closest("[data-copy-offer]");
      if (!button) return;
      navigator.clipboard?.writeText(button.dataset.copyOffer).catch(() => {});
      button.textContent = "Copied";
      window.FBUI.toast(`${button.dataset.copyOffer} copied`);
      setTimeout(() => button.textContent = "Copy", 1400);
    });
  }

  function detailsNavigation() {
    const details = $(".pd-details");
    const cards = $$(".pd-content-card", details);
    if (cards[0]) cards[0].id = "pd-highlights";
    if (cards[1]) cards[1].id = "pd-information";
    const reviews = $("#customer-reviews");
    details.insertAdjacentHTML("beforebegin", `<nav class="pd-detail-tabs" aria-label="Product information"><a href="#pd-highlights">Highlights</a><a href="#pd-highlights">Description</a><a href="#pd-information">Product information</a><a href="#pd-information">Nutrition</a><a href="#customer-reviews">Reviews</a><a href="#pd-delivery-panel">Delivery & returns</a></nav>`);
    $(".pd-buy-panel").id = "pd-delivery-panel";
    if (reviews) {
      const firstReview = $(".pd-review", reviews);
      if (firstReview) firstReview.innerHTML = `<div class="pd-review-head"><span class="pd-avatar">VS</span><span><b>Verified shopper</b><small><i class="fa-solid fa-circle-check"></i> Verified buyer · Purchased ${product.weight}</small></span><strong>★★★★★</strong></div><h3>Dependable everyday quality</h3><p>Good quality, secure packaging and dependable delivery.</p><button type="button" class="pd-helpful"><i class="fa-regular fa-thumbs-up"></i> Helpful</button>`;
      const form = $(".pd-review-form", reviews);
      if (form) {
        const select = $('select[name="rating"]', form);
        select?.insertAdjacentHTML("beforebegin", `<fieldset class="pd-star-picker"><legend>Your rating</legend>${[5,4,3,2,1].map(value => `<input id="pd-star-${value}" type="radio" name="visual-rating" value="${value}"><label for="pd-star-${value}" title="${value} stars">★</label>`).join("")}</fieldset>`);
        form.insertAdjacentHTML("afterbegin", `<p>Share your experience with other shoppers.</p>`);
        document.addEventListener("change", event => { if (event.target.name === "visual-rating" && select) select.value = event.target.value; });
      }
    }
  }

  function bundle() {
    const row = $("#pd-frequent");
    if (!row) return;
    const section = row.closest(".pd-product-section");
    const cards = $$(".product-card", row).slice(0, 3);
    if (!cards.length) return;
    const ids = cards.map(card => Number(card.dataset.productId));
    section.classList.add("pd-bundle-section");
    row.classList.add("pd-bundle-products");
    cards.forEach((card, index) => card.insertAdjacentHTML("afterbegin", `<label class="pd-bundle-check"><input type="checkbox" value="${ids[index]}" checked><span>✓</span></label>${index ? '<i class="pd-bundle-plus">+</i>' : ""}`));
    row.insertAdjacentHTML("afterend", `<aside class="pd-bundle-summary"><span>Bundle offer</span><h3 data-bundle-count>Buy all ${ids.length} products</h3><strong data-bundle-total></strong><small data-bundle-save></small><button class="btn btn-primary" type="button" data-bundle-add><i class="fa-solid fa-basket-shopping"></i> Add selected to basket</button></aside>`);
    const update = () => {
      const selected = $$(".pd-bundle-check input:checked", section).map(input => window.getProductById(Number(input.value))).filter(Boolean);
      $("[data-bundle-count]", section).textContent = `Buy ${selected.length} selected products`;
      $("[data-bundle-total]", section).textContent = money(selected.reduce((sum, item) => sum + item.sellingPrice, 0));
      $("[data-bundle-save]", section).textContent = `You save ${money(selected.reduce((sum, item) => sum + item.savings, 0))}`;
    };
    section.addEventListener("change", update);
    section.addEventListener("click", event => {
      if (!event.target.closest("[data-bundle-add]")) return;
      $$(".pd-bundle-check input:checked", section).forEach(input => window.addCart(Number(input.value)));
      window.FBUI.toast("Selected bundle added to basket");
    });
    update();
  }

  function recent() {
    const row = $("#pd-recent");
    if (!row) {
      $$(".pd-product-section").find(section => /recently viewed/i.test($("h2", section)?.textContent || ""))?.remove();
      return;
    }
    if (!row.children.length) row.closest(".pd-product-section")?.remove();
  }

  gallery();
  enrichProductInfo();
  offers();
  detailsNavigation();
  bundle();
  recent();
})(window, document);
