(function(window,document){
"use strict";
const store=window.FBStorage;
const $=(selector,context=document)=>context.querySelector(selector);
const $$=(selector,context=document)=>[...context.querySelectorAll(selector)];
const CITIES=["Ahmedabad","Gandhinagar"];
const AREAS=["Gota","Chandlodia","Chandkheda","Thaltej","Satellite","Navrangpura"];
const NAV_ITEMS=[
  ["Grocery","shop.html"],["Fruits and Vegetables","category.html?category=Fruits%20and%20Vegetables"],
  ["Dairy & Bakery","category.html?category=Dairy%20and%20Bakery"],["Packaged Food","category.html?category=Instant%20Food"],
  ["Beverages","category.html?category=Beverages%20and%20Juices"],["Personal Care","category.html?category=Personal%20Care"],
  ["Home Care","category.html?category=Household%20Cleaning"],["Baby Care","category.html?category=Baby%20Care"],
  ["Home & Kitchen","category.html?category=Home%20and%20Kitchen"],["Appliances","category.html?category=Small%20Appliances"],
  ["Offers","offers.html"]
];
const categoryUrl=category=>`category.html?category=${encodeURIComponent(category.name)}`;
function categories(){return window.CATEGORY_METADATA||[]}
const NAV_DEPARTMENTS={
  "Grocery":["Rice, Atta and Grains","Pulses and Dal","Cooking Oil and Ghee","Salt, Sugar and Jaggery","Spices and Masala","Dry Fruits and Nuts","Breakfast and Cereals"],
  "Fruits and Vegetables":["Fruits and Vegetables"],
  "Dairy & Bakery":["Dairy and Bakery"],
  "Packaged Food":["Biscuits and Cookies","Snacks and Namkeen","Instant Food","Noodles and Pasta","Frozen Food"],
  "Beverages":["Tea and Coffee","Beverages and Juices","Soft Drinks"],
  "Personal Care":["Personal Care","Hair Care","Skin Care","Oral Care"],
  "Home Care":["Household Cleaning","Laundry Care","Kitchen Cleaning"],
  "Baby Care":["Baby Care"],
  "Home & Kitchen":["Home and Kitchen","Pooja Essentials"],
  "Appliances":["Small Appliances"]
};
const NAV_PROMOS={
  "Grocery":["Monthly Grocery Savings","Save up to 25% on pantry essentials."],
  "Fruits and Vegetables":["Fresh from farm to basket","Quality produce selected for everyday meals."],
  "Dairy & Bakery":["Fresh dairy every day","Milk, paneer and bakery favourites delivered fresh."],
  "Packaged Food":["Snack more, save more","Tea-time treats and quick meals at better value."],
  "Beverages":["Refreshing savings","Tea, coffee and drinks for every moment."],
  "Personal Care":["Care for yourself every day","Trusted grooming and self-care essentials."],
  "Home Care":["A cleaner home for less","Dependable cleaning essentials for every room."],
  "Baby Care":["Gentle care for little ones","Thoughtfully selected baby essentials."],
  "Home & Kitchen":["Organise your kitchen beautifully","Useful solutions for easier routines."],
  "Appliances":["Smart appliances for everyday living","Reliable helpers for modern homes."]
};
function location(){
  return store.get("fb-location",{area:"Satellite",city:"Ahmedabad",pin:"380015",label:"Satellite, Ahmedabad"});
}
const utilityIcon=name=>{
  const paths={
    offer:'<path d="M3 4.5V9l5.5 5.5 6-6L9 3H4.5A1.5 1.5 0 0 0 3 4.5Z"/><path d="M6.5 6.5h.01"/>',
    pin:'<path d="M14 7c0 5-6 9-6 9S2 12 2 7a6 6 0 1 1 12 0Z"/><circle cx="8" cy="7" r="2"/>',
    code:'<path d="M2.5 8h11"/><path d="m6 4-4 4 4 4m4-8 4 4-4 4"/>',
    store:'<path d="M2 6h12l-1-3H3L2 6Z"/><path d="M3 6v8h10V6M6 14V9h4v5"/>',
    support:'<path d="M3 9V8a5 5 0 0 1 10 0v1"/><path d="M3 9H2v4h3V9H3Zm10 0h1v4h-3V9h2Zm0 4c0 1.5-1 2-3 2"/>',
    track:'<path d="M3 5.5 8 3l5 2.5v6L8 14l-5-2.5v-6Z"/><path d="m3 5.5 5 2.5 5-2.5M8 8v6"/>',
    globe:'<circle cx="8" cy="8" r="6"/><path d="M2 8h12M8 2a9 9 0 0 1 0 12M8 2a9 9 0 0 0 0 12"/>',
    more:'<circle cx="3" cy="8" r=".7"/><circle cx="8" cy="8" r=".7"/><circle cx="13" cy="8" r=".7"/>',
    chevron:'<path d="m5 6 3 3 3-3"/>'
  };
  return `<svg class="utility-action__icon" viewBox="0 0 16 16" aria-hidden="true">${paths[name]||""}</svg>`;
};
const brandMark=()=>`<span class="fb-brand-mark" aria-hidden="true"><svg viewBox="0 0 64 64"><defs><linearGradient id="fb-mark-bg" x1="8" y1="5" x2="56" y2="60" gradientUnits="userSpaceOnUse"><stop stop-color="#16834a"/><stop offset=".55" stop-color="#075c34"/><stop offset="1" stop-color="#033f26"/></linearGradient><linearGradient id="fb-mark-leaf" x1="37" y1="9" x2="49" y2="25" gradientUnits="userSpaceOnUse"><stop stop-color="#e6ff8a"/><stop offset="1" stop-color="#a9df4e"/></linearGradient></defs><rect class="fb-brand-tile" x="3" y="3" width="58" height="58" rx="18"/><path class="fb-brand-shine" d="M12 13c9-7 25-8 38-1"/><path class="fb-brand-leaf" d="M39 11c8 .4 12 4.8 11.4 12.2-7.5.8-12-2.7-12.5-9.7 3.3 2.3 5.7 4.7 7.6 7.5"/><path class="fb-brand-basket" d="M17 27.5h31l-3.7 21H20.7l-3.7-21Z"/><path class="fb-brand-handle" d="m14.5 27.5 7-9m29 9-6.8-9"/><path class="fb-brand-f" d="M25.2 43V31h9.7M25.4 36.2h7.1"/><path class="fb-brand-slat" d="M39.8 31v12"/></svg></span>`;
const brandWordmark=(tagline=true)=>`<span class="fb-brand-copy"><strong><span>Fresh</span><b>Basket</b></strong>${tagline?"<small>Freshness Delivered Daily</small>":""}<em>FB</em></span>`;
const megaIcon=category=>{
  const name=(category||"").toLowerCase();let path='<path d="M5 8h22l-2 18H7L5 8Z"/><path d="m9 8 3-5m11 5-3-5M11 14v6m5-6v6m5-6v6"/>';
  if(/fruit|vegetable|dry fruit|spice/.test(name))path='<path d="M16 8c-7 0-11 5-10 12 1 7 8 10 10 10s9-3 10-10c1-7-3-12-10-12Z"/><path d="M16 8c0-4 3-6 7-6M16 8c-3-4-7-3-9-1"/>';
  else if(/dairy|beverage|tea|coffee|drink/.test(name))path='<path d="M10 4h12l2 6v17H8V10l2-6Z"/><path d="M8 11h16M12 4v7"/>';
  else if(/oil|ghee|care|clean|laundry/.test(name))path='<path d="M12 3h8v5l4 4v16H8V12l4-4V3Z"/><path d="M12 8h8M12 17h8"/>';
  else if(/baby|pet|health/.test(name))path='<path d="M16 29s-11-6-11-16c0-6 8-8 11-2 3-6 11-4 11 2 0 10-11 16-11 16Z"/>';
  return `<svg viewBox="0 0 32 32" aria-hidden="true">${path}</svg>`;
};
function focusedMenu(label,index){
  if(label==="Offers")return `<div class="fb-focused-menu fb-focused-menu--offers" id="nav-menu-${index}" role="menu"><div><span class="eyebrow">FreshBasket savings</span><h3>Offers &amp; rewards</h3>${["Today's Deals","Top Discounts","Buy More Save More","Free Delivery Offers","Coupon Codes","Clearance Deals"].map(name=>`<a role="menuitem" href="offers.html">${name}<i class="fa-solid fa-arrow-right"></i></a>`).join("")}</div><aside><span>Save up to</span><strong>30%</strong><p>On selected everyday essentials.</p><a class="btn btn-primary" href="offers.html">View all offers</a></aside></div>`;
  const names=NAV_DEPARTMENTS[label]||[],metadata=categories().filter(category=>names.includes(category.name)),allSubs=metadata.flatMap(category=>category.subcategories.map(sub=>({sub,category}))),products=(window.PRODUCTS||[]).filter(item=>names.includes(item.category)).sort((a,b)=>(b.bestseller?1:0)-(a.bestseller?1:0)||b.rating-a.rating).slice(0,3);
  const promo=NAV_PROMOS[label]||[`Shop ${label}`,`Explore quality ${label.toLowerCase()} essentials.`],image=metadata[0]?.image||products[0]?.image||"assets/images/placeholders/product.svg";
  return `<div class="fb-focused-menu ${names.length>3?"fb-focused-menu--wide":""}" id="nav-menu-${index}" role="menu"><div class="fb-focused-main"><div class="fb-focused-head"><span class="eyebrow">Shop by aisle</span><h3>${label}</h3></div><div class="fb-focused-links">${(allSubs.length?allSubs:metadata.map(category=>({sub:{name:category.name,productCount:category.productCount},category}))).slice(0,12).map(({sub,category})=>`<a role="menuitem" href="category.html?category=${encodeURIComponent(category.name)}&subcategory=${encodeURIComponent(sub.name)}">${megaIcon(sub.name)}<span><b>${sub.name}</b><small>${sub.productCount||""} products</small></span><i class="fa-solid fa-arrow-right"></i></a>`).join("")}</div><a class="fb-focused-view" href="${NAV_ITEMS[index][1]}">View all ${label} <i class="fa-solid fa-arrow-right"></i></a></div><div class="fb-focused-picks"><span class="eyebrow">Popular picks</span><div>${products.map(item=>`<article><a href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"><span><b>${item.shortName||item.name}</b><small>${item.weight}</small><strong>${window.FBUI.money(item.sellingPrice||item.price)}</strong></span></a><button type="button" data-nav-add="${item.id}" aria-label="Add ${item.name}">Add</button></article>`).join("")||'<p>Explore popular essentials in this department.</p>'}</div></div><aside class="fb-focused-promo"><span class="fb-focused-badge">Featured</span><img src="${image}" alt="${metadata[0]?.imageAlt||label}" onerror="imageFallback(this)"><h3>${promo[0]}</h3><p>${promo[1]}</p><a class="btn btn-primary" href="${NAV_ITEMS[index][1]}">Shop now <i class="fa-solid fa-arrow-right"></i></a></aside></div>`;
}
function shell(){
  const current=location(),cart=store.get("fb-cart",[]),wish=store.get("fb-wish",[]),auth=store.get("fb-auth",null);
  const fulfilment=store.get("fb-fulfilment","delivery"),language=store.get("fb-language","English"),pickupLabel=fulfilment==="pickup"?`Pickup: ${current.area} Store`:"Pickup Point";
  const accountName=auth?.loggedIn?(auth.name||"Member").split(" ")[0]:"Hello!";
  const count=cart.reduce((sum,item)=>sum+Number(item.qty||0),0);
  return `<div class="fb-site-header" data-global-header>
    <div class="fb-topbar utility-bar"><div class="container site-container"><div class="utility-bar__inner">
      <div class="utility-bar__offer">${utilityIcon("offer")}<strong>Everyday Low Prices</strong><span class="utility-offer__divider" aria-hidden="true"></span><span>Grocery &amp; Household Essentials</span></div>
      <div class="utility-bar__actions">
        <div class="utility-group utility-group--location"><button class="utility-action" type="button" data-location-open aria-label="Change delivery location">${utilityIcon("pin")}<span data-location-short>${current.area}, ${current.city}</span>${utilityIcon("chevron")}</button><button class="utility-action utility-action--accent" type="button" data-location-open data-pin-open>${utilityIcon("code")}<span>Select PIN Code</span></button></div>
        <div class="utility-group"><button class="utility-action" type="button" data-location-open data-pickup-open>${utilityIcon("store")}<span data-pickup-label>${pickupLabel}</span></button></div>
        <div class="utility-group utility-group--support"><a class="utility-action" href="contact.html">${utilityIcon("support")}<span>Support</span></a><a class="utility-action" href="track-order.html">${utilityIcon("track")}<span>Track Order</span></a></div>
        <div class="utility-menu utility-language"><button class="utility-action utility-menu__trigger" type="button" aria-haspopup="menu" aria-expanded="false">${utilityIcon("globe")}<span data-language-label>${language}</span>${utilityIcon("chevron")}</button><div class="utility-menu__panel" role="menu" aria-label="Language"><button role="menuitem" type="button" data-language="English">English</button><button role="menuitem" type="button" data-language="Gujarati">Gujarati</button><button role="menuitem" type="button" data-language="Hindi">Hindi</button><small>Display preference only</small></div></div>
        <div class="utility-menu utility-more"><button class="utility-action utility-menu__trigger" type="button" aria-label="More services" aria-haspopup="menu" aria-expanded="false">${utilityIcon("more")}<span>More</span></button><div class="utility-menu__panel utility-more__panel" role="menu"><button role="menuitem" type="button" data-location-open data-pin-open>${utilityIcon("code")} Select PIN Code</button><button role="menuitem" type="button" data-location-open data-pickup-open>${utilityIcon("store")} ${pickupLabel}</button><a role="menuitem" href="contact.html">${utilityIcon("support")} Support</a><a role="menuitem" href="track-order.html">${utilityIcon("track")} Track Order</a><button role="menuitem" type="button" data-utility-language>${utilityIcon("globe")} Language: ${language}</button></div></div>
      <select class="fb-language" aria-label="Select language"><option>English</option><option>ગુજરાતી</option><option>हिन्दी</option></select>
    </div></div></div></div>
    <div class="fb-shopping-header"><div class="fb-header-main"><div class="container fb-header-main-inner">
      <a class="fb-header-logo" href="index.html" aria-label="FreshBasket home">${brandMark()}${brandWordmark()}</a>
      <button class="fb-location-control" type="button" data-location-open aria-label="Change delivery location" title="${current.label}"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M16 8c0 5-6 9-6 9S4 13 4 8a6 6 0 1 1 12 0Z"/><circle cx="10" cy="8" r="2"/></svg><span><small>Deliver to</small><b data-location-label>${current.label}</b></span><i class="fa-solid fa-chevron-down"></i></button>
      <form class="fb-header-search search" role="search"><div class="fb-search-box"><i class="fa-solid fa-magnifying-glass"></i><input id="global-search" aria-label="Search products" placeholder="Search for products, brands and categories" autocomplete="off" aria-autocomplete="list" aria-expanded="false"><button class="fb-search-clear" type="button" aria-label="Clear search"><i class="fa-solid fa-xmark"></i></button><button class="fb-search-submit" type="submit" aria-label="Search"><i class="fa-solid fa-arrow-right"></i></button></div><div class="suggestions fb-search-suggestions" role="listbox"></div></form>
      <div class="fb-header-actions"><a class="fb-header-action" href="${auth?.loggedIn?"account.html":"login.html"}"><i class="fa-regular fa-user"></i><span><small>${accountName}</small><b>${auth?.loggedIn?"Account":"Login"}</b></span></a>
        <a class="fb-header-action" href="wishlist.html" aria-label="Wishlist"><i class="fa-regular fa-heart"></i><span><small>Saved</small><b>Wishlist</b></span><span class="badge-count wish-count">${wish.length}</span></a>
        <a class="fb-header-action cart-button" href="cart.html" aria-label="Cart"><i class="fa-solid fa-basket-shopping"></i><span><small>Basket</small><b><span class="cart-count">${count}</span> items</b></span><span class="fb-cart-total cart-total cart-amount">₹0</span></a>
      </div>
    </div></div>
    <div class="fb-category-bar"><nav class="container fb-category-nav" aria-label="Store departments"><a class="fb-all-products ${/shop\.html$/i.test(window.location.pathname)?"is-active":""}" href="shop.html"><i class="fa-solid fa-border-all"></i><span>All Products</span></a>${NAV_ITEMS.map((item,index)=>`<div class="fb-nav-dropdown ${index>=8&&index<=10?"fb-nav-secondary":""}"><button type="button" data-nav-trigger="${index}" aria-haspopup="menu" aria-expanded="false" aria-controls="nav-menu-${index}">${item[0]} <i class="fa-solid fa-chevron-down"></i></button>${focusedMenu(item[0],index)}</div>`).join("")}<div class="fb-nav-more"><button type="button" aria-haspopup="menu" aria-expanded="false">More <i class="fa-solid fa-chevron-down"></i></button><div role="menu">${NAV_ITEMS.slice(8,11).map((item,index)=>`<a role="menuitem" href="${item[1]}">${item[0]}</a>`).join("")}</div></div></nav></div></div>
    <div class="fb-mobile-main"><div class="container"><div class="fb-mobile-row"><button class="fb-mobile-button" type="button" data-mobile-open aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
      <a class="fb-header-logo fb-mobile-logo" href="index.html" aria-label="FreshBasket home">${brandMark()}${brandWordmark(false)}</a>
      <a class="fb-mobile-button" href="${auth?.loggedIn?"account.html":"login.html"}" aria-label="Account"><i class="fa-regular fa-user"></i></a>
      <a class="fb-mobile-button" href="cart.html" aria-label="Cart"><i class="fa-solid fa-basket-shopping"></i><span class="badge-count cart-count">${count}</span></a></div>
      <button class="fb-mobile-location" type="button" data-location-open><i class="fa-solid fa-location-dot"></i><span data-location-label>${current.label}</span><i class="fa-solid fa-chevron-down"></i></button>
      <form class="fb-mobile-search search" role="search"><div class="fb-search-box"><i class="fa-solid fa-magnifying-glass"></i><input aria-label="Search products" placeholder="Search products and brands" autocomplete="off" aria-autocomplete="list" aria-expanded="false"><button class="fb-search-clear" type="button" aria-label="Clear search"><i class="fa-solid fa-xmark"></i></button><button class="fb-search-submit" type="submit" aria-label="Search"><i class="fa-solid fa-arrow-right"></i></button></div><div class="suggestions fb-search-suggestions" role="listbox"></div></form>
    </div></div>
  </div><button class="fb-mobile-backdrop" type="button" aria-label="Close menu"></button><aside class="fb-mobile-drawer" aria-hidden="true"></aside>`;
}
function bottomNav(){
  return `<nav class="mobile-bottom" aria-label="Mobile navigation"><a href="index.html"><i class="fa-solid fa-house"></i>Home</a><a href="shop.html"><i class="fa-solid fa-store"></i>Shop</a><a href="offers.html"><i class="fa-solid fa-tags"></i>Offers</a><a href="cart.html"><i class="fa-solid fa-basket-shopping"></i>Cart<span class="badge-count cart-count">0</span></a><a href="account.html"><i class="fa-regular fa-user"></i>Account</a></nav>`;
}
function render(){
  if($(".fb-site-header"))return;
  const mount=$("#site-header,[data-global-header]");
  const utility=$(".utility"),header=$(".main-header"),nav=$(".nav"),drawer=$(".mobile-drawer");
  if(mount)mount.outerHTML=shell();else if(utility)utility.insertAdjacentHTML("beforebegin",shell());else document.body.insertAdjacentHTML("afterbegin",shell());
  utility?.remove();header?.remove();nav?.remove();drawer?.remove();
  const oldBottom=$(".mobile-bottom");if(oldBottom)oldBottom.outerHTML=bottomNav();else document.body.insertAdjacentHTML("beforeend",bottomNav());
  renderMobileDrawer();bind();
  applyActiveState();
}
function applyActiveState(){
  const page=(window.location.pathname.split("/").pop()||"index.html").toLowerCase(),query=new URLSearchParams(window.location.search),category=(query.get("category")||"").toLowerCase();
  $$(".fb-category-nav > a,.fb-nav-more a").forEach(link=>{const href=link.getAttribute("href")||"",target=href.split("?")[0].toLowerCase();let match=target===page;if(page==="category.html"&&href.includes("category=")){const value=decodeURIComponent((href.split("category=")[1]||"").split("&")[0]).toLowerCase();match=category.includes(value)||value.includes(category)}link.classList.toggle("is-active",match);if(match)link.setAttribute("aria-current","page")});
  $$("[data-nav-trigger]").forEach(button=>{const item=NAV_ITEMS[Number(button.dataset.navTrigger)],href=item?.[1]||"",target=href.split("?")[0].toLowerCase();let match=target===page;if(page==="category.html"){const names=NAV_DEPARTMENTS[item?.[0]]||[],matchCategory=names.some(name=>{const value=name.toLowerCase();return category.includes(value)||value.includes(category)});match=matchCategory}button.classList.toggle("is-active",match);if(match)button.setAttribute("aria-current","page")});
  $$(".fb-header-action").forEach(link=>{const target=(link.getAttribute("href")||"").split("?")[0].toLowerCase();if(target===page)link.setAttribute("aria-current","page")});
}
function renderMobileDrawer(){
  const drawer=$(".fb-mobile-drawer"),current=location();if(!drawer)return;
  const mobileGroups=NAV_ITEMS.map((item,index)=>{const names=NAV_DEPARTMENTS[item[0]]||[],groups=categories().filter(category=>names.includes(category.name)),links=groups.flatMap(category=>category.subcategories.slice(0,6).map(sub=>`<a href="category.html?category=${encodeURIComponent(category.name)}&subcategory=${encodeURIComponent(sub.name)}">${sub.name}</a>`));if(item[0]==="Offers")links.push('<a href="offers.html">Today’s deals</a><a href="coupons.html">Coupon codes</a>');return `<div class="fb-mobile-category"><button class="fb-mobile-category-toggle" type="button" aria-expanded="false" data-mobile-category="${index}"><span>${megaIcon(item[0])} ${item[0]}</span><i class="fa-solid fa-chevron-right"></i></button><div class="fb-mobile-subcategories"><div class="fb-mobile-subhead"><button type="button" data-mobile-category-back><i class="fa-solid fa-arrow-left"></i> Back</button><b>${item[0]}</b></div>${links.join("")}<a href="${item[1]}"><b>View all ${item[0]}</b></a></div></div>`}).join("");
  drawer.innerHTML=`<div class="fb-mobile-drawer-head"><a class="fb-header-logo" href="index.html">${brandMark()}${brandWordmark(false)}</a><button class="icon-btn" type="button" data-mobile-close aria-label="Close menu">&times;</button></div>
    <div class="fb-mobile-quick"><button type="button" data-location-open><i class="fa-solid fa-location-dot"></i> ${current.area}</button><a href="track-order.html"><i class="fa-solid fa-box"></i> Track order</a><a href="wishlist.html"><i class="fa-regular fa-heart"></i> Wishlist</a><a href="offers.html"><i class="fa-solid fa-tags"></i> Offers</a></div>
    <h3>Shop by department</h3><div class="fb-mobile-category-list">${mobileGroups}</div>`;
}
function closeFocusedMenus(){
  $$(".fb-nav-dropdown.is-open").forEach(item=>{item.classList.remove("is-open");$("button[data-nav-trigger]",item)?.setAttribute("aria-expanded","false");});
}
function premiumLocationMarkup(modal,selected,fulfillment,recent,addresses){
  $("#ui-modal-title",modal).textContent="Choose your location";
  $(".ui-modal-header",modal)?.insertAdjacentHTML("afterbegin",'<span class="fb-location-header-icon"><i class="fa-solid fa-location-dot"></i></span>');
  const saved=addresses.slice(0,2);
  const body=$(".ui-modal-body",modal);
  body.innerHTML=`<div class="fb-loc">
    <div class="fb-loc-top">
      <button class="fb-loc-detect" type="button" data-detect-location><i class="fa-solid fa-location-crosshairs"></i><span>Use current location</span><em>Detect</em></button>
      <div class="fb-loc-mode" role="radiogroup" aria-label="Fulfilment type">
        <label class="fb-loc-mode-card"><input type="radio" name="location-fulfillment" value="delivery" ${fulfillment==="delivery"?"checked":""}><i class="fa-solid fa-truck-fast"></i><span><b>Home delivery</b><small>Today, 7–9 PM</small></span></label>
        <label class="fb-loc-mode-card"><input type="radio" name="location-fulfillment" value="pickup" ${fulfillment==="pickup"?"checked":""}><i class="fa-solid fa-store"></i><span><b>Store pickup</b><small>Free · Ready soon</small></span></label>
      </div>
    </div>
    <div class="fb-loc-fields">
      <div class="fb-loc-field"><label for="location-pin">PIN code</label><div class="fb-loc-pin"><input class="form-control" id="location-pin" inputmode="numeric" maxlength="6" value="${selected.pin||""}" placeholder="6-digit PIN" aria-describedby="location-note"><button class="btn btn-primary" type="button" data-check-pin>Check</button></div></div>
      <div class="fb-loc-field"><label for="location-city">City</label><select class="form-control" id="location-city">${CITIES.map(city=>`<option ${city===selected.city?"selected":""}>${city}</option>`).join("")}</select></div>
    </div>
    <p class="fb-location-note" id="location-note" aria-live="polite"></p>
    <div class="fb-loc-block"><div class="fb-loc-block-head"><h3>Areas in Ahmedabad</h3><small>Delivery today</small></div><div class="fb-loc-areas">${AREAS.map(area=>`<label class="fb-loc-chip"><input type="radio" name="location-area" value="${area}" ${area===selected.area?"checked":""}><span>${area}</span></label>`).join("")}</div></div>
    <div class="fb-loc-meta">
      ${recent.length?`<div class="fb-loc-block"><div class="fb-loc-block-head"><h3>Recent</h3></div><div class="fb-loc-recent">${recent.slice(0,3).map(area=>`<button type="button" data-location-area="${area}">${area}</button>`).join("")}</div></div>`:""}
      <div class="fb-loc-block"><div class="fb-loc-block-head"><h3>Saved</h3><a href="addresses.html">Manage</a></div><div class="fb-loc-saved">${saved.map(address=>`<button type="button" data-saved-location="${address.text||address.address||""}" data-saved-area="${address.area||selected.area}"><i class="fa-solid ${String(address.type).toLowerCase()==="work"?"fa-building":"fa-house"}"></i><span><b>${address.type||"Saved"}</b><small>${address.area||selected.area}</small></span>${address.default?'<em>Default</em>':""}</button>`).join("")||'<a class="fb-loc-empty" href="addresses.html">Add a saved address</a>'}</div></div>
    </div>
    <div class="fb-loc-summary" aria-live="polite"><i class="fa-solid fa-truck-fast" data-summary-icon></i><div><b data-summary-type>${fulfillment==="pickup"?"Store pickup":"Home delivery"}</b><small data-summary-place>${selected.area}, ${selected.city} ${selected.pin||""}</small></div><span data-summary-time>${fulfillment==="pickup"?"Free pickup":"Today, 7–9 PM"}</span></div>
  </div>`;
  $(".ui-modal-footer",modal).innerHTML='<button class="btn btn-outline" type="button" data-modal-close>Cancel</button><button class="btn btn-primary" type="button" data-location-confirm>Confirm location <i class="fa-solid fa-arrow-right"></i></button>';
}
function openMobile(){
  $(".fb-mobile-drawer")?.classList.add("is-open");$(".fb-mobile-backdrop")?.classList.add("is-open");$(".fb-mobile-drawer")?.setAttribute("aria-hidden","false");$("[data-mobile-open]")?.setAttribute("aria-expanded","true");document.body.classList.add("scroll-lock");window.setTimeout(()=>$("[data-mobile-close]")?.focus(),30);
}
function closeMobile(){
  $(".fb-mobile-drawer")?.classList.remove("is-open");$(".fb-mobile-backdrop")?.classList.remove("is-open");$(".fb-mobile-drawer")?.setAttribute("aria-hidden","true");$("[data-mobile-open]")?.setAttribute("aria-expanded","false");document.body.classList.remove("scroll-lock");
}
function openLocation(preferPickup=false){
  const selected=location(),fulfilment=preferPickup?"pickup":store.get("fb-fulfilment","delivery");
  const recent=store.get("fb-recent-locations",["Thaltej","Navrangpura"]).filter(area=>AREAS.includes(area));
  const addresses=store.get("fb-addresses",[]);
  const modal=window.FBUI.openModal({title:"Choose delivery or pickup location",content:`<div class="fb-location-grid">
    <div class="fb-location-full"><button class="btn btn-outline fb-detect-location" type="button" data-detect-location><i class="fa-solid fa-location-crosshairs"></i> Detect current location</button><p class="fb-location-note" id="location-note"></p></div>
    <div class="form-group"><label class="form-label" for="location-pin">Enter PIN code</label><div class="fb-pin-form"><input class="form-control" id="location-pin" inputmode="numeric" maxlength="6" value="${selected.pin||""}" placeholder="6-digit PIN"><button class="btn btn-outline" type="button" data-check-pin>Check</button></div></div>
    <div class="form-group"><label class="form-label" for="location-city">City</label><select class="form-control" id="location-city">${CITIES.map(city=>`<option ${city===selected.city?"selected":""}>${city}</option>`).join("")}</select></div>
    <div class="fb-location-full"><label class="form-label">Ahmedabad locations</label><div class="fb-choice-list">${AREAS.map(area=>`<label class="fb-choice"><input type="radio" name="location-area" value="${area}" ${area===selected.area?"checked":""}> ${area}</label>`).join("")}</div></div>
    <div><label class="form-label">Recent locations</label><div class="fb-choice-list">${recent.map(area=>`<button class="fb-choice" type="button" data-location-area="${area}">${area}</button>`).join("")||"<small>No recent locations</small>"}</div></div>
    <div><label class="form-label">Saved addresses</label><div class="fb-choice-list">${addresses.slice(0,3).map(address=>`<button class="fb-choice" type="button" data-saved-location="${address.text||address.address||""}" data-saved-area="${address.area||selected.area}">${address.type||"Saved"} · ${address.area||selected.area}</button>`).join("")||'<a class="fb-choice" href="addresses.html">Add saved address</a>'}</div></div>
    <div class="fb-location-full"><label class="form-label">Fulfilment type</label><div class="fb-choice-list"><label class="fb-choice"><input type="radio" name="location-fulfilment" value="delivery" ${fulfilment==="delivery"?"checked":""}><i class="fa-solid fa-truck-fast"></i> Home delivery</label><label class="fb-choice"><input type="radio" name="location-fulfilment" value="pickup" ${fulfilment==="pickup"?"checked":""}><i class="fa-solid fa-store"></i> Pickup</label></div></div>
  </div>`,footer:'<button class="btn btn-outline" type="button" data-modal-close>Cancel</button><button class="btn btn-primary" type="button" data-location-confirm>Confirm location</button>'});
  modal.classList.add("fb-location-modal");
  premiumLocationMarkup(modal,selected,fulfillment,recent,addresses);
  const syncSummary=()=>{
    const area=$('input[name="location-area"]:checked',modal)?.value||selected.area;
    const city=$("#location-city",modal)?.value||selected.city;
    const pin=$("#location-pin",modal)?.value.trim()||selected.pin||"";
    const type=$('input[name="location-fulfillment"]:checked',modal)?.value||"delivery";
    const pickup=type==="pickup";
    const icon=$("[data-summary-icon]",modal),typeNode=$("[data-summary-type]",modal),place=$("[data-summary-place]",modal),time=$("[data-summary-time]",modal);
    if(icon)icon.className=`fa-solid ${pickup?"fa-store":"fa-truck-fast"}`;
    if(typeNode)typeNode.textContent=pickup?"Store pickup":"Home delivery";
    if(place)place.textContent=`${area}, ${city}${pin?` ${pin}`:""}`;
    if(time)time.textContent=pickup?"Free pickup":"Today, 7–9 PM";
  };
  const choose=area=>{const radio=$(`input[name="location-area"][value="${area}"]`,modal);if(radio){radio.checked=true;syncSummary();}};
  $$("[data-location-area]",modal).forEach(button=>button.onclick=()=>choose(button.dataset.locationArea));
  $$("[data-saved-location]",modal).forEach(button=>button.onclick=()=>choose(button.dataset.savedArea));
  $$('input[name="location-area"],input[name="location-fulfillment"]',modal).forEach(input=>input.addEventListener("change",syncSummary));
  $("#location-city",modal)?.addEventListener("change",syncSummary);
  $("#location-pin",modal)?.addEventListener("input",syncSummary);
  $("[data-check-pin]",modal).onclick=()=>{const pin=$("#location-pin",modal).value.trim(),note=$("#location-note",modal);note.textContent=/^\d{6}$/.test(pin)?"PIN available for delivery.":"Enter a valid 6-digit PIN.";note.style.color=/^\d{6}$/.test(pin)?"#16803b":"var(--red)";syncSummary();};
  $("[data-detect-location]",modal).onclick=()=>{const note=$("#location-note",modal);note.textContent="Detecting your location…";if(!navigator.geolocation){note.textContent="Location detection unavailable. Choose an area.";return;}navigator.geolocation.getCurrentPosition(()=>{choose("Satellite");note.textContent="Detected near Satellite, Ahmedabad.";},()=>{note.textContent="Location permission unavailable. Choose an area.";},{timeout:5000});};
  $("[data-location-confirm]",modal).onclick=()=>{
    const area=$('input[name="location-area"]:checked',modal)?.value||selected.area,city=$("#location-city",modal).value,pin=$("#location-pin",modal).value.trim()||selected.pin;
    const type=$('input[name="location-fulfillment"]:checked',modal)?.value||"delivery";
    store.set("fb-location",{area,city,pin,label:`${area}, ${city}${pin?` ${pin}`:""}`});store.set("fb-fulfillment",type);
    store.set("fb-recent-locations",[area,...recent.filter(item=>item!==area)].slice(0,4));window.FBUI.closeModal(modal);updateLocation();window.FBUI.toast(`${type==="pickup"?"Pickup":"Delivery"} location updated`);
  };
}
function updateLocation(){
  const current=location(),pickup=store.get("fb-fulfilment","delivery")==="pickup"?`Pickup: ${current.area} Store`:"Pickup Point";
  $$("[data-location-label]").forEach(node=>node.textContent=current.label);$$("[data-location-short]").forEach(node=>node.textContent=`${current.area}, ${current.city}`);$$("[data-pickup-label]").forEach(node=>node.textContent=pickup);renderMobileDrawer();
}
function closeUtilityMenus(except){
  $$(".utility-menu.is-open").forEach(menu=>{if(menu===except)return;menu.classList.remove("is-open");$(".utility-menu__trigger",menu)?.setAttribute("aria-expanded","false");});
}
function bindSearch(){
  $$(".search").forEach(form=>{
    const input=$("input",form),panel=$(".fb-search-suggestions",form),clear=$(".fb-search-clear",form);if(!input||!panel)return;
    form.dataset.enhancedSearch="true";let active=-1;
    const close=()=>{panel.classList.remove("open");input.setAttribute("aria-expanded","false");active=-1;};
    const draw=()=>{
      const query=input.value.trim().toLowerCase(),recent=store.get("fb-search-history",[]).slice(0,3),products=(window.PRODUCTS||[]).filter(item=>(`${item.name} ${item.brand} ${item.category} ${item.subcategory||""}`).toLowerCase().includes(query)).slice(0,5);
      clear?.classList.toggle("is-visible",Boolean(query));
      if(!query){panel.innerHTML=`<div class="fb-suggest-head">Recent searches</div>${recent.map(term=>`<button type="button" data-search-term="${term}"><i class="fa-solid fa-clock-rotate-left"></i><span>${term}</span></button>`).join("")}<div class="fb-suggest-head">Popular searches</div>${["Atta","Fresh vegetables","Milk","Cooking oil"].map(term=>`<button type="button" data-search-term="${term}"><i class="fa-solid fa-arrow-trend-up"></i><span>${term}</span></button>`).join("")}`;}
      else if(products.length){panel.innerHTML=`<div class="fb-suggest-head">Product matches</div>${products.map(item=>`<a href="product-details.html?id=${item.id}" role="option"><img src="${item.image}" alt="" onerror="imageFallback(this)"><span><b>${item.name}</b><small>${item.brand} · ${item.weight||item.unit||""}</small></span><strong>${window.FBUI.money(item.sellingPrice||item.price)}</strong><i class="fa-solid fa-arrow-right"></i></a>`).join("")}<a class="fb-suggest-all" href="search-results.html?q=${encodeURIComponent(input.value.trim())}">View all results</a>`;}
      else panel.innerHTML='<div class="fb-suggest-empty"><b>No matching products</b><small>Try a product, brand, or category name.</small></div>';
      panel.classList.add("open");input.setAttribute("aria-expanded","true");
      $$("[data-search-term]",panel).forEach(button=>button.onclick=()=>{input.value=button.dataset.searchTerm;draw();input.focus();});
    };
    input.addEventListener("focus",draw);input.addEventListener("input",draw);
    clear?.addEventListener("click",()=>{input.value="";draw();input.focus();});
    input.addEventListener("keydown",event=>{const choices=$$("a,button",panel);if(event.key==="Escape"){close();return}if(event.key==="ArrowDown"||event.key==="ArrowUp"){event.preventDefault();active=(active+(event.key==="ArrowDown"?1:-1)+choices.length)%choices.length;choices[active]?.focus();}});
    form.addEventListener("submit",event=>{event.preventDefault();const term=input.value.trim();if(!term)return input.focus();store.set("fb-search-history",[term,...store.get("fb-search-history",[]).filter(item=>item.toLowerCase()!==term.toLowerCase())].slice(0,6));window.location.href=`search-results.html?q=${encodeURIComponent(term)}`;});
    document.addEventListener("click",event=>{if(!form.contains(event.target))close();});
  });
}
function bind(){
  const bar=$(".fb-category-bar"),navMore=$(".fb-nav-more"),navMoreButton=$(".fb-nav-more > button");let closeTimer,openTimer;
  const openFocused=item=>{clearTimeout(closeTimer);closeFocusedMenus();item.classList.add("is-open");$("button[data-nav-trigger]",item)?.setAttribute("aria-expanded","true");positionFocused(item);};
  const scheduleOpen=item=>{clearTimeout(openTimer);openTimer=window.setTimeout(()=>openFocused(item),120)};
  const scheduleClose=()=>{clearTimeout(openTimer);clearTimeout(closeTimer);closeTimer=window.setTimeout(closeFocusedMenus,220)};
  $$(".fb-nav-dropdown").forEach(item=>{const button=$("button[data-nav-trigger]",item);button?.addEventListener("click",event=>{event.stopPropagation();item.classList.contains("is-open")?closeFocusedMenus():openFocused(item)});item.addEventListener("mouseenter",()=>window.innerWidth>1050&&scheduleOpen(item));item.addEventListener("mouseleave",scheduleClose);item.addEventListener("focusin",()=>openFocused(item));});
  bar?.addEventListener("click",event=>{const add=event.target.closest("[data-nav-add]");if(!add)return;event.preventDefault();event.stopPropagation();window.addCart?.(Number(add.dataset.navAdd));syncCounts();window.FBUI.toast("Added to basket");});
  navMoreButton?.addEventListener("click",event=>{event.stopPropagation();const open=!navMore.classList.contains("is-open");navMore.classList.toggle("is-open",open);navMoreButton.setAttribute("aria-expanded",String(open));});
  document.addEventListener("click",event=>{if(!event.target.closest(".fb-category-bar"))closeFocusedMenus();if(!event.target.closest(".utility-menu"))closeUtilityMenus();if(!event.target.closest(".fb-nav-more")){navMore?.classList.remove("is-open");navMoreButton?.setAttribute("aria-expanded","false");}});
  document.addEventListener("keydown",event=>{if(event.key==="Escape"){closeFocusedMenus();closeMobile();closeUtilityMenus();navMore?.classList.remove("is-open");navMoreButton?.setAttribute("aria-expanded","false");}if(event.key==="Tab"&&$(".fb-mobile-drawer")?.classList.contains("is-open")){const focusable=$$('a,button,input,select,[tabindex]:not([tabindex="-1"])',$(".fb-mobile-drawer")).filter(node=>!node.closest(".fb-mobile-subcategories")||node.closest(".fb-mobile-category.is-open"));if(!focusable.length)return;const first=focusable[0],last=focusable[focusable.length-1];if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus()}else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus()}}});
  $$(".utility-menu__trigger").forEach(button=>button.addEventListener("click",event=>{event.stopPropagation();const menu=button.closest(".utility-menu"),open=!menu.classList.contains("is-open");closeUtilityMenus(menu);menu.classList.toggle("is-open",open);button.setAttribute("aria-expanded",String(open));if(open)$("[role=menuitem]",menu)?.focus();}));
  $$("[data-language]").forEach(button=>button.addEventListener("click",()=>{store.set("fb-language",button.dataset.language);$$("[data-language-label]").forEach(node=>node.textContent=button.dataset.language);closeUtilityMenus();window.FBUI.toast(`Language preference: ${button.dataset.language}`);}));
  $("[data-utility-language]")?.addEventListener("click",()=>{$(".utility-language .utility-menu__trigger")?.click();});
  $$("[data-location-open]").forEach(button=>button.onclick=()=>{closeUtilityMenus();openLocation(button.hasAttribute("data-pickup-open"));if(button.hasAttribute("data-pin-open"))window.setTimeout(()=>$("#location-pin")?.focus(),80);});
  $("[data-mobile-open]")?.addEventListener("click",openMobile);$("[data-mobile-close]")?.addEventListener("click",closeMobile);$(".fb-mobile-backdrop")?.addEventListener("click",closeMobile);
  $(".fb-mobile-drawer")?.addEventListener("click",event=>{const back=event.target.closest("[data-mobile-category-back]");if(back){const item=back.closest(".fb-mobile-category");item.classList.remove("is-open");$(".fb-mobile-category-toggle",item)?.setAttribute("aria-expanded","false");return}const button=event.target.closest("[data-mobile-category]");if(!button)return;const item=button.closest(".fb-mobile-category"),willOpen=!item.classList.contains("is-open");$$(".fb-mobile-category.is-open").forEach(node=>node.classList.remove("is-open"));item.classList.toggle("is-open",willOpen);button.setAttribute("aria-expanded",String(willOpen));});
  bindSearch();
  const siteHeader=$(".fb-site-header");
  let utilityCondensed=siteHeader?.classList.contains("is-condensed")||false;
  let compactFrame=0;
  const compactUtility=()=>{
    compactFrame=0;
    if(!siteHeader)return;
    if(window.innerWidth<=768){
      utilityCondensed=false;
    }else if(!utilityCondensed&&window.scrollY>72){
      utilityCondensed=true;
    }else if(utilityCondensed&&window.scrollY<18){
      utilityCondensed=false;
    }
    siteHeader.classList.toggle("is-condensed",utilityCondensed);
  };
  const scheduleCompact=()=>{if(!compactFrame)compactFrame=window.requestAnimationFrame(compactUtility);};
  window.addEventListener("scroll",scheduleCompact,{passive:true});window.addEventListener("resize",()=>{$$(".fb-nav-dropdown.is-open").forEach(positionFocused);scheduleCompact();});compactUtility();
}
function positionFocused(item){
  const menu=$(".fb-focused-menu",item);if(!menu)return;menu.style.left="0";menu.style.right="auto";const rect=menu.getBoundingClientRect(),gutter=20;if(rect.right>window.innerWidth-gutter){menu.style.left="auto";menu.style.right="0"}if(menu.getBoundingClientRect().left<gutter){menu.style.left=`${gutter-item.getBoundingClientRect().left}px`;menu.style.right="auto";}
}
function syncCounts(){
  const cart=store.get("fb-cart",[]),wish=store.get("fb-wish",[]),products=new Map((window.PRODUCTS||[]).map(item=>[item.id,item]));
  const count=cart.reduce((sum,item)=>sum+Number(item.qty||0),0),total=cart.reduce((sum,item)=>sum+(products.get(Number(item.id))?.sellingPrice||0)*Number(item.qty||0),0);
  $$(".cart-count").forEach(node=>node.textContent=count);$$(".wish-count").forEach(node=>node.textContent=wish.length);$$(".cart-total").forEach(node=>node.textContent=window.FBUI.money(total));
}
let initialized=false;
function init(){if(initialized)return;initialized=true;if(!document.querySelector('link[rel="icon"]')){const favicon=document.createElement("link");favicon.rel="icon";favicon.type="image/svg+xml";favicon.href="assets/images/logo/freshbasket-mark.svg";document.head.appendChild(favicon)}render();syncCounts();window.addEventListener("storage",()=>{syncCounts();updateLocation();});document.addEventListener("freshbasket:statechange",syncCounts);}
window.FreshBasketHeader=Object.freeze({init,render,syncCounts,openLocation,closeMenus:closeFocusedMenus});
init();
})(window,document);
