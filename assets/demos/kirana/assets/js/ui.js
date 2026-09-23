(function (window, document) {
  "use strict";

  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];
  const fallbackImage = "assets/images/placeholders/product.svg";

  function money(value) {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(Number(value) || 0);
  }

  function query(name, fallback = "") {
    return new URLSearchParams(window.location.search).get(name) ?? fallback;
  }

  function imageFallback(image) {
    if (!image) return;
    image.onerror = null;
    image.src = fallbackImage;
    image.classList.add("is-fallback");
  }

  function toast(message, options = {}) {
    let stack = $(".toast-stack");
    if (!stack) {
      stack = document.createElement("div");
      stack.className = "toast-stack";
      stack.setAttribute("aria-live", "polite");
      document.body.append(stack);
    }
    const item = document.createElement("div");
    item.className = `ui-toast ${options.type ? `ui-toast-${options.type}` : ""}`.trim();
    item.setAttribute("role", "status");
    item.innerHTML = `<span></span><button class="icon-btn" type="button" aria-label="Dismiss">&times;</button>`;
    $("span", item).textContent = String(message);
    stack.append(item);
    const close = () => {
      item.classList.add("is-leaving");
      window.setTimeout(() => item.remove(), 180);
    };
    $("button", item).addEventListener("click", close);
    window.setTimeout(close, options.duration || 2400);
    return item;
  }

  function openModal(options = {}) {
    const overlay = document.createElement("div");
    overlay.className = "ui-overlay";
    overlay.innerHTML = `<section class="ui-modal" role="dialog" aria-modal="true" aria-labelledby="ui-modal-title">
      <header class="ui-modal-header"><h2 id="ui-modal-title"></h2><button class="icon-btn" type="button" data-modal-close aria-label="Close">&times;</button></header>
      <div class="ui-modal-body"></div>
      <footer class="ui-modal-footer"></footer>
    </section>`;
    $("#ui-modal-title", overlay).textContent = options.title || "Details";
    $(".ui-modal-body", overlay).append(
      typeof options.content === "string" ? document.createRange().createContextualFragment(options.content) : options.content || ""
    );
    if (options.footer) $(".ui-modal-footer", overlay).append(
      typeof options.footer === "string" ? document.createRange().createContextualFragment(options.footer) : options.footer
    );
    document.body.append(overlay);
    document.body.classList.add("scroll-lock");
    const close = () => closeModal(overlay);
    $$("[data-modal-close]", overlay).forEach((button) => button.addEventListener("click", close));
    overlay.addEventListener("click", (event) => { if (event.target === overlay) close(); });
    overlay.addEventListener("keydown", (event) => { if (event.key === "Escape") close(); });
    requestAnimationFrame(() => overlay.classList.add("is-open"));
    $("[data-modal-close]", overlay).focus();
    return overlay;
  }

  function closeModal(target = $(".ui-overlay.is-open")) {
    if (!target) return;
    target.classList.remove("is-open");
    document.body.classList.remove("scroll-lock");
    window.setTimeout(() => target.remove(), 180);
  }

  function openDrawer(target) {
    const drawer = typeof target === "string" ? $(target) : target;
    if (!drawer) return;
    let backdrop = $(".ui-drawer-backdrop");
    if (!backdrop) {
      backdrop = document.createElement("button");
      backdrop.type = "button";
      backdrop.className = "ui-drawer-backdrop";
      backdrop.setAttribute("aria-label", "Close drawer");
      document.body.append(backdrop);
    }
    drawer.classList.add("is-open");
    backdrop.classList.add("is-open");
    document.body.classList.add("scroll-lock");
    backdrop.onclick = () => closeDrawer(drawer);
  }

  function closeDrawer(target = $(".ui-drawer.is-open")) {
    const drawer = typeof target === "string" ? $(target) : target;
    drawer?.classList.remove("is-open");
    $(".ui-drawer-backdrop")?.classList.remove("is-open");
    document.body.classList.remove("scroll-lock");
  }

  window.FBUI = Object.freeze({
    $,
    $$,
    money,
    query,
    toast,
    openModal,
    closeModal,
    openDrawer,
    closeDrawer,
    imageFallback,
    fallbackImage
  });
  window.imageFallback = imageFallback;
})(window, document);

