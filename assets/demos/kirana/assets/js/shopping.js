(function(window,document){
"use strict";
const store=window.FBStorage;
const ui=window.FBUI;
const $=(selector,context=document)=>context.querySelector(selector);
const money=ui.money;
const KEYS={cart:"fb-cart",wishlist:"fb-wish",saved:"fb-saved",compare:"fb-compare",coupon:"fb-coupon",fulfilment:"fb-fulfilment"};
const COUPONS={
  WELCOME100:{label:"₹100 off",description:"On orders of ₹499 or more",minimum:499,type:"flat",value:100},
  SAVE10:{label:"10% off",description:"Up to ₹200 off on orders of ₹799+",minimum:799,type:"percent",value:10,max:200},
  FREEDEL:{label:"Free delivery",description:"On orders of ₹199 or more",minimum:199,type:"delivery",value:49},
  MONTHLY250:{label:"₹250 off",description:"On monthly baskets of ₹1,499+",minimum:1499,type:"flat",value:250}
};
const product=id=>(window.getProductById?window.getProductById(id):window.PRODUCTS.find(item=>item.id===Number(id)));
const ids=key=>store.get(key,[]).map(Number).filter(id=>product(id));
const cart=()=>store.get(KEYS.cart,[]).map(item=>({id:Number(item.id),qty:Math.max(1,Number(item.qty)||1)})).filter(item=>product(item.id));
const saveCart=items=>{store.set(KEYS.cart,items);sync();};
function sync(){window.FreshBasketHeader?.syncCounts();document.dispatchEvent(new CustomEvent("freshbasket:statechange"));}
function setIds(key,values){store.set(key,[...new Set(values.map(Number))]);sync();}
function addToCart(id,quantity=1){
  const item=product(id);if(!item||item.stock<1){ui.toast("This product is currently out of stock");return false;}
  const list=cart(),existing=list.find(entry=>entry.id===item.id);
  const next=Math.min(item.stock,(existing?.qty||0)+Math.max(1,Number(quantity)||1));
  if(existing)existing.qty=next;else list.push({id:item.id,qty:next});
  saveCart(list);ui.toast(next===item.stock?"Added - maximum available quantity reached":"Added to your basket");renderCurrent();return true;
}
function removeCart(id){saveCart(cart().filter(item=>item.id!==Number(id)));ui.toast("Removed from basket");renderCurrent();}
function changeQuantity(id,delta){
  const item=product(id),list=cart(),entry=list.find(row=>row.id===Number(id));if(!item||!entry)return;
  const next=entry.qty+Number(delta);
  if(next<1){ui.toast("Quantity cannot be below 1");return;}
  if(next>item.stock){ui.toast(`Only ${item.stock} available in stock`);return;}
  entry.qty=next;saveCart(list);renderCurrent();
}
function addWishlist(id){setIds(KEYS.wishlist,[...ids(KEYS.wishlist),id]);ui.toast("Saved to wishlist");renderCurrent();}
function removeWishlist(id){setIds(KEYS.wishlist,ids(KEYS.wishlist).filter(item=>item!==Number(id)));ui.toast("Removed from wishlist");renderCurrent();}
function moveWishlistToCart(id){if(addToCart(id))removeWishlist(id);}
function saveForLater(id){setIds(KEYS.saved,[...ids(KEYS.saved),id]);saveCart(cart().filter(item=>item.id!==Number(id)));ui.toast("Saved for later");renderCurrent();}
function removeSaved(id){setIds(KEYS.saved,ids(KEYS.saved).filter(item=>item!==Number(id)));ui.toast("Removed from saved items");renderCurrent();}
function moveSavedToCart(id){if(addToCart(id))removeSaved(id);}
function addCompare(id){
  const list=ids(KEYS.compare);if(list.includes(Number(id))){ui.toast("Already in comparison");return;}
  if(list.length>=4){ui.toast("You can compare up to 4 products");return;}
  setIds(KEYS.compare,[...list,id]);ui.toast("Added to comparison");renderCurrent();
}
function removeCompare(id){setIds(KEYS.compare,ids(KEYS.compare).filter(item=>item!==Number(id)));ui.toast("Removed from comparison");renderCurrent();}
function totals(){
  const rows=cart().map(entry=>({entry,item:product(entry.id)}));
  const totalMrp=rows.reduce((sum,row)=>sum+row.item.mrp*row.entry.qty,0);
  const subtotal=rows.reduce((sum,row)=>sum+row.item.sellingPrice*row.entry.qty,0);
  const productDiscount=totalMrp-subtotal;
  const fulfilment=store.get(KEYS.fulfilment,"delivery");
  const baseDelivery=fulfilment==="pickup"||subtotal>=499?0:49;
  const handling=rows.length?9:0;
  const code=store.get(KEYS.coupon,"");
  const coupon=COUPONS[code];
  let couponDiscount=0;
  if(coupon&&subtotal>=coupon.minimum){
    if(coupon.type==="flat")couponDiscount=Math.min(coupon.value,subtotal);
    if(coupon.type==="percent")couponDiscount=Math.min(coupon.max||Infinity,Math.round(subtotal*coupon.value/100));
    if(coupon.type==="delivery")couponDiscount=baseDelivery;
  }
  const delivery=Math.max(0,baseDelivery-(coupon?.type==="delivery"?couponDiscount:0));
  const finalTotal=Math.max(0,subtotal-couponDiscount+delivery+handling);
  return {totalMrp,subtotal,productDiscount,couponDiscount,delivery,handling,finalTotal,totalSavings:productDiscount+couponDiscount+(baseDelivery-delivery),code,fulfilment};
}
function applyCoupon(code){
  code=String(code||"").trim().toUpperCase();const coupon=COUPONS[code],summary=totals();
  if(!coupon)return {ok:false,message:"Enter a valid coupon code."};
  if(summary.subtotal<coupon.minimum)return {ok:false,message:`Add ${money(coupon.minimum-summary.subtotal)} more to use ${code}.`};
  store.set(KEYS.coupon,code);renderCart();return {ok:true,message:`${code} applied successfully.`};
}
function clearCoupon(){store.remove(KEYS.coupon);renderCart();ui.toast("Coupon removed");}
function empty(icon,title,text,link="shop.html",label="Start shopping"){return `<div class="shopping-panel shopping-empty"><i class="${icon}"></i><h2>${title}</h2><p>${text}</p><a class="btn btn-primary" href="${link}">${label}</a></div>`;}
function cartItem(row){
  const item=product(row.id);return `<article class="shopping-item">
    <a href="product-details.html?id=${item.id}"><img class="shopping-item-image" src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"></a>
    <div><h3><a href="product-details.html?id=${item.id}">${item.name}</a></h3><p class="shopping-meta">${item.brand} · ${item.weight}</p>
      <div class="shopping-price"><strong>${money(item.sellingPrice)}</strong><span class="mrp">${money(item.mrp)}</span><span class="shopping-saving">Save ${money(item.savings)}</span></div>
      <p class="shopping-stock ${item.stockStatus==="In stock"?"":"out"}"><i class="fa-solid fa-circle-check"></i> ${item.stockStatus} · ${item.stock} available</p>
      <p class="shopping-delivery"><i class="fa-solid fa-truck-fast"></i> ${item.deliveryTime}</p>
      <div class="shopping-actions"><button class="shopping-action danger" data-cart-remove="${item.id}">Remove</button><button class="shopping-action" data-cart-wish="${item.id}">Move to wishlist</button><button class="shopping-action" data-cart-save="${item.id}">Save for later</button></div>
    </div>
    <div class="shopping-qty"><button data-cart-qty="${item.id}" data-delta="-1" aria-label="Decrease ${item.name} quantity">−</button><b>${row.qty}</b><button data-cart-qty="${item.id}" data-delta="1" aria-label="Increase ${item.name} quantity">+</button></div>
  </article>`}
function couponSuggestions(){return Object.entries(COUPONS).map(([code,coupon])=>`<button type="button" class="coupon-suggestion" data-coupon="${code}"><span><code>${code}</code><small>${coupon.description}</small></span><b>${coupon.label}</b></button>`).join("")}
function recommendationCard(item,action="cart"){return `<article class="shopping-card"><a href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"></a><h3>${item.name}</h3><p class="shopping-meta">${item.brand} · ${item.weight}</p><div class="shopping-price"><strong>${money(item.sellingPrice)}</strong><span class="mrp">${money(item.mrp)}</span></div><button class="btn btn-primary" data-${action}-add="${item.id}">${action==="compare"?"Compare":"Add to basket"}</button></article>`}
function renderCart(){
  const host=$("#shopping-cart");if(!host)return;const list=cart();
  if(!list.length){host.innerHTML=empty("fa-solid fa-basket-shopping","Your basket is empty","Add fresh essentials and they will appear here.");$("#cart-recommendations").innerHTML=window.PRODUCTS.filter(item=>item.featured&&item.stock>0).slice(0,4).map(item=>recommendationCard(item)).join("");return;}
  const sum=totals(),coupon=COUPONS[sum.code];
  host.innerHTML=`<div class="shopping-layout"><section class="shopping-panel"><div class="shopping-panel-head"><h2>${list.reduce((n,row)=>n+row.qty,0)} items</h2><a class="btn btn-outline btn-sm" href="shop.html">Continue Shopping</a></div>${list.map(cartItem).join("")}</section>
  <aside class="shopping-panel shopping-summary"><h2>Order summary</h2><h3>Delivery option</h3><div class="fulfilment-options">
    <label class="fulfilment-option"><input type="radio" name="fulfilment" value="delivery" ${sum.fulfilment==="delivery"?"checked":""}><span><b>Home delivery</b><small>Deliver to your saved address</small></span></label>
    <label class="fulfilment-option"><input type="radio" name="fulfilment" value="pickup" ${sum.fulfilment==="pickup"?"checked":""}><span><b>Store pickup</b><small>Collect from Prahlad Nagar</small></span></label></div>
    <h3>Apply coupon</h3><form class="coupon-form" id="coupon-form"><input class="form-control" id="coupon-code" autocomplete="off" placeholder="Coupon code" value="${sum.code}"><button class="btn btn-primary btn-sm">Apply</button>${sum.code?'<button class="btn btn-ghost btn-sm" type="button" id="coupon-clear">Clear</button>':""}</form><p class="coupon-message ${coupon?"success":""}" id="coupon-message">${coupon?`${sum.code} is applied.`:""}</p>
    <div class="coupon-suggestions">${couponSuggestions()}</div>
    <div class="summary-row"><span>Total MRP</span><b>${money(sum.totalMrp)}</b></div><div class="summary-row"><span>Product discount</span><b>− ${money(sum.productDiscount)}</b></div><div class="summary-row"><span>Coupon discount</span><b>− ${money(sum.couponDiscount)}</b></div><div class="summary-row"><span>Delivery charge</span><b>${sum.delivery?money(sum.delivery):"FREE"}</b></div><div class="summary-row"><span>Handling charge</span><b>${money(sum.handling)}</b></div><div class="summary-divider"></div><div class="summary-row total"><span>Final total</span><b>${money(sum.finalTotal)}</b></div><div class="summary-saving">You saved ${money(sum.totalSavings)} on this order</div><a class="btn btn-primary btn-block" href="checkout.html">Proceed to Checkout <i class="fa-solid fa-arrow-right"></i></a>
  </aside></div>`;
}
function renderWishlist(){
  const host=$("#wishlist-content");if(!host)return;const list=ids(KEYS.wishlist).map(product);
  host.innerHTML=list.length?`<div class="shopping-panel-head"><h2>${list.length} saved products</h2><a class="btn btn-outline" href="shop.html">Continue Shopping</a></div><div class="shopping-grid">${list.map(item=>`<article class="shopping-card"><a href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"></a><h3>${item.name}</h3><p class="shopping-meta">${item.brand} · ${item.weight}</p><div class="shopping-price"><strong>${money(item.sellingPrice)}</strong><span class="mrp">${money(item.mrp)}</span></div><p class="shopping-stock">${item.stockStatus}</p><button class="btn btn-primary" data-wish-cart="${item.id}">Move to basket</button><button class="shopping-action danger" data-wish-remove="${item.id}">Remove</button></article>`).join("")}</div>`:empty("fa-regular fa-heart","Your wishlist is empty","Save products you love and find them quickly later.");
}
function renderSaved(){
  const host=$("#saved-content");if(!host)return;const list=ids(KEYS.saved).map(product);
  host.innerHTML=list.length?`<div class="shopping-panel-head"><h2>${list.length} saved for later</h2><a class="btn btn-outline" href="shop.html">Continue Shopping</a></div><div class="shopping-grid">${list.map(item=>`<article class="shopping-card"><a href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"></a><h3>${item.name}</h3><p class="shopping-meta">${item.brand} · ${item.weight}</p><div class="shopping-price"><strong>${money(item.sellingPrice)}</strong><span class="mrp">${money(item.mrp)}</span></div><button class="btn btn-primary" data-saved-cart="${item.id}">Move to basket</button><button class="shopping-action danger" data-saved-remove="${item.id}">Remove</button></article>`).join("")}</div>`:empty("fa-regular fa-bookmark","Nothing saved for later","Items moved from your basket will appear here.");
}
function renderCompare(){
  const host=$("#compare-content");if(!host)return;const list=ids(KEYS.compare).slice(0,4).map(product);
  if(!list.length){host.innerHTML=empty("fa-solid fa-scale-balanced","No products to compare","Add up to four products and compare their value and attributes.","shop.html","Browse products")+`<div class="section-head"><h2>Popular products to compare</h2></div><div class="shopping-grid">${window.PRODUCTS.filter(item=>item.bestseller).slice(0,4).map(item=>recommendationCard(item,"compare")).join("")}</div>`;return;}
  const row=(label,getter)=>`<tr><td>${label}</td>${list.map(item=>`<td>${getter(item)}</td>`).join("")}</tr>`;
  host.innerHTML=`<div class="shopping-panel-head"><h2>Comparing ${list.length} of 4 products</h2><a class="btn btn-outline" href="shop.html">Add products</a></div><div class="shopping-panel compare-wrap"><table class="compare-table"><tbody>
    ${row("Product",item=>`<img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"><h3 class="compare-product-name">${item.name}</h3><button class="shopping-action danger" data-compare-remove="${item.id}">Remove</button>`)}
    ${row("Price",item=>`<strong>${money(item.sellingPrice)}</strong><br><span class="mrp">${money(item.mrp)}</span>`)}
    ${row("Discount",item=>`${item.discount}% · Save ${money(item.savings)}`)}
    ${row("Weight",item=>item.weight)}
    ${row("Rating",item=>`<span class="rating">★ ${item.rating} (${item.reviewCount})</span>`)}
    ${row("Availability",item=>`<span class="shopping-stock">${item.stockStatus}</span>`)}
    ${row("Category",item=>item.category)}
    ${row("Dietary type",item=>item.dietaryType)}
    ${row("Highlights",item=>item.highlights.slice(0,3).join("<br>"))}
    ${row("Action",item=>`<button class="btn btn-primary btn-sm" data-compare-cart="${item.id}">Add to basket</button>`)}
  </tbody></table></div>`;
}
function renderCurrent(){renderCart();renderWishlist();renderSaved();renderCompare();}
document.addEventListener("click",event=>{
  const button=event.target.closest("button");if(!button)return;const d=button.dataset;
  if(d.cartRemove)removeCart(d.cartRemove);if(d.cartWish){addWishlist(d.cartWish);removeCart(d.cartWish);}if(d.cartSave)saveForLater(d.cartSave);
  if(d.cartQty)changeQuantity(d.cartQty,d.delta);if(d.cartAdd)addToCart(d.cartAdd);
  if(d.wishCart)moveWishlistToCart(d.wishCart);if(d.wishRemove)removeWishlist(d.wishRemove);
  if(d.savedCart)moveSavedToCart(d.savedCart);if(d.savedRemove)removeSaved(d.savedRemove);
  if(d.compareAdd)addCompare(d.compareAdd);if(d.compareRemove)removeCompare(d.compareRemove);if(d.compareCart)addToCart(d.compareCart);
  if(d.coupon){const input=$("#coupon-code");if(input)input.value=d.coupon;const result=applyCoupon(d.coupon),message=$("#coupon-message");if(message){message.textContent=result.message;message.className=`coupon-message ${result.ok?"success":"error"}`;}}
  if(button.id==="coupon-clear")clearCoupon();
});
document.addEventListener("change",event=>{if(event.target.name==="fulfilment"){store.set(KEYS.fulfilment,event.target.value);renderCart();}});
document.addEventListener("submit",event=>{if(event.target.id!=="coupon-form")return;event.preventDefault();const result=applyCoupon($("#coupon-code").value),message=$("#coupon-message");if(message){message.textContent=result.message;message.className=`coupon-message ${result.ok?"success":"error"}`;}});
window.addCart=addToCart;window.removeCart=removeCart;window.qty=changeQuantity;
window.toggleWish=id=>ids(KEYS.wishlist).includes(Number(id))?removeWishlist(id):addWishlist(id);
window.saveLater=saveForLater;window.compareProduct=addCompare;
window.FBShopping=Object.freeze({addToCart,removeCart,changeQuantity,addWishlist,removeWishlist,saveForLater,addCompare,applyCoupon,clearCoupon,totals,render:renderCurrent});
if(document.querySelector('[data-shopping-page="cart"]')){
  const cartStyles=document.createElement("link");cartStyles.rel="stylesheet";cartStyles.href="assets/css/cart-premium.css";document.head.appendChild(cartStyles);
  const cartScript=document.createElement("script");cartScript.src="assets/js/cart-premium.js";document.body.appendChild(cartScript);
}
if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",()=>{sync();renderCurrent();});else{sync();renderCurrent();}
})(window,document);
