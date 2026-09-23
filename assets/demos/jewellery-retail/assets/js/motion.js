(()=>{
  "use strict";
  if(window.__zivaraMotion)return;
  window.__zivaraMotion=true;
  const reduce=matchMedia("(prefers-reduced-motion: reduce)").matches;
  const $=(s,c=document)=>c.querySelector(s);
  const $$=(s,c=document)=>[...c.querySelectorAll(s)];
  const progress=document.createElement("div");
  progress.className="motion-progress";
  progress.setAttribute("aria-hidden","true");
  document.body.prepend(progress);
  const backTop=document.createElement("button");
  backTop.className="motion-back-top";
  backTop.type="button";
  backTop.setAttribute("aria-label","Back to top");
  backTop.textContent="↑";
  document.body.append(backTop);
  backTop.addEventListener("click",()=>scrollTo({top:0,behavior:reduce?"auto":"smooth"}));
  const syncScroll=()=>backTop.classList.toggle("visible",scrollY>650);
  addEventListener("scroll",syncScroll,{passive:true});
  syncScroll();

  document.body.classList.add("motion-page-enter");
  const finishPageEnter=e=>{if(e&&e.target!==document.body)return;document.body.classList.remove("motion-page-enter");document.body.removeEventListener("animationend",finishPageEnter)};
  document.body.addEventListener("animationend",finishPageEnter);
  setTimeout(finishPageEnter,600);
  addEventListener("pageshow",()=>{document.body.classList.remove("motion-page-leaving");progress.classList.remove("active")});

  const isInternal=a=>{
    if(!a.href||a.target==="_blank"||a.hasAttribute("download"))return false;
    const url=new URL(a.href,location.href);
    return url.origin===location.origin&&url.pathname!==location.pathname&&!a.href.startsWith("javascript:");
  };
  document.addEventListener("click",e=>{
    const a=e.target.closest("a");
    if(!a||!isInternal(a)||e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;
    e.preventDefault();
    progress.classList.add("active");
    if(!reduce)document.body.classList.add("motion-page-leaving");
    setTimeout(()=>location.href=a.href,reduce?0:180);
  });

  const revealSelector=[
    ".page-hero > *",".crumbs",".shop-toolbar",".filters",".store-card",
    ".product-detail > section",".pdp-gallery",".pdp-buy-panel",".pdp-tabs",
    ".pdp-story-panel",".pdp-story-media",".pdp-complete-look > *",".pdp-reviews header",".pdp-review-grid article",
    ".cart-item",".cart-progress",".luxury-cart-summary",".cart-promise > *",
    ".cart-recommendations > *",".cart-testimonial > .store-shell",".summary-box",".checkout-card",
    ".account-card",".account-nav",".auth-card",".content-page > *",".faq details",
    ".empty-state",".success > *",".timeline",".footer-cols > div",".bridal-copy",".bridal-consult",
    ".section-title",".testimonial-grid article",".why-grid article",".combo-grid article",
    ".editorial-head",".editorial-card",".editorial-footer",
    ".budget-head","#underGrid .product-card",".budget-footer",".combo-head",".combo-footer",
    ".promise-head",".promise-proof",".stories-head",".stories-proof",
    ".insta-head",".insta-card",".insta-footer",".consult-copy",".consult-card",
    ".newsletter-copy",".newsletter-card",".editorial-footer-main > *",".footer-account-utility",".editorial-trust",".editorial-footer-bottom",
    ".luxury-footer-hero > *",".luxury-footer-nav",".luxury-footer-services article",
    ".luxury-footer-social-stage > *",".luxury-footer-quote",".luxury-footer-bottom",
    ".checkout-progress",".checkout-hero > .store-shell > *",".checkout-section",
    ".checkout-summary",".checkout-security",".checkout-help",
    ".confirmation-hero > *",".confirmation-timeline > *",".confirmation-order-card",
    ".confirmation-side > section",".confirmation-promises > div",".confirmation-recommend > *",
    ".confirmation-thankyou > *",
    ".tracking-hero > .store-shell > *",".tracking-overview > *",".tracking-journey > *",
    ".tracking-milestones article",".tracking-card",".tracking-side > section",
    ".tracking-benefits > div",".tracking-recommend > *",".tracking-quote > *",
    ".account-hero > .store-shell > *",".account-lounge-nav",".account-section-head",
    ".account-stats > a",".account-quick-actions",".account-recent",".account-recent article",
    ".account-recommend",".account-recommend article",".account-dashboard-side > section",
    ".lounge-page-card",".lounge-page-help",".lounge-security-card",".lounge-wishlist article"
  ].join(",");
  const surfaceSelector=[
    ".store-card",".product-card",".checkout-section",".checkout-summary",
    ".confirmation-order-card",".confirmation-side > section",".tracking-card",
    ".tracking-side > section",".account-stats > a",".account-quick-actions a",
    ".account-recent article",".account-dashboard-side > section",".lounge-page-card",
    ".lounge-wishlist article",".testimonial-grid article",".why-grid article",".combo-grid article"
  ].join(",");
  const mediaSelector=[
    ".store-card-media",".product-card-media",".account-recommend article",
    ".cart-item-media",".confirmation-recommend article",".tracking-recommend article",
    ".editorial-card",".insta-card",".combo-grid article"
  ].join(",");
  const actionSelector=[
    ".button",".btn","button:not(.motion-back-top):not(.zivara-alert-close):not(.zivara-alert-confirm)",".store-wa",".wa-head",
    ".card-actions a",".card-actions button",".account-quick-actions a",
    ".confirmation-actions a",".tracking-actions a"
  ].join(",");
  const whatsappSvg=`<svg class="whatsapp-icon" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><circle cx="16" cy="16" r="16"/><path d="M16.05 7.2a8.63 8.63 0 0 0-7.38 13.1L7.5 24.6l4.4-1.15a8.63 8.63 0 1 0 4.15-16.25Zm0 15.8a7.1 7.1 0 0 1-3.62-.99l-.26-.15-2.61.68.7-2.54-.17-.27A7.17 7.17 0 1 1 16.05 23Z"/><path d="M19.99 17.62c-.22-.11-1.29-.64-1.49-.71-.2-.08-.34-.11-.49.11-.14.22-.56.71-.69.85-.13.15-.25.17-.47.06-.22-.11-.92-.34-1.75-1.08a6.58 6.58 0 0 1-1.21-1.5c-.13-.22-.01-.34.1-.45.1-.1.22-.25.33-.38.11-.13.14-.22.22-.36.07-.15.03-.28-.02-.39-.06-.11-.49-1.17-.67-1.61-.17-.42-.35-.36-.49-.37h-.41c-.15 0-.38.06-.58.28-.2.22-.76.75-.76 1.82s.78 2.11.89 2.25c.11.15 1.53 2.34 3.72 3.29.52.22.92.36 1.24.46.52.17.99.14 1.37.09.42-.06 1.29-.53 1.47-1.04.18-.51.18-.95.13-1.04-.05-.1-.2-.15-.42-.26Z"/></svg>`;
  const normalizeWhatsApp=(root=document)=>{
    const scope=root===document?document:root;
    const links=[...(scope.matches?.('a[href*="wa.me"]')?[scope]:[]),...$$('a[href*="wa.me"]',scope)];
    links.forEach(link=>{
      const old=link.querySelector("svg");
      const shouldShow=old||link.matches(".button,.store-wa,.wa-head,.floating-wa,.quick-wa,.card-actions a,.store-actions a");
      if(shouldShow&&!link.querySelector(".whatsapp-icon")){
        if(old)old.outerHTML=whatsappSvg;else link.insertAdjacentHTML("afterbegin",whatsappSvg);
      }
      if(!link.getAttribute("aria-label")){
        const label=link.textContent.trim();
        link.setAttribute("aria-label",label?`${label} on WhatsApp`:"Chat on WhatsApp");
      }
      if(link.classList.contains("floating-wa")){
        link.title="Chat on WhatsApp";
        link.setAttribute("aria-label","Chat on WhatsApp");
      }
    });
    const spriteIcons=[...(scope.matches?.('svg:has(use[href="#whatsapp"])')?[scope]:[]),...$$('svg:has(use[href="#whatsapp"])',scope)];
    spriteIcons.forEach(svg=>{if(!svg.classList.contains("whatsapp-icon"))svg.outerHTML=whatsappSvg});
  };
  let observer;
  if(!reduce&&"IntersectionObserver" in window){
    observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(entry.isIntersecting){entry.target.classList.add("motion-in");observer.unobserve(entry.target)}
    }),{threshold:.14,rootMargin:"0px 0px -4% 0px"});
  }
  const prepare=(root=document)=>{
    $$(revealSelector,root).forEach((el,index)=>{
      if(el.classList.contains("motion-reveal")||el.closest(".category-grid,.trust-grid"))return;
      el.classList.add("motion-reveal");
      el.style.setProperty("--motion-delay",`${Math.min(index%6,5)*70}ms`);
      if(observer)observer.observe(el);else el.classList.add("motion-in");
    });
    $$("img",root).forEach(img=>{
      const ready=()=>img.classList.add("motion-img-loaded");
      if(img.complete)ready();else img.addEventListener("load",ready,{once:true});
    });
    $$(surfaceSelector,root).forEach(el=>el.classList.add("motion-surface"));
    $$(mediaSelector,root).forEach(el=>el.classList.add("motion-media"));
    $$(actionSelector,root).forEach(el=>el.classList.add("motion-action"));
    normalizeWhatsApp(root);
  };
  prepare();
  const mutation=new MutationObserver(records=>records.forEach(record=>record.addedNodes.forEach(node=>{
    if(node.nodeType===1)prepare(node);
  })));
  mutation.observe(document.body,{childList:true,subtree:true});

  if(!reduce&&matchMedia("(hover: hover) and (pointer: fine)").matches){
    document.addEventListener("pointermove",e=>{
      const surface=e.target.closest(".motion-surface");
      if(!surface)return;
      const rect=surface.getBoundingClientRect();
      surface.style.setProperty("--motion-x",`${e.clientX-rect.left}px`);
      surface.style.setProperty("--motion-y",`${e.clientY-rect.top}px`);
    },{passive:true});
  }

  document.addEventListener("pointerdown",e=>{
    const action=e.target.closest(".motion-action");
    if(!action||action.disabled)return;
    const rect=action.getBoundingClientRect();
    const ripple=document.createElement("span");
    const size=Math.max(rect.width,rect.height)*1.35;
    ripple.className="motion-ripple";
    ripple.style.width=ripple.style.height=`${size}px`;
    ripple.style.left=`${e.clientX-rect.left-size/2}px`;
    ripple.style.top=`${e.clientY-rect.top-size/2}px`;
    action.append(ripple);
    ripple.addEventListener("animationend",()=>ripple.remove(),{once:true});
  });

  const path=location.pathname.split("/").pop()||"index.html";
  const currentParams=new URLSearchParams(location.search);
  const navItemLinks=$$([
    ".nav-row > a",
    ".nav-row > .has-mega > a",
    ".mobile-store-menu nav > a",
    ".account-lounge-nav > a",
    ".account-mobile-nav > a"
  ].join(","));
  const scored=navItemLinks.map(a=>{
    const raw=a.getAttribute("href");
    if(!raw||raw.startsWith("#")||raw.startsWith("javascript:"))return{a,score:-1};
    const url=new URL(raw,location.href);
    const hrefPath=url.pathname.split("/").pop()||"index.html";
    if(hrefPath!==path)return{a,score:-1};
    const linkParams=[...url.searchParams];
    if(linkParams.some(([key,value])=>currentParams.get(key)!==value))return{a,score:-1};
    return{a,score:linkParams.length};
  }).filter(item=>item.score>=0);
  const best=scored.reduce((max,item)=>Math.max(max,item.score),-1);
  scored.forEach(({a,score})=>{
    if(score===best)a.classList.add("motion-active-link");
  });

  document.addEventListener("visibilitychange",()=>{
    document.documentElement.classList.toggle("motion-tab-hidden",document.hidden);
  });
})();
