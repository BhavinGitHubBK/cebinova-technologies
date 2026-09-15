(function (window, document) {
  "use strict";
  if(!document.querySelector('link[href="assets/css/shared-layout.css"]')){const shared=document.createElement("link");shared.rel="stylesheet";shared.href="assets/css/shared-layout.css";document.head.appendChild(shared)}
  if(!document.querySelector('link[href="assets/css/heading-system.css"]')){const headings=document.createElement("link");headings.rel="stylesheet";headings.href="assets/css/heading-system.css";document.head.appendChild(headings)}

  function highlightTitle(title) {
    if (!title || title.dataset.headingEnhanced || title.querySelector(".section-heading__accent")) return;
    const text = title.textContent.trim();
    const words = text.split(/\s+/);
    if (words.length < 2) return;
    const accentCount = words.length > 4 ? 2 : 1;
    const plain = words.slice(0, -accentCount).join(" ");
    const accent = words.slice(-accentCount).join(" ");
    title.textContent = "";
    title.append(document.createTextNode(`${plain} `));
    const mark = document.createElement("span");
    mark.className = "section-heading__accent";
    mark.textContent = accent;
    title.append(mark);
    title.dataset.headingEnhanced = "true";
  }

  function enhanceHeadings(root) {
    const scope = root?.querySelectorAll ? root : document;
    scope.querySelectorAll(".section-head:not(.section-heading)").forEach((heading) => {
      heading.classList.add("section-heading", "section-heading--default");
      const content = heading.firstElementChild;
      content?.classList.add("section-heading__content");
      const eyebrow = content?.querySelector(".eyebrow");
      eyebrow?.classList.add("section-heading__eyebrow");
      const title = content?.querySelector("h2,h3");
      title?.classList.add("section-heading__title");
      highlightTitle(title);
      content?.querySelector("p")?.classList.add("section-heading__description");
      const action = Array.from(heading.children).find((child) => child.matches?.("a,button"));
      action?.classList.add("section-heading__action");
      observeHeading(heading);
    });
    scope.querySelectorAll(".page-hero .container:not(.page-heading),.catalog-hero .container:not(.page-heading)").forEach((heading) => {
      heading.classList.add("page-heading");
      const eyebrow = heading.querySelector(".eyebrow");
      eyebrow?.classList.add("section-heading__eyebrow");
      const title = heading.querySelector("h1");
      title?.classList.add("page-heading__title");
      highlightTitle(title);
      heading.querySelector("p")?.classList.add("page-heading__description");
      observeHeading(heading);
    });
  }

  let headingObserver;
  function observeHeading(heading) {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches || !("IntersectionObserver" in window)) {
      heading.classList.add("is-heading-visible");
      return;
    }
    if (!headingObserver) headingObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-heading-visible");
        headingObserver.unobserve(entry.target);
      });
    }, { threshold: .18 });
    headingObserver.observe(heading);
  }

  function productCard(product) {
    if (!product) return "";
    const cart = window.FBStorage.get("fb-cart", []);
    const wishlist = window.FBStorage.get("fb-wish", []);
    const inCart = cart.find((item) => Number(item.id) === Number(product.id));
    const discount = Math.max(0, Math.round((product.mrp - product.price) / product.mrp * 100));
    return `<article class="product-card" data-product-id="${product.id}">
      <a href="product-details.html?id=${product.id}" class="product-image">
        <img src="${product.image}" width="400" height="300" loading="lazy" alt="${product.name}" onerror="imageFallback(this)">
      </a>
      ${discount ? `<span class="discount">${discount}% OFF</span>` : ""}
      <button class="wish ${wishlist.includes(product.id) ? "active" : ""}" type="button" onclick="toggleWish(${product.id})" aria-label="Save ${product.name}">
        <i class="${wishlist.includes(product.id) ? "fa-solid" : "fa-regular"} fa-heart"></i>
      </button>
      <div class="product-meta">${product.brand} · ${product.weight}</div>
      <a href="product-details.html?id=${product.id}"><h3 class="product-name">${product.name}</h3></a>
      <div class="rating"><i class="fa-solid fa-star"></i> ${product.rating} <span>(${product.reviews})</span></div>
      <div class="price-row"><span class="price">${window.FBUI.money(product.price)}</span><span class="mrp">${window.FBUI.money(product.mrp)}</span></div>
      <div class="delivery"><i class="fa-solid fa-bolt"></i> ${product.delivery}</div>
      ${inCart
        ? `<div class="qty"><button type="button" onclick="qty(${product.id},-1)" aria-label="Decrease">−</button><b>${inCart.qty}</b><button type="button" onclick="qty(${product.id},1)" aria-label="Increase">+</button></div>`
        : `<button class="btn btn-primary btn-block" type="button" onclick="addCart(${product.id})"><i class="fa-solid fa-basket-shopping"></i> Add to basket</button>`}
    </article>`;
  }

  function counts() {
    window.FreshBasketHeader?.syncCounts();
    return {
      cart: window.FBStorage.get("fb-cart", []).reduce((sum, item) => sum + Number(item.qty || 0), 0),
      wishlist: window.FBStorage.get("fb-wish", []).length
    };
  }

  function init() {
    window.FreshBasketHeader?.init();
    window.FreshBasketFooter?.init();
    enhanceHeadings(document);
    const headingChanges = new MutationObserver((records) => records.forEach((record) => record.addedNodes.forEach((node) => {
      if (node.nodeType === 1) enhanceHeadings(node);
    })));
    headingChanges.observe(document.body, { childList: true, subtree: true });
    document.documentElement.classList.add("js-ready");
  }

  window.FreshBasket = Object.freeze({
    init,
    formatCurrency: window.FBUI.money,
    getQueryParam: window.FBUI.query,
    storage: window.FBStorage,
    toast: window.FBUI.toast,
    modal: { open: window.FBUI.openModal, close: window.FBUI.closeModal },
    drawer: { open: window.FBUI.openDrawer, close: window.FBUI.closeDrawer },
    renderProductCard: productCard,
    syncCounts: counts
  });

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
  if (!/(^|\/)index\.html$/i.test(location.pathname) && location.pathname.split("/").pop()) {
    const premiumScript = document.createElement("script");
    premiumScript.src = "assets/js/internal-pages-premium.js";
    premiumScript.defer = true;
    document.body.appendChild(premiumScript);
  }
})(window, document);
