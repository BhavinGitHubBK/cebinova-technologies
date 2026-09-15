(function(window,document){
"use strict";
if(!document.querySelector('link[href="assets/css/premium-home.css"]')){const premiumStyle=document.createElement("link");premiumStyle.rel="stylesheet";premiumStyle.href="assets/css/premium-home.css";document.head.appendChild(premiumStyle)}
if(!document.querySelector('link[href="assets/css/brand-showcase.css"]')){const brandStyle=document.createElement("link");brandStyle.rel="stylesheet";brandStyle.href="assets/css/brand-showcase.css";document.head.appendChild(brandStyle)}
if(!document.querySelector('link[href="assets/css/app-showcase.css"]')){const appStyle=document.createElement("link");appStyle.rel="stylesheet";appStyle.href="assets/css/app-showcase.css";document.head.appendChild(appStyle)}
if(!document.querySelector('link[href="assets/css/review-showcase.css"]')){const reviewStyle=document.createElement("link");reviewStyle.rel="stylesheet";reviewStyle.href="assets/css/review-showcase.css";document.head.appendChild(reviewStyle)}
if(!document.querySelector('link[href="assets/css/club-showcase.css"]')){const clubStyle=document.createElement("link");clubStyle.rel="stylesheet";clubStyle.href="assets/css/club-showcase.css";document.head.appendChild(clubStyle)}
if(!document.querySelector('link[href="assets/css/deal-showcase.css"]')){const dealStyle=document.createElement("link");dealStyle.rel="stylesheet";dealStyle.href="assets/css/deal-showcase.css";document.head.appendChild(dealStyle)}
const $=(selector,context=document)=>context.querySelector(selector);
const $$=(selector,context=document)=>[...context.querySelectorAll(selector)];
const store=window.FBStorage,ui=window.FBUI,money=ui.money;
const used=new Set();
let slideIndex=0,countdownTimer;
const product=id=>window.getProductById?window.getProductById(id):window.PRODUCTS.find(item=>item.id===Number(id));
const cart=()=>store.get("fb-cart",[]);
const wishlist=()=>store.get("fb-wish",[]).map(Number);
const homeCount=()=>5;
function take(source,count=6){
  const selected=[];
  for(const item of source){if(item&&item.stock>0&&!used.has(item.id)){selected.push(item);used.add(item.id);if(selected.length===count)break;}}
  return selected;
}
function byCategories(names){return window.PRODUCTS.filter(item=>names.includes(item.category));}
function discountSort(items=[...window.PRODUCTS]){return [...items].sort((a,b)=>b.discount-a.discount||b.rating-a.rating)}
function card(item){
  const entry=cart().find(row=>Number(row.id)===item.id),wished=wishlist().includes(item.id);
  return `<article class="home-product-card" data-home-product="${item.id}"><a class="home-product-image" href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" loading="lazy" onerror="imageFallback(this)"></a>
    <span class="home-discount">${item.discount}% OFF</span><button class="home-wish ${wished?"is-active":""}" type="button" data-home-wish="${item.id}" aria-label="${wished?"Remove":"Add"} ${item.name} ${wished?"from":"to"} wishlist"><i class="${wished?"fa-solid":"fa-regular"} fa-heart"></i></button>
    <div class="home-product-meta">${item.brand} · ${item.weight}</div><a href="product-details.html?id=${item.id}"><h3 class="home-product-name">${item.name}</h3></a>
    <div class="home-product-rating">★ ${item.rating} <span>(${item.reviewCount})</span></div><div class="home-price"><strong>${money(item.sellingPrice)}</strong><span class="home-mrp">MRP ${money(item.mrp)}</span></div><div class="home-save">You Save ${money(item.savings)}</div><div class="home-delivery"><i class="fa-solid fa-bolt"></i> ${item.deliveryTime}</div>
    <div class="home-card-actions">${entry?`<div class="home-qty home-qty-wrap"><button type="button" data-home-qty="${item.id}" data-delta="-1" aria-label="Decrease quantity">−</button><b>${entry.qty}</b><button type="button" data-home-qty="${item.id}" data-delta="1" aria-label="Increase quantity">+</button></div>`:`<button class="btn btn-primary" type="button" data-home-add="${item.id}"><i class="fa-solid fa-basket-shopping"></i> Add</button><button class="home-quick" type="button" data-home-quick="${item.id}" aria-label="Quick view ${item.name}"><i class="fa-regular fa-eye"></i></button>`}</div>
  </article>`;
}
function section(id,items){const host=$(id);if(host)host.innerHTML=items.length?items.map(card).join(""):'<div class="home-empty-products">More products are arriving soon.</div>';}
function sectionLink(category){return `category.html?category=${encodeURIComponent(category)}`}
function renderCategories(){
  const host=$("#home-categories");if(!host)return;
  const featured=(window.CATEGORY_METADATA||[]).filter(category=>category.featured).slice(0,10);
  host.innerHTML=featured.map((category,index)=>`<a class="home-category home-category--accent-${index%6} ${index===0?"is-featured":""}" href="${sectionLink(category.name)}" aria-label="Browse ${category.name}, ${category.productCount} products"><span class="home-category-accent"></span>${index===0?'<em class="home-category-featured">Most popular</em>':""}<span class="home-category-image"><img src="${category.image}" width="220" height="180" loading="lazy" alt="${category.imageAlt||category.name}" onerror="imageFallback(this)"></span><span class="home-category-copy"><b>${category.name}</b><small>Explore quality essentials</small></span><span class="home-category-count">${category.productCount} Products</span><span class="home-category-action">Explore <i class="fa-solid fa-arrow-right"></i></span></a>`).join("");
}
function renderBrands(){
  const host=$("#home-brands"),brands=window.FB_BRANDS||[];if(!host)return;
  host.innerHTML=brands.map(brand=>`<a class="home-brand" style="--brand-accent:${brand.accent}" href="${brand.link}" aria-label="Explore ${brand.name}, ${brand.category}, ${brand.productCount} products">${brand.featured?'<span class="home-brand-badge">House brand</span>':""}<span class="home-brand-logo">${brand.logoMarkup}</span><strong class="home-brand-wordmark">${brand.name}</strong><small class="home-brand-category">${brand.category}</small><span class="home-brand-meta">${brand.productCount} Products <i class="fa-solid fa-arrow-right"></i></span></a>`).join("");
}
function initBrandCarousel(){
  const track=$("#home-brands"),prev=$("[data-brand-prev]"),next=$("[data-brand-next]");if(!track||!prev||!next)return;
  const reduced=matchMedia("(prefers-reduced-motion: reduce)").matches;
  let timer=0,paused=false;
  const distance=()=>track.querySelector(".home-brand")?.getBoundingClientRect().width+16||240;
  const move=direction=>{const atEnd=track.scrollLeft+track.clientWidth>=track.scrollWidth-8,atStart=track.scrollLeft<=8;if(direction>0&&atEnd)track.scrollTo({left:0,behavior:reduced?"auto":"smooth"});else if(direction<0&&atStart)track.scrollTo({left:track.scrollWidth,behavior:reduced?"auto":"smooth"});else track.scrollBy({left:direction*distance(),behavior:reduced?"auto":"smooth"});restart()};
  const stop=()=>{clearInterval(timer);timer=0};
  const start=()=>{stop();if(!reduced&&!paused&&!document.hidden)timer=setInterval(()=>move(1),4000)};
  const restart=()=>{stop();start()};
  prev.addEventListener("click",()=>move(-1));next.addEventListener("click",()=>move(1));
  track.addEventListener("keydown",event=>{if(event.key==="ArrowLeft"){event.preventDefault();move(-1)}if(event.key==="ArrowRight"){event.preventDefault();move(1)}});
  track.closest(".home-brand-carousel").addEventListener("mouseenter",()=>{paused=true;stop()});
  track.closest(".home-brand-carousel").addEventListener("mouseleave",()=>{paused=false;start()});
  track.addEventListener("focusin",()=>{paused=true;stop()});track.addEventListener("focusout",event=>{if(!track.contains(event.relatedTarget)){paused=false;start()}});
  track.addEventListener("pointerdown",stop,{passive:true});track.addEventListener("pointerup",start,{passive:true});
  document.addEventListener("visibilitychange",()=>document.hidden?stop():start());start();
}
function initAppShowcase(){
  const section=$(".home-app-premium"),screens=$$("[data-app-screen]",section),dots=$$("[data-app-dot]",section);if(!section||!screens.length)return;
  const reduced=matchMedia("(prefers-reduced-motion: reduce)").matches;
  let active=0,timer=0,visible=false;
  const show=index=>{active=(index+screens.length)%screens.length;screens.forEach((screen,i)=>screen.classList.toggle("is-active",i===active));dots.forEach((dot,i)=>{dot.classList.toggle("is-active",i===active);dot.setAttribute("aria-current",i===active?"true":"false")})};
  const stop=()=>{clearInterval(timer);timer=0};
  const start=()=>{stop();if(visible&&!reduced&&!document.hidden)timer=setInterval(()=>show(active+1),4200)};
  dots.forEach((dot,index)=>dot.addEventListener("click",()=>{show(index);start()}));
  section.addEventListener("mouseenter",stop);section.addEventListener("mouseleave",start);
  section.addEventListener("focusin",stop);section.addEventListener("focusout",event=>{if(!section.contains(event.relatedTarget))start()});
  document.addEventListener("visibilitychange",()=>document.hidden?stop():start());
  if("IntersectionObserver" in window){const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{visible=entry.isIntersecting;if(visible){section.classList.add("is-app-visible");start()}else stop()}),{threshold:.2});observer.observe(section)}else{visible=true;start()}
  show(0);
}
function renderDeal(){
  const item=window.PRODUCTS.find(product=>product.dealOfTheDay&&!used.has(product.id))||discountSort().find(product=>!used.has(product.id));if(!item)return;used.add(item.id);
  const stockPercent=Math.max(12,Math.min(100,Math.round(item.stock/80*100)));
  $("#home-deal").innerHTML=`<div class="deal-product-stage"><div class="deal-glass"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"></div><span class="deal-floating-badge"><i class="fa-solid fa-fire"></i> ${item.discount}% OFF</span><span class="deal-limited"><i class="fa-solid fa-bolt"></i> Limited time</span></div><div class="deal-content"><span class="deal-kicker">Deal of the day</span><h2>${item.name}</h2><p class="deal-description">${item.description}</p><div class="deal-product-meta"><span><i class="fa-solid fa-tag"></i> ${item.brand}</span><span><i class="fa-solid fa-weight-hanging"></i> ${item.weight}</span><span class="deal-rating"><i class="fa-solid fa-star"></i> ${item.rating} (${item.reviewCount})</span><span><i class="fa-solid fa-circle-check"></i> ${item.stockStatus}</span></div><div class="deal-price-row"><strong class="deal-price">${money(item.sellingPrice)}</strong><del class="deal-mrp">MRP ${money(item.mrp)}</del><span class="deal-save">Save ${money(item.savings)}</span><span class="deal-discount">${item.discount}% OFF</span></div><div class="deal-timer-label"><span><i class="fa-regular fa-clock"></i> Offer ends in</span><span>Today only</span></div><div class="deal-countdown" aria-label="Deal countdown"><span class="deal-time"><b id="deal-hours">05</b><small>HOURS</small></span><i class="deal-colon">:</i><span class="deal-time"><b id="deal-minutes">59</b><small>MINUTES</small></span><i class="deal-colon">:</i><span class="deal-time"><b id="deal-seconds">59</b><small>SECONDS</small></span></div><div class="deal-progress" aria-hidden="true"><span id="deal-progress"></span></div><div class="deal-trust"><span><i class="fa-solid fa-box"></i> Freshly Packed</span><span><i class="fa-solid fa-truck-fast"></i> Same-Day Delivery</span><span><i class="fa-solid fa-shield-halved"></i> Quality Checked</span><span><i class="fa-solid fa-rotate-left"></i> Easy Returns</span></div><div class="deal-stock"><span><b>Only ${item.stock} left</b><small>Selling fast</small></span><div class="deal-stock-bar"><i style="width:${stockPercent}%"></i></div></div><div class="deal-actions"><button class="deal-primary" type="button" data-home-add="${item.id}"><i class="fa-solid fa-basket-shopping"></i> Add deal to basket <i class="fa-solid fa-arrow-right"></i></button><a class="deal-secondary" href="product-details.html?id=${item.id}">View Details</a></div></div>`;
  let remaining=21600;clearInterval(countdownTimer);countdownTimer=setInterval(()=>{remaining=remaining>0?remaining-1:21600;const h=Math.floor(remaining/3600),m=Math.floor(remaining%3600/60),s=remaining%60;$("#deal-hours")&&($("#deal-hours").textContent=String(h).padStart(2,"0"));$("#deal-minutes")&&($("#deal-minutes").textContent=String(m).padStart(2,"0"));$("#deal-seconds")&&($("#deal-seconds").textContent=String(s).padStart(2,"0"));const progress=$("#deal-progress");if(progress)progress.style.width=`${remaining/21600*100}%`;},1000);
}
function renderProducts(){
  used.clear();
  const count=homeCount();
  section("#home-offers",take(discountSort(),count));
  section("#home-essentials",take(discountSort(byCategories(["Rice, Atta and Grains","Pulses and Dal","Cooking Oil and Ghee","Salt, Sugar and Jaggery","Spices and Masala"])),count));
  section("#home-fruit",take(byCategories(["Fruits and Vegetables"]),count));
  section("#home-dairy",take(byCategories(["Dairy and Bakery"]),count));
  section("#home-snacks",take(byCategories(["Biscuits and Cookies","Snacks and Namkeen","Instant Food","Noodles and Pasta"]),count));
  section("#home-beverages",take(byCategories(["Tea and Coffee","Beverages and Juices","Soft Drinks"]),count));
  section("#home-cleaning",take(byCategories(["Household Cleaning","Laundry Care","Kitchen Cleaning"]),count));
  section("#home-personal",take(byCategories(["Personal Care","Hair Care","Skin Care","Oral Care"]),count));
  section("#home-baby",take(byCategories(["Baby Care"]),count));
  section("#home-home-kitchen",take(byCategories(["Home and Kitchen","Small Appliances","Pooja Essentials"]),count));
  renderDeal();
  const recentIds=store.get("fb-recent",[]).map(Number),recent=take(recentIds.map(product).filter(Boolean),count);
  if(recent.length<count)recent.push(...take(window.PRODUCTS.filter(item=>item.bestseller),count-recent.length));
  section("#home-recent",recent);
  const interests=[...new Set(recent.flatMap(item=>[item.category,...item.tags]))];
  const recommendedPool=window.PRODUCTS.filter(item=>interests.some(term=>`${item.category} ${item.tags.join(" ")}`.includes(term))).sort((a,b)=>b.rating-a.rating);
  const recommended=take(recommendedPool,count);if(recommended.length<count)recommended.push(...take(window.PRODUCTS.filter(item=>item.featured),count-recommended.length));
  section("#home-recommended",recommended);
}
function renderHero(){
  const trust='<div class="home-hero-trust"><span><i class="fa-solid fa-circle-check"></i> Fresh quality</span><span><i class="fa-solid fa-truck-fast"></i> Fast delivery</span><span><i class="fa-solid fa-shield-halved"></i> Secure checkout</span></div>';
  const slides=[
    {eyebrow:"Fresh value every day",title:'Fresh groceries,<br><span>thoughtfully delivered.</span>',description:"Shop produce, pantry staples and household essentials without the supermarket queue.",primary:["Shop groceries","shop.html","fa-basket-shopping"],secondary:["View offers","offers.html","fa-tags"],image:"assets/images/banners/freshbasket-hero.png",alt:"Fresh groceries with vegetables, fruit, bread and milk"},
    {eyebrow:"Monthly grocery savings",title:'Stock up more.<br><span>Spend a little less.</span>',description:"Save on rice, flour, pulses, cooking oils, spices and everyday pantry essentials.",primary:["Shop essentials",sectionLink("Rice, Atta and Grains"),"fa-basket-shopping"],secondary:["Explore deals","offers.html","fa-tags"],image:"assets/images/banners/hero-pantry-v2.png",alt:"Rice, flour, pulses, oil and spices arranged for a monthly grocery shop"},
    {eyebrow:"Farm-fresh selection",title:'Fresh fruits and vegetables,<br><span>picked for your family.</span>',description:"Discover colourful, quality produce for your everyday meals.",primary:["Shop fresh produce",sectionLink("Fruits and Vegetables"),"fa-leaf"],secondary:["Browse products","shop.html","fa-store"],image:"assets/images/banners/hero-produce-v2.png",alt:"Colourful fresh fruits and vegetables in a market basket"},
    {eyebrow:"Home-care savings",title:'A cleaner home,<br><span>at a better value.</span>',description:"Shop laundry, kitchen cleaning and household essentials at attractive prices.",primary:["Shop home care",sectionLink("Household Cleaning"),"fa-sparkles"],secondary:["View offers","offers.html","fa-tags"],image:"assets/images/banners/hero-home-care-v2.png",alt:"Premium household cleaning products and natural cleaning tools"}
  ];
  $("#home-slides").innerHTML=slides.map((slide,index)=>`<article class="home-slide home-slide--${index+1} ${index===0?"is-active":""}" aria-hidden="${index!==0}"><div class="home-slide-copy"><span class="offer-pill"><i class="fa-solid ${index===2?"fa-leaf":"fa-bolt"}"></i> ${slide.eyebrow}</span><h1>${slide.title}</h1><p>${slide.description}</p><div class="home-slide-actions"><a class="btn btn-primary" href="${slide.primary[1]}"><i class="fa-solid ${slide.primary[2]}"></i>${slide.primary[0]}<i class="fa-solid fa-arrow-right"></i></a><a class="btn btn-light" href="${slide.secondary[1]}"><i class="fa-solid ${slide.secondary[2]}"></i>${slide.secondary[0]}</a></div>${trust}</div><div class="home-slide-visual"><img src="${slide.image}" width="1824" height="864" ${index===0?'fetchpriority="high"':'loading="lazy"'} alt="${slide.alt}" onerror="imageFallback(this)"></div></article>`).join("");
  $(".home-slider-dots").innerHTML=slides.map((_,index)=>`<button class="home-slider-dot ${index===0?"is-active":""}" type="button" data-slide="${index}" aria-label="Show slide ${index+1}"></button>`).join("");
}
function showSlide(index){
  const slides=$$(".home-slide"),dots=$$(".home-slider-dot");slideIndex=(index+slides.length)%slides.length;
  slides.forEach((slide,i)=>{slide.classList.toggle("is-active",i===slideIndex);slide.setAttribute("aria-hidden",String(i!==slideIndex));});dots.forEach((dot,i)=>dot.classList.toggle("is-active",i===slideIndex));
}
function quickView(id){
  const item=product(id);if(!item)return;
  const modal=ui.openModal({title:item.name,content:`<div class="home-quick-modal"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"><div><span class="eyebrow">${item.brand}</span><p>${item.weight} · <span class="rating">★ ${item.rating} (${item.reviewCount})</span></p><div class="home-price"><strong style="font-size:1.45rem">${money(item.sellingPrice)}</strong><span class="home-mrp">MRP ${money(item.mrp)}</span></div><p class="home-save">You Save ${money(item.savings)}</p><p>${item.description}</p><p class="shopping-stock">${item.stockStatus} · ${item.stock} available</p><button class="btn btn-primary btn-block" type="button" data-modal-add="${item.id}">Add to basket</button></div></div>`});
  $("[data-modal-add]",modal).onclick=()=>{window.addCart(item.id);ui.closeModal(modal);window.setTimeout(renderProducts,0);};
}
function handleClick(event){
  const button=event.target.closest("button");if(!button)return;
  if(button.dataset.slide)showSlide(Number(button.dataset.slide));
  if(button.dataset.sliderPrev!==undefined)showSlide(slideIndex-1);
  if(button.dataset.sliderNext!==undefined)showSlide(slideIndex+1);
  if(button.dataset.homeAdd){window.addCart(Number(button.dataset.homeAdd));renderProducts();}
  if(button.dataset.homeQty){const id=Number(button.dataset.homeQty),delta=Number(button.dataset.delta),item=product(id),entry=cart().find(row=>Number(row.id)===id);if(delta>0&&entry&&entry.qty>=item.stock){ui.toast(`Only ${item.stock} available in stock`);return;}window.qty(id,delta);renderProducts();}
  if(button.dataset.homeWish){window.toggleWish(Number(button.dataset.homeWish));renderProducts();}
  if(button.dataset.homeQuick)quickView(Number(button.dataset.homeQuick));
}
let heroTimer,heroPaused=false,heroHover=false,heroFocus=false,heroStarted=0,heroRemaining=6000;
function stopHero(preserve=true){clearTimeout(heroTimer);heroTimer=null;if(preserve&&heroStarted)heroRemaining=Math.max(300,heroRemaining-(performance.now()-heroStarted));$(".home-slider")?.classList.remove("is-playing");}
function startHero(reset=false){
  const slider=$(".home-slider"),bar=$(".home-slider-progress span");if(!slider)return;stopHero(false);if(reset)heroRemaining=6000;
  if(bar&&reset){bar.style.animation="none";void bar.offsetWidth;bar.style.animation="";}
  if(heroPaused||heroHover||heroFocus||document.hidden||window.matchMedia("(prefers-reduced-motion: reduce)").matches)return;
  heroStarted=performance.now();slider.classList.add("is-playing");heroTimer=setTimeout(()=>{showSlide(slideIndex+1);heroRemaining=6000;startHero(true);},heroRemaining);
}
function premiumEnhancements(){
  const slider=$(".home-slider"),controls=$(".home-slider-controls");
  if(slider&&controls&&!$(".home-slider-progress")){
    slider.setAttribute("aria-label","Featured grocery offers");slider.tabIndex=0;
    const pause=document.createElement("button");pause.type="button";pause.className="home-slider-pause";pause.setAttribute("aria-label","Pause carousel");pause.innerHTML='<i class="fa-solid fa-pause"></i>';controls.insertBefore(pause,controls.lastElementChild);
    slider.insertAdjacentHTML("beforeend",'<div class="home-slider-progress" aria-hidden="true"><span></span></div>');
    pause.addEventListener("click",()=>{heroPaused=!heroPaused;pause.innerHTML=heroPaused?'<i class="fa-solid fa-play"></i>':'<i class="fa-solid fa-pause"></i>';pause.setAttribute("aria-label",heroPaused?"Play carousel":"Pause carousel");startHero()});
    slider.addEventListener("mouseenter",()=>{heroHover=true;stopHero()});slider.addEventListener("mouseleave",()=>{heroHover=false;startHero()});
    slider.addEventListener("focusin",()=>{heroFocus=true;stopHero()});slider.addEventListener("focusout",event=>{if(!slider.contains(event.relatedTarget)){heroFocus=false;startHero()}});
    slider.addEventListener("keydown",event=>{if(event.key==="ArrowLeft"){event.preventDefault();showSlide(slideIndex-1);startHero(true)}if(event.key==="ArrowRight"){event.preventDefault();showSlide(slideIndex+1);startHero(true)}});
    let touchX=0;slider.addEventListener("touchstart",event=>{touchX=event.changedTouches[0].clientX;stopHero()},{passive:true});slider.addEventListener("touchend",event=>{const distance=event.changedTouches[0].clientX-touchX;if(Math.abs(distance)>45)showSlide(slideIndex+(distance<0?1:-1));startHero(true)},{passive:true});
    controls.addEventListener("click",()=>startHero(true));document.addEventListener("visibilitychange",()=>document.hidden?stopHero():startHero());startHero(true);
  }
  const descriptions={"Top offers":"Big savings on everyday favourites, updated regularly.","Grocery essentials":"Stock your pantry with trusted daily staples.","Fruits and vegetables":"Fresh, quality-checked produce for every meal.","Dairy and bakery":"Breakfast basics and everyday fresh favourites.","Snacks and packaged food":"Tea-time treats and quick bites for every mood.","Beverages":"Refreshing drinks, tea and coffee for every moment.","Household cleaning":"Dependable cleaning essentials for a fresher home.","Personal care":"Everyday self-care, thoughtfully selected.","Baby care":"Gentle essentials made for little ones.","Home and kitchen":"Useful supplies that make daily routines easier.","Recently viewed products":"Pick up where you left off.","Recommended products":"Suggestions selected from your browsing interests."};
  $$(".section-head").forEach(head=>{const title=$("h2",head);if(title&&!$("p",head)&&descriptions[title.textContent.trim()])title.insertAdjacentHTML("afterend",`<p>${descriptions[title.textContent.trim()]}</p>`)});
  const reveals=$$("main .section").slice(1);if("IntersectionObserver" in window){const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add("is-visible");observer.unobserve(entry.target)}}),{threshold:.06});reveals.forEach(section=>{section.classList.add("home-reveal");observer.observe(section)})}else reveals.forEach(section=>section.classList.add("is-visible"));
  const whyHead=$(".home-why-head"),benefits=$$(".home-benefit");if("IntersectionObserver" in window&&whyHead){const benefitObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{if(!entry.isIntersecting)return;whyHead.classList.add("is-visible");benefits.forEach((card,index)=>{card.style.transitionDelay=`${100+index*70}ms`;card.classList.add("is-visible")});benefitObserver.disconnect()}),{threshold:.14});benefitObserver.observe($(".home-why"));}else{whyHead?.classList.add("is-visible");benefits.forEach(card=>card.classList.add("is-visible"))}
  const categoryCards=$$(".home-category");if("IntersectionObserver" in window&&categoryCards.length){const categoryObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{if(!entry.isIntersecting)return;categoryCards.forEach((card,index)=>{card.style.transitionDelay=`${index*60}ms`;card.classList.add("is-visible")});categoryObserver.disconnect()}),{threshold:.1});categoryObserver.observe($("#home-categories"));}else categoryCards.forEach(card=>card.classList.add("is-visible"));
  const news=$(".home-newsletter form"),email=$('input[type="email"]',news);if(news&&email){news.noValidate=true;news.addEventListener("submit",event=>{news.querySelector(".newsletter-error")?.remove();if(!email.validity.valid){event.preventDefault();event.stopImmediatePropagation();news.insertAdjacentHTML("beforeend",'<small class="newsletter-error">Please enter a valid email address.</small>');email.focus()}},true)}
}
function init(){
  if(!$(".home-v2"))return;renderHero();renderCategories();renderBrands();initBrandCarousel();renderProducts();premiumEnhancements();initAppShowcase();document.addEventListener("click",handleClick);
  $(".home-newsletter form")?.addEventListener("submit",event=>{event.preventDefault();const form=event.currentTarget,button=$(".home-club-submit",form),panel=form.closest(".home-club-panel");if(button){button.disabled=true;button.classList.add("is-loading");button.querySelector("span").textContent="Joining";button.querySelector("i").className="fa-solid fa-spinner"}window.setTimeout(()=>{form.reset();panel?.classList.add("is-success");ui.toast("Welcome to FreshBasket Club!");},650);});
}
window.FreshBasketHomepage=Object.freeze({init,renderProducts,showSlide,quickView});
if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",init);else init();
})(window,document);
