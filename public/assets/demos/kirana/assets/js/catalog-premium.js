(function (window, document) {
  "use strict";
  const root = document.querySelector("[data-catalog-page]");
  if (!root || !window.FreshBasketListing) return;
  const state = window.FreshBasketListing.state;
  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];
  const mode = root.dataset.catalogPage;

  function activeCount() {
    return state.categories.length + state.subcategories.length + state.brands.length +
      Number(state.minPrice > 0) + Number(state.maxPrice < 5000) +
      Number(Boolean(state.discount)) + Number(Boolean(state.rating)) +
      Number(state.availability) + Number(Boolean(state.dietary)) + Number(Boolean(state.pack));
  }

  function enhanceHero() {
    const copy = $(".catalog-hero-copy");
    if (!copy || $(".catalog-summary", copy)) return;
    const total = (window.PRODUCTS || []).length;
    copy.insertAdjacentHTML("beforeend", `<div class="catalog-hero-actions"><a href="#catalog-grid"><i class="fa-solid fa-basket-shopping"></i> Shop essentials</a><a href="offers.html">Explore offers <i class="fa-solid fa-arrow-right"></i></a></div><div class="catalog-summary"><span><i class="fa-solid fa-box-open"></i><b>${total}+</b><small>quality products</small></span><span><i class="fa-solid fa-table-cells-large"></i><b>30</b><small>departments</small></span><span><i class="fa-solid fa-truck-fast"></i><b>45 min</b><small>fast delivery</small></span></div>`);
    const artwork = $(".catalog-banner-image");
    if (artwork && !$(".catalog-art-badge", artwork)) {
      artwork.insertAdjacentHTML("afterbegin", `<span class="catalog-art-kicker">Pantry favourite</span><span class="catalog-art-shape one"></span><span class="catalog-art-shape two"></span>`);
      artwork.insertAdjacentHTML("beforeend", `<div class="catalog-art-badge"><i class="fa-solid fa-shield-heart"></i><span><b>Quality checked</b><small>FreshBasket assured</small></span></div>`);
    }
    if (mode === "shop") {
      const chips = [
        ["", "All products"], ["rice-atta-and-grains", "Grocery"],
        ["fruits-and-vegetables", "Fresh produce"], ["dairy-and-bakery", "Dairy"],
        ["snacks-and-namkeen", "Snacks"], ["beverages-and-juices", "Beverages"],
        ["personal-care", "Personal care"], ["household-cleaning", "Home care"],
        ["baby-care", "Baby care"]
      ];
      const strip = $("#catalog-subcategories");
      strip.innerHTML = chips.map(([value, label], index) =>
        `<button class="catalog-subcategory ${index === 0 ? "is-active" : ""}" type="button" data-premium-category="${value}">${label}</button>`
      ).join("");
    }
  }

  function enhanceSort() {
    const select = $("#catalog-sort");
    if (!select || select.dataset.customReady) return;
    select.dataset.customReady = "true";
    select.classList.add("catalog-native-sort");
    const options = [...select.options];
    select.insertAdjacentHTML("afterend", `<div class="catalog-sort-menu" data-custom-sort><button class="catalog-sort-trigger" type="button" aria-haspopup="listbox" aria-expanded="false"><span><small>Sort by</small><b data-sort-label>${options.find(option => option.selected)?.textContent || options[0]?.textContent}</b></span><i class="fa-solid fa-chevron-down"></i></button><div class="catalog-sort-options" role="listbox" tabindex="-1">${options.map(option => `<button type="button" role="option" data-sort-value="${option.value}" aria-selected="${option.selected}"><span><i class="fa-solid fa-check"></i>${option.textContent}</span>${option.value === "popularity" ? `<small>Most purchased</small>` : option.value === "discount" ? `<small>Biggest savings</small>` : ""}</button>`).join("")}</div></div>`);
  }

  function enhanceFilters(context) {
    if (!context || context.dataset.premiumReady) return;
    context.dataset.premiumReady = "true";
    context.insertAdjacentHTML("afterbegin", `<div class="catalog-filter-head"><div><strong>Filters</strong><span data-premium-filter-count>0 active</span></div><button type="button" data-clear-filters disabled>Clear all</button></div><div class="catalog-filter-summary" data-filter-summary hidden></div><label class="catalog-filter-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Search categories or brands" data-premium-filter-search aria-label="Search categories or brands"><button type="button" data-filter-search-clear aria-label="Clear filter search" hidden><i class="fa-solid fa-xmark"></i></button></label><p class="catalog-filter-empty" data-filter-empty hidden role="status">No matching filters found</p>`);
    $$(".catalog-filter-group", context).forEach((group, index) => {
      const heading = $("h3", group);
      if (!heading) return;
      const open = /category|price|availability/i.test(heading.textContent);
      const content = document.createElement("div");
      content.className = "catalog-filter-content";
      [...group.children].filter(node => node !== heading).forEach(node => content.appendChild(node));
      heading.replaceWith(Object.assign(document.createElement("button"), {
        className: "catalog-filter-toggle",
        type: "button",
        innerHTML: `<span>${heading.textContent}</span><i class="fa-solid fa-chevron-down"></i>`
      }));
      $(".catalog-filter-toggle", group).setAttribute("aria-expanded", String(open));
      group.appendChild(content);
      group.classList.toggle("is-open", open || index === 0);
      $$(".catalog-filter-option", group).forEach(option => {
        const input = $("input", option);
        if (input && !$(".catalog-check", option)) input.insertAdjacentHTML("afterend", `<span class="catalog-check" aria-hidden="true"></span>`);
        if (input && !option.querySelector("small")) {
          const count = optionCount(input);
          option.insertAdjacentHTML("beforeend", `<small aria-label="${count} products">${count}</small>`);
          if (count === 0) { input.disabled = true; option.classList.add("is-disabled"); }
        }
      });
      const title = $(".catalog-filter-toggle span", group)?.textContent.trim().toLowerCase();
      compactGroup(group, 5);
      if (title === "price") {
        const content = $(".catalog-filter-content", group);
        content?.insertAdjacentHTML("afterbegin", `<div class="catalog-price-summary" data-price-summary>₹0 - ₹5,000</div><div class="catalog-dual-range"><input type="range" min="0" max="5000" step="50" value="0" data-range-min aria-label="Minimum price"><input type="range" min="0" max="5000" step="50" value="5000" data-range-max aria-label="Maximum price"></div>`);
      }
      const stored = sessionStorage.getItem(`fb-filter-group-${title}`);
      if (stored !== null) {
        group.classList.toggle("is-open", stored === "open");
        $(".catalog-filter-toggle", group)?.setAttribute("aria-expanded", String(stored === "open"));
      }
    });
    const scroll = document.createElement("div");
    scroll.className = "catalog-filter-scroll";
    $$(".catalog-filter-group", context).forEach(group => scroll.appendChild(group));
    context.appendChild(scroll);
    context.insertAdjacentHTML("beforeend", `<div class="catalog-scroll-fade is-top" aria-hidden="true"></div><div class="catalog-scroll-fade is-bottom" aria-hidden="true"></div>`);
    const updateFade = () => {
      context.classList.toggle("has-scroll-above", scroll.scrollTop > 6);
      context.classList.toggle("has-scroll-below", scroll.scrollTop + scroll.clientHeight < scroll.scrollHeight - 6);
    };
    scroll.addEventListener("scroll", updateFade, { passive:true });
    requestAnimationFrame(updateFade);
  }

  function optionCount(input) {
    const products = window.PRODUCTS || [];
    if (input.dataset.filter === "categories") return products.filter(item => item.categorySlug === input.value).length;
    if (input.dataset.filter === "subcategories") return products.filter(item => item.subcategorySlug === input.value).length;
    if (input.dataset.filter === "brands") return products.filter(item => String(item.brand).toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "") === input.value).length;
    if (input.dataset.singleFilter === "discount") return products.filter(item => item.discount >= Number(input.value)).length;
    if (input.dataset.singleFilter === "rating") return products.filter(item => item.rating >= Number(input.value)).length;
    if (input.hasAttribute("data-availability")) return products.filter(item => item.stock > 0).length;
    return products.length;
  }

  function compactGroup(group, limit) {
    const options = $$(".catalog-filter-option", group);
    if (options.length <= limit) return;
    options.forEach((option, index) => {
      option.dataset.limitIndex = String(index);
      if (index >= limit) {
        option.hidden = true;
        option.dataset.limitHidden = "true";
      }
    });
    group.dataset.filterLimit = String(limit);
    $(".catalog-filter-content", group).insertAdjacentHTML("beforeend", `<button class="catalog-filter-more" type="button" data-filter-more aria-expanded="false">Show ${options.length - limit} more <i class="fa-solid fa-chevron-down"></i></button>`);
  }

  function filterLabels() {
    const labels = [];
    ["categories","subcategories","brands"].forEach(key => state[key].forEach(value => labels.push({ key, value, label:value.replace(/-/g," ") })));
    if (state.minPrice > 0 || state.maxPrice < 5000) labels.push({ key:"price", label:`₹${state.minPrice}–₹${state.maxPrice}` });
    if (state.discount) labels.push({ key:"discount", label:`${state.discount}%+ off` });
    if (state.rating) labels.push({ key:"rating", label:`${state.rating}★ & above` });
    if (state.availability) labels.push({ key:"availability", label:"In stock" });
    if (state.dietary) labels.push({ key:"dietary", label:state.dietary.replace(/-/g," ") });
    if (state.pack) labels.push({ key:"pack", label:`${state.pack} pack` });
    return labels;
  }

  function updatePremiumUI() {
    const count = activeCount(), labels = filterLabels();
    $$("[data-premium-filter-count]").forEach(node => node.textContent = `${count} active`);
    $$(".catalog-filter-head [data-clear-filters]").forEach(button => button.disabled = count === 0);
    $$("[data-filter-summary]").forEach(summary => {
      summary.hidden = !labels.length;
      summary.innerHTML = labels.slice(0,4).map(item => `<button type="button" data-remove-filter="${item.key}" data-value="${item.value || ""}">${item.label}<i class="fa-solid fa-xmark"></i></button>`).join("") + (labels.length > 4 ? `<span>+${labels.length - 4} more</span>` : "");
    });
    $$("[data-mobile-filters]").forEach(button => {
      button.innerHTML = `<i class="fa-solid fa-sliders"></i> Filters${count ? ` <span class="catalog-mobile-count">${count}</span>` : ""}`;
    });
    $$(".catalog-filter-option").forEach(option => option.classList.toggle("is-selected", Boolean($("input", option)?.checked)));
    $$("[data-view]").forEach(button => {
      const active = button.dataset.view === state.view;
      button.classList.remove("active");
      button.classList.toggle("is-active", active);
      button.setAttribute("aria-pressed", String(active));
    });
    $$("[data-custom-sort]").forEach(menu => {
      const selected = $(`[data-sort-value="${state.sort}"]`, menu);
      $("[data-sort-label]", menu).textContent = selected?.querySelector("span")?.textContent.trim() || "Relevance";
      $$("[data-sort-value]", menu).forEach(option => option.setAttribute("aria-selected", String(option.dataset.sortValue === state.sort)));
    });
    const load = $("#catalog-load");
    if (load && !$(".catalog-progress", load)) {
      const match = load.textContent.match(/\((\d+) remaining\)/i);
      const count = Number(($("#catalog-count")?.textContent.match(/\d+/) || [0])[0]);
      const remaining = match ? Number(match[1]) : 0;
      const viewed = Math.max(0, count - remaining);
      const percent = count ? Math.round(viewed / count * 100) : 0;
      load.insertAdjacentHTML("afterbegin", `<div class="catalog-progress-copy"><span>${viewed} of ${count} products viewed</span><b>${percent}%</b></div><div class="catalog-progress"><span style="width:${percent}%"></span></div>`);
      const button = $("[data-load-more]", load);
      if (button) button.innerHTML = `Load ${Math.min(20, remaining)} more products <small>${remaining} remaining</small>`;
    }
    const apply = $("[data-sheet-apply]");
    if (apply) apply.textContent = `Show ${($("#catalog-count")?.textContent.match(/\d+/) || [0])[0]} products`;
    const sheet = $("#catalog-filter-sheet"), title = $(".catalog-sheet-title");
    if (sheet?.dataset.sheet === "filter" && title) title.innerHTML = `Filters <span class="catalog-mobile-count">${count}</span>`;
    $$("[data-price-summary]").forEach(node => node.textContent = `₹${state.minPrice.toLocaleString("en-IN")} - ₹${state.maxPrice.toLocaleString("en-IN")}`);
    $$("[data-range-min]").forEach(node => node.value = state.minPrice);
    $$("[data-range-max]").forEach(node => node.value = state.maxPrice);
  }

  function init() {
    enhanceHero();
    enhanceSort();
    enhanceFilters($("#catalog-filters"));
    enhanceFilters($("#catalog-sheet-filters"));
    const saved = localStorage.getItem("fb-catalog-view");
    if (saved === "list" && state.view !== "list") {
      state.view = "list";
      window.FreshBasketListing.render();
    }
    updatePremiumUI();
    const observer = new MutationObserver(updatePremiumUI);
    ["#catalog-grid", "#catalog-load", "#catalog-active"].forEach(selector => {
      const node = $(selector);
      if (node) observer.observe(node, { childList: true });
    });
  }

  document.addEventListener("click", event => {
    const sortTrigger = event.target.closest(".catalog-sort-trigger");
    if (sortTrigger) {
      const menu = sortTrigger.closest("[data-custom-sort]");
      const open = !menu.classList.contains("is-open");
      $$("[data-custom-sort].is-open").forEach(node => node.classList.remove("is-open"));
      menu.classList.toggle("is-open", open);
      sortTrigger.setAttribute("aria-expanded", String(open));
      if (open) $("[data-sort-value][aria-selected='true']", menu)?.focus();
      return;
    }
    const sortOption = event.target.closest("[data-sort-value]");
    if (sortOption) {
      const select = $("#catalog-sort");
      select.value = sortOption.dataset.sortValue;
      select.dispatchEvent(new Event("change", { bubbles:true }));
      const menu = sortOption.closest("[data-custom-sort]");
      menu.classList.remove("is-open");
      $(".catalog-sort-trigger", menu).setAttribute("aria-expanded", "false");
      $(".catalog-sort-trigger", menu).focus();
      return;
    }
    if (!event.target.closest("[data-custom-sort]")) {
      $$("[data-custom-sort].is-open").forEach(menu => {
        menu.classList.remove("is-open");
        $(".catalog-sort-trigger", menu)?.setAttribute("aria-expanded", "false");
      });
    }
    const toggle = event.target.closest(".catalog-filter-toggle");
    if (toggle) {
      const group = toggle.closest(".catalog-filter-group");
      const open = !group.classList.contains("is-open");
      group.classList.toggle("is-open", open);
      toggle.setAttribute("aria-expanded", String(open));
      const title = $(".catalog-filter-toggle span", group)?.textContent.trim().toLowerCase();
      if (title) sessionStorage.setItem(`fb-filter-group-${title}`, open ? "open" : "closed");
    }
    const category = event.target.closest("[data-premium-category]");
    if (category) {
      state.categories = category.dataset.premiumCategory ? [category.dataset.premiumCategory] : [];
      state.limit = 20;
      $$("[data-premium-category]").forEach(node => node.classList.toggle("is-active", node === category));
      window.FreshBasketListing.render();
    }
    const view = event.target.closest("[data-view]");
    if (view) localStorage.setItem("fb-catalog-view", view.dataset.view);
    const more = event.target.closest("[data-filter-more]");
    if (more) {
      const group = more.closest(".catalog-filter-group"), expanded = more.getAttribute("aria-expanded") === "true";
      $$("[data-limit-hidden]", group).forEach(option => option.hidden = expanded);
      more.setAttribute("aria-expanded", String(!expanded));
      more.innerHTML = expanded ? `Show ${$$("[data-limit-hidden]", group).length} more <i class="fa-solid fa-chevron-down"></i>` : `Show less <i class="fa-solid fa-chevron-up"></i>`;
    }
    const clearSearch = event.target.closest("[data-filter-search-clear]");
    if (clearSearch) {
      const input = clearSearch.previousElementSibling;
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles:true }));
      input.focus();
    }
  });

  document.addEventListener("input", event => {
    if (event.target.matches("[data-range-min],[data-range-max]")) {
      const context = event.target.closest("#catalog-filters,#catalog-sheet-filters");
      const minRange = $("[data-range-min]", context), maxRange = $("[data-range-max]", context);
      let min = Number(minRange.value), max = Number(maxRange.value);
      if (min > max - 50) { if (event.target === minRange) min = max - 50; else max = min + 50; }
      minRange.value = Math.max(0, min); maxRange.value = Math.min(5000, max);
      $('[data-price="min"]', context).value = minRange.value;
      $('[data-price="max"]', context).value = maxRange.value;
      event.target.dispatchEvent(new Event("change", { bubbles:true }));
      return;
    }
    if (!event.target.matches("[data-premium-filter-search]")) return;
    const term = event.target.value.trim().toLowerCase();
    const context = event.target.closest("#catalog-filters,#catalog-sheet-filters");
    const clear = $("[data-filter-search-clear]", context);
    clear.hidden = !term;
    let matches = 0;
    $$(".catalog-filter-option", context).forEach(option => {
      const match = !term || option.textContent.toLowerCase().includes(term);
      const group = option.closest(".catalog-filter-group");
      const expanded = $("[data-filter-more]", group)?.getAttribute("aria-expanded") === "true";
      option.hidden = term ? !match : option.dataset.limitHidden === "true" && !expanded;
      if (term && match) matches++;
    });
    if (term) $$(".catalog-filter-group", context).forEach(group => {
      const hasMatch = $$(".catalog-filter-option", group).some(option => !option.hidden);
      group.classList.toggle("is-open", hasMatch);
      $(".catalog-filter-toggle", group)?.setAttribute("aria-expanded", String(hasMatch));
    });
    $$("[data-filter-more]", context).forEach(button => button.hidden = Boolean(term));
    $("[data-filter-empty]", context).hidden = !term || matches > 0;
  });

  document.addEventListener("keydown", event => {
    const sortMenu = event.target.closest("[data-custom-sort]");
    if (sortMenu) {
      const options = $$("[data-sort-value]", sortMenu), current = options.indexOf(document.activeElement);
      if (event.key === "Escape") {
        sortMenu.classList.remove("is-open");
        $(".catalog-sort-trigger", sortMenu).setAttribute("aria-expanded", "false");
        $(".catalog-sort-trigger", sortMenu).focus();
        return;
      }
      if (event.key === "ArrowDown" || event.key === "ArrowUp") {
        event.preventDefault();
        const direction = event.key === "ArrowDown" ? 1 : -1;
        const next = current < 0 ? (direction > 0 ? 0 : options.length - 1) : (current + direction + options.length) % options.length;
        options[next]?.focus();
        return;
      }
      if ((event.key === "Enter" || event.key === " ") && document.activeElement?.matches("[data-sort-value]")) {
        event.preventDefault();
        document.activeElement.click();
        return;
      }
    }
    const sheet = $("#catalog-filter-sheet");
    if (event.key !== "Tab" || !sheet?.classList.contains("is-open")) return;
    const focusable = $$('button:not([disabled]),input:not([disabled]),select:not([disabled]),a[href]', sheet).filter(node => node.offsetParent !== null);
    if (!focusable.length) return;
    const first = focusable[0], last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });

  init();
})(window, document);
