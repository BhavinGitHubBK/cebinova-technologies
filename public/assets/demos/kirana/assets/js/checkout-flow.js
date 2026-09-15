(function(window,document){
"use strict";
const store=window.FBStorage,ui=window.FBUI,money=ui.money;
const $=(selector,context=document)=>context.querySelector(selector);
const $$=(selector,context=document)=>[...context.querySelectorAll(selector)];
const checkout=$("[data-checkout-page]"),confirmation=$("[data-confirmation-page]");
const pickupPoints=[["Gota","2.4 km"],["Chandlodia","3.8 km"],["Chandkheda","6.1 km"],["Thaltej","4.5 km"],["Satellite","1.2 km"],["Navrangpura","5.0 km"]];
const cart=()=>store.get("fb-cart",[]).map(row=>({id:Number(row.id),qty:Math.max(1,Number(row.qty)||1)})).filter(row=>window.getProductById(row.id));
const product=id=>window.getProductById(id);
const esc=value=>String(value??"").replace(/[&<>"']/g,char=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[char]));
function totals(){
 const rows=cart(),totalMrp=rows.reduce((sum,row)=>sum+product(row.id).mrp*row.qty,0),subtotal=rows.reduce((sum,row)=>sum+product(row.id).sellingPrice*row.qty,0),productDiscount=totalMrp-subtotal,fulfilment=store.get("fb-fulfilment","delivery"),baseDelivery=fulfilment==="pickup"||subtotal>=499?0:49,handling=0,code=store.get("fb-coupon",""),coupons={WELCOME100:[499,100],SAVE10:[799,Math.min(200,Math.round(subtotal*.1))],MONTHLY250:[1499,250]};
 let couponDiscount=code==="FREEDEL"&&subtotal>=199?baseDelivery:(coupons[code]&&subtotal>=coupons[code][0]?coupons[code][1]:0),delivery=code==="FREEDEL"?Math.max(0,baseDelivery-couponDiscount):baseDelivery,finalTotal=Math.max(0,subtotal-couponDiscount+delivery+handling);
 return{totalMrp,subtotal,productDiscount,couponDiscount,delivery,handling,finalTotal,totalSavings:productDiscount+couponDiscount+(baseDelivery-delivery),code};
}
function itemMarkup(row){const item=product(row.id);return `<div class="co-order-item"><img src="${item.image}" alt="${esc(item.name)}" onerror="imageFallback(this)"><div><h3>${esc(item.name)}</h3><small>${esc(item.weight)} × ${row.qty}</small></div><b>${money(item.sellingPrice*row.qty)}</b></div>`;}
function summaryMarkup(){const sum=totals();return `<h2>Order summary</h2><div class="co-order-items">${cart().map(itemMarkup).join("")}</div><div class="co-summary-lines"><div class="co-summary-row"><span>Subtotal</span><b>${money(sum.subtotal)}</b></div><div class="co-summary-row"><span>Product discount</span><b>− ${money(sum.productDiscount)}</b></div><div class="co-summary-row"><span>Coupon ${sum.code?`(${sum.code})`:""}</span><b>− ${money(sum.couponDiscount)}</b></div><div class="co-summary-row"><span>Delivery fee</span><b>${sum.delivery?money(sum.delivery):"FREE"}</b></div><div class="co-summary-row total"><span>Final total</span><b>${money(sum.finalTotal)}</b></div><div class="co-saving">Total savings: ${money(sum.totalSavings)}</div><p class="co-secure"><i class="fa-solid fa-shield-halved"></i> Simulated checkout. No real payment is processed.</p></div>`;}
function field(id,label,required=true,type="text",wide=false,extra=""){return `<div class="co-field ${wide?"wide":""}"><label for="${id}">${label}${required?" *":""}</label><input class="form-control" id="${id}" name="${id}" type="${type}" ${required?"required":""} ${extra}><p class="co-error">Please enter a valid ${label.toLowerCase()}.</p></div>`;}
function renderCheckout(){
 if(!checkout)return;if(!cart().length){checkout.innerHTML=`<div class="container"><section class="co-panel co-empty"><i class="fa-solid fa-basket-shopping"></i><h1>Your cart is empty</h1><p>Add products before starting checkout.</p><a class="btn btn-primary" href="shop.html">Continue Shopping</a></section></div>`;return;}
 const saved=store.get("fb-location",{area:"Satellite",city:"Ahmedabad",pin:"380015"}),today=new Date().toISOString().slice(0,10);
 checkout.innerHTML=`<div class="container"><div class="co-title"><nav class="breadcrumb"><a href="cart.html">Cart</a> / Checkout</nav><span class="eyebrow">Secure simulated checkout</span><h1>Complete your order</h1></div><div class="co-layout"><form class="co-panel" id="co-form" novalidate><div class="co-steps">${["Login / Guest","Delivery type","Address / Pickup","Date & slot","Payment","Review"].map((label,index)=>`<button class="co-step ${index===0?"is-active":""}" type="button" data-go-step="${index}"><span>${index+1}</span>${label}</button>`).join("")}</div>
 <section class="co-stage is-active" data-stage="0"><h2>Login or guest checkout</h2><div class="co-choice-grid"><label class="co-choice"><input type="radio" name="customerMode" value="guest" checked><span><b>Guest checkout</b><small>Continue without creating an account</small></span></label><label class="co-choice"><input type="radio" name="customerMode" value="login"><span><b>Login</b><small>Use your FreshBasket account</small></span></label></div><div class="co-grid" style="margin-top:16px">${field("email","Email address",true,"email")}${field("loginPassword","Password",false,"password","","placeholder=\"Only required for demo login\"")}</div></section>
 <section class="co-stage" data-stage="1"><h2>Delivery or pickup</h2><div class="co-choice-grid"><label class="co-choice"><input type="radio" name="deliveryType" value="delivery" ${store.get("fb-fulfilment","delivery")==="delivery"?"checked":""}><span><b>Home delivery</b><small>Delivered to your address</small></span></label><label class="co-choice"><input type="radio" name="deliveryType" value="pickup" ${store.get("fb-fulfilment","delivery")==="pickup"?"checked":""}><span><b>Store pickup</b><small>Collect from a nearby point</small></span></label></div></section>
 <section class="co-stage" data-stage="2"><div data-address-fields><h2>Home delivery address</h2><div class="co-grid">${field("fullName","Full name")}${field("mobile","Mobile number",true,"tel",false,"inputmode=\"numeric\" maxlength=\"10\" pattern=\"[6-9][0-9]{9}\"")}${field("alternate","Alternate number",false,"tel",false,"inputmode=\"numeric\" maxlength=\"10\"")}${field("house","House or flat number")}${field("building","Building or society")}${field("street","Street")}${field("area","Area",true,"text",false,`value=\"${esc(saved.area)}\"`)}${field("landmark","Landmark",false)}${field("city","City",true,"text",false,`value=\"${esc(saved.city)}\"`)}<div class="co-field"><label for="state">State *</label><select class="form-control" id="state" required><option value="Gujarat">Gujarat</option></select><p class="co-error">Select a state.</p></div>${field("pincode","PIN code",true,"text",false,`inputmode=\"numeric\" maxlength=\"6\" pattern=\"[0-9]{6}\" value=\"${esc(saved.pin)}\"`)}<div class="co-field"><label for="addressType">Address type *</label><select class="form-control" id="addressType" required><option>Home</option><option>Work</option><option>Other</option></select><p class="co-error">Select an address type.</p></div><div class="co-field wide"><label for="instructions">Delivery instructions</label><textarea class="form-control" id="instructions" rows="3"></textarea></div></div></div><div data-pickup-fields hidden><h2>Select pickup point</h2><div class="co-pickups">${pickupPoints.map(([name,distance],index)=>`<label class="co-pickup"><input type="radio" name="pickupPoint" value="${name}" ${index===4?"checked":""}> <b>${name}</b><small>${distance} away</small></label>`).join("")}</div><h3>Pickup contact</h3><div class="co-grid">${field("pickupName","Full name")}${field("pickupMobile","Mobile number",true,"tel",false,"inputmode=\"numeric\" maxlength=\"10\" pattern=\"[6-9][0-9]{9}\"")}</div></div></section>
 <section class="co-stage" data-stage="3"><h2>Date and time slot</h2><div class="co-grid"><div class="co-field"><label for="orderDate">Delivery or pickup date *</label><input class="form-control" id="orderDate" type="date" min="${today}" value="${today}" required><p class="co-error">Choose a valid date.</p></div><div class="co-field"><label for="orderSlot">Time slot *</label><select class="form-control" id="orderSlot" required><option value="">Choose a slot</option><option>8–10 AM</option><option>10 AM–12 PM</option><option>2–4 PM</option><option>7–9 PM</option></select><p class="co-error">Choose a time slot.</p></div></div></section>
 <section class="co-stage" data-stage="4"><h2>Payment</h2><p>All methods are simulated. No real payment gateway is connected.</p><div class="co-choice-grid">${["Cash on Delivery","UPI","Credit Card","Debit Card","Net Banking","Wallet"].map((method,index)=>`<label class="co-choice"><input type="radio" name="payment" value="${method}" ${index===0?"checked":""}><span><b>${method}</b><small>Demo payment option</small></span></label>`).join("")}</div><p class="co-error" data-payment-error>Please select a payment method.</p></section>
 <section class="co-stage" data-stage="5"><h2>Review your order</h2><div id="co-review"></div><label class="co-choice"><input id="terms" type="checkbox" required><span><b>I accept the terms and confirm this demo order.</b><small>No real payment will be processed.</small></span></label><p class="co-error" data-terms-error>Please accept the terms to place your order.</p></section>
 <div class="co-actions"><button class="btn btn-outline" type="button" data-back hidden>Back</button><button class="btn btn-primary" type="button" data-next>Continue</button><button class="btn btn-primary" type="submit" data-place hidden>Place Order</button></div></form><aside class="co-panel co-summary" id="co-summary">${summaryMarkup()}</aside></div></div>`;
 let step=0;const form=$("#co-form");
 function type(){return $('input[name="deliveryType"]:checked',form)?.value||"delivery";}
 function toggleAddress(){const delivery=type()==="delivery";$("[data-address-fields]").hidden=!delivery;$("[data-pickup-fields]").hidden=delivery;store.set("fb-fulfilment",delivery?"delivery":"pickup");$("#co-summary").innerHTML=summaryMarkup();}
 function fieldsFor(index){const stage=$(`[data-stage="${index}"]`,form);return $$("input[required],select[required],textarea[required]",stage).filter(input=>{const hidden=input.closest("[hidden]");return !hidden;});}
 function validate(index){let valid=true;fieldsFor(index).forEach(input=>{const okay=input.checkValidity();input.closest(".co-field")?.classList.toggle("has-error",!okay);valid=okay&&valid;});if(index===4&&!$('input[name="payment"]:checked',form)){valid=false;$("[data-payment-error]").style.display="block";}if(index===5&&!$("#terms").checked){valid=false;$("[data-terms-error]").style.display="block";}return valid;}
 function review(){const fd=new FormData(form),delivery=type()==="delivery";$("#co-review").innerHTML=`<div class="co-review-block"><h3>${delivery?"Home delivery":"Store pickup"}</h3><p>${delivery?`${esc($("#house").value)}, ${esc($("#building").value)}, ${esc($("#street").value)}, ${esc($("#area").value)}, ${esc($("#city").value)} ${esc($("#pincode").value)}`:`${esc(fd.get("pickupPoint"))} pickup point`}</p></div><div class="co-review-block"><h3>Date and slot</h3><p>${esc($("#orderDate").value)} · ${esc($("#orderSlot").value)}</p></div><div class="co-review-block"><h3>Payment</h3><p>${esc(fd.get("payment"))} · simulated payment</p></div>`;}
 function show(index){step=Math.max(0,Math.min(5,index));$$("[data-stage]",form).forEach(node=>node.classList.toggle("is-active",Number(node.dataset.stage)===step));$$("[data-go-step]",form).forEach((node,i)=>{node.classList.toggle("is-active",i===step);node.classList.toggle("is-complete",i<step);});$("[data-back]").hidden=step===0;$("[data-next]").hidden=step===5;$("[data-place]").hidden=step!==5;if(step===5)review();}
 form.addEventListener("change",event=>{if(event.target.name==="deliveryType")toggleAddress();if(event.target.name==="payment")$("[data-payment-error]").style.display="none";if(event.target.id==="terms")$("[data-terms-error]").style.display="none";});
 form.addEventListener("input",event=>event.target.closest(".co-field")?.classList.remove("has-error"));
 form.addEventListener("click",event=>{const button=event.target.closest("button");if(!button)return;if(button.dataset.next!==undefined&&validate(step))show(step+1);if(button.dataset.back!==undefined)show(step-1);if(button.dataset.goStep!==undefined&&Number(button.dataset.goStep)<step)show(Number(button.dataset.goStep));});
 form.addEventListener("submit",event=>{event.preventDefault();if(!validate(5))return;const fd=new FormData(form),delivery=type()==="delivery",sum=totals(),now=new Date(),order={number:`FBM2026${String(Date.now()).slice(-6)}`,createdAt:now.toISOString(),displayDate:now.toLocaleString("en-IN"),deliveryType:delivery?"Home delivery":"Pickup",fulfilment:delivery?"delivery":"pickup",customer:{mode:fd.get("customerMode"),email:$("#email").value,name:delivery?$("#fullName").value:$("#pickupName").value,mobile:delivery?$("#mobile").value:$("#pickupMobile").value},address:delivery?{fullName:$("#fullName").value,mobile:$("#mobile").value,alternate:$("#alternate").value,house:$("#house").value,building:$("#building").value,street:$("#street").value,area:$("#area").value,landmark:$("#landmark").value,city:$("#city").value,state:$("#state").value,pin:$("#pincode").value,type:$("#addressType").value,instructions:$("#instructions").value}:null,pickup:delivery?null:{point:fd.get("pickupPoint"),distance:pickupPoints.find(row=>row[0]===fd.get("pickupPoint"))?.[1]||"",contact:$("#pickupName").value,mobile:$("#pickupMobile").value},date:$("#orderDate").value,slot:$("#orderSlot").value,paymentMethod:fd.get("payment"),paymentStatus:fd.get("payment")==="Cash on Delivery"?"Pay on delivery":"Simulated payment successful",products:cart().map(row=>({...row,name:product(row.id).name,image:product(row.id).image,weight:product(row.id).weight,price:product(row.id).sellingPrice,mrp:product(row.id).mrp})),totals:sum};
 try{store.set("fb-latest-order",order);store.set("fb-last-order",order);const history=store.get("fb-order-history",[]);store.set("fb-order-history",[order,...history.filter(item=>item.number!==order.number)]);store.set("fb-order",order.number);store.set("fb-cart",[]);window.FreshBasketHeader?.syncCounts();location.href="order-confirmation.html";}catch(error){ui.toast("We could not save your order. Your cart has not been cleared.");}});
 $$("select.form-control",form).forEach(enhanceCheckoutSelect);
 enhanceCheckoutDate($("#orderDate",form));
 toggleAddress();show(0);
}
function enhanceCheckoutDate(input){
 if(!input||input.dataset.coDateEnhanced==="1")return;
 input.dataset.coDateEnhanced="1";
 const field=input.closest(".co-field");
 const min=input.min?new Date(input.min+"T00:00:00"):new Date();
 min.setHours(0,0,0,0);
 const pad=n=>String(n).padStart(2,"0");
 const toValue=date=>`${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`;
 const toLabel=value=>{
  if(!value)return"Choose a date";
  const [y,m,d]=value.split("-").map(Number);
  return new Date(y,m-1,d).toLocaleDateString("en-IN",{day:"2-digit",month:"short",year:"numeric"});
 };
 const widget=document.createElement("div");
 widget.className="co-date";
 input.parentNode.insertBefore(widget,input);
 widget.appendChild(input);
 input.classList.add("co-date-native");
 input.tabIndex=-1;
 const trigger=document.createElement("button");
 trigger.type="button";
 trigger.className="co-date-trigger";
 trigger.setAttribute("aria-haspopup","dialog");
 trigger.setAttribute("aria-expanded","false");
 trigger.innerHTML=`<span class="co-date-icon"><i class="fa-regular fa-calendar"></i></span><span data-co-date-label>${esc(toLabel(input.value))}</span><i class="fa-solid fa-chevron-down co-date-chevron" aria-hidden="true"></i>`;
 const panel=document.createElement("div");
 panel.className="co-date-panel";
 panel.hidden=true;
 panel.setAttribute("role","dialog");
 panel.setAttribute("aria-label","Choose delivery date");
 widget.append(trigger,panel);
 const label=$("[data-co-date-label]",trigger);
 let view=input.value?new Date(input.value+"T00:00:00"):new Date(min);
 const close=()=>{widget.classList.remove("is-open");trigger.setAttribute("aria-expanded","false");panel.hidden=true};
 const open=()=>{
  $$(".co-select.is-open,.co-date.is-open").forEach(node=>{
   if(node===widget)return;
   node.classList.remove("is-open");
   const menu=$(".co-select-menu,.co-date-panel",node);
   if(menu)menu.hidden=true;
   $(".co-select-trigger,.co-date-trigger",node)?.setAttribute("aria-expanded","false");
  });
  view=input.value?new Date(input.value+"T00:00:00"):new Date(min);
  render();
  widget.classList.add("is-open");
  trigger.setAttribute("aria-expanded","true");
  panel.hidden=false;
 };
 const choose=value=>{
  input.value=value;
  label.textContent=toLabel(value);
  widget.classList.add("has-value");
  field?.classList.remove("has-error");
  input.dispatchEvent(new Event("input",{bubbles:true}));
  input.dispatchEvent(new Event("change",{bubbles:true}));
  close();
  trigger.focus();
 };
 const render=()=>{
  const year=view.getFullYear(),month=view.getMonth();
  const first=new Date(year,month,1);
  const start=new Date(first);
  start.setDate(1-first.getDay());
  const monthLabel=view.toLocaleDateString("en-IN",{month:"long",year:"numeric"});
  let days="";
  for(let i=0;i<42;i++){
   const day=new Date(start);
   day.setDate(start.getDate()+i);
   const value=toValue(day);
   const inMonth=day.getMonth()===month;
   const disabled=day<min;
   const selected=input.value===value;
   const today=toValue(new Date())===value;
   days+=`<button type="button" class="co-date-day${inMonth?"":" is-muted"}${selected?" is-selected":""}${today?" is-today":""}" data-date="${value}" ${disabled?"disabled":""}>${day.getDate()}</button>`;
  }
  panel.innerHTML=`<div class="co-date-head"><button type="button" class="co-date-nav" data-nav="-1" aria-label="Previous month"><i class="fa-solid fa-chevron-left"></i></button><b>${esc(monthLabel)}</b><button type="button" class="co-date-nav" data-nav="1" aria-label="Next month"><i class="fa-solid fa-chevron-right"></i></button></div><div class="co-date-week"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="co-date-grid">${days}</div><div class="co-date-foot"><button type="button" data-today>Today</button><button type="button" data-clear>Clear</button></div>`;
  $$("[data-nav]",panel).forEach(btn=>btn.onclick=()=>{view=new Date(year,month+Number(btn.dataset.nav),1);render();});
  $$(".co-date-day:not([disabled])",panel).forEach(btn=>btn.onclick=()=>choose(btn.dataset.date));
  $("[data-today]",panel).onclick=()=>{
   const today=new Date();today.setHours(0,0,0,0);
   if(today<min){ui.toast("Earliest available date is already selected.");return;}
   choose(toValue(today));
  };
  $("[data-clear]",panel).onclick=()=>{
   input.value="";
   label.textContent="Choose a date";
   widget.classList.remove("has-value");
   input.dispatchEvent(new Event("input",{bubbles:true}));
   input.dispatchEvent(new Event("change",{bubbles:true}));
   close();
  };
 };
 trigger.onclick=()=>widget.classList.contains("is-open")?close():open();
 document.addEventListener("pointerdown",event=>{if(!widget.contains(event.target))close();});
 document.addEventListener("keydown",event=>{if(event.key==="Escape"&&widget.classList.contains("is-open")){close();trigger.focus();}});
 if(input.value)widget.classList.add("has-value");
}
function enhanceCheckoutSelect(select){
 if(!select||select.dataset.coEnhanced==="1")return;
 select.dataset.coEnhanced="1";
 const icons={
  "Choose a slot":"fa-clock",
  "8–10 AM":"fa-sun",
  "10 AM–12 PM":"fa-cloud-sun",
  "2–4 PM":"fa-mug-hot",
  "7–9 PM":"fa-moon",
  Home:"fa-house",
  Work:"fa-briefcase",
  Other:"fa-location-dot",
  Gujarat:"fa-map"
 };
 const field=select.closest(".co-field");
 const widget=document.createElement("div");
 widget.className="co-select";
 select.parentNode.insertBefore(widget,select);
 widget.appendChild(select);
 select.classList.add("co-select-native");
 select.tabIndex=-1;
 select.setAttribute("aria-hidden","true");
 const selected=select.options[select.selectedIndex];
 const trigger=document.createElement("button");
 trigger.type="button";
 trigger.className="co-select-trigger";
 trigger.setAttribute("aria-haspopup","listbox");
 trigger.setAttribute("aria-expanded","false");
 trigger.innerHTML=`<span class="co-select-icon"><i class="fa-solid ${icons[selected?.text]||"fa-chevron-down"}"></i></span><span data-co-label>${esc(selected?.text||"Choose")}</span><i class="fa-solid fa-chevron-down co-select-chevron" aria-hidden="true"></i>`;
 const menu=document.createElement("div");
 menu.className="co-select-menu";
 menu.hidden=true;
 menu.setAttribute("role","listbox");
 [...select.options].forEach(option=>{
  if(!option.value&&option.text==="Choose a slot"){
   // keep placeholder in trigger only
  }
  const item=document.createElement("button");
  item.type="button";
  item.className="co-select-option";
  item.dataset.value=option.value;
  item.setAttribute("role","option");
  item.setAttribute("aria-selected",String(option.selected));
  if(!option.value)item.dataset.placeholder="1";
  item.innerHTML=`<i class="fa-solid ${icons[option.text]||"fa-circle"}" aria-hidden="true"></i><span>${esc(option.text)}</span><i class="fa-solid fa-check" aria-hidden="true"></i>`;
  menu.appendChild(item);
 });
 widget.append(trigger,menu);
 const label=trigger.querySelector("[data-co-label]");
 const icon=$(".co-select-icon i",trigger);
 const items=$$(".co-select-option",menu);
 const sync=()=>{
  const option=select.options[select.selectedIndex];
  label.textContent=option?.text||"Choose";
  icon.className=`fa-solid ${icons[option?.text]||"fa-chevron-down"}`;
  widget.classList.toggle("has-value",!!select.value);
  items.forEach(node=>node.setAttribute("aria-selected",String(node.dataset.value===select.value)));
  field?.classList.toggle("has-error",false);
 };
 const close=()=>{widget.classList.remove("is-open");trigger.setAttribute("aria-expanded","false");menu.hidden=true};
 const open=()=>{
  $$(".co-select.is-open").forEach(node=>{if(node!==widget){node.classList.remove("is-open");$(".co-select-menu",node).hidden=true;$(".co-select-trigger",node)?.setAttribute("aria-expanded","false");}});
  widget.classList.add("is-open");
  trigger.setAttribute("aria-expanded","true");
  menu.hidden=false;
  (items.find(item=>item.getAttribute("aria-selected")==="true")||items[0])?.focus();
 };
 const choose=item=>{
  select.value=item.dataset.value;
  sync();
  select.dispatchEvent(new Event("input",{bubbles:true}));
  select.dispatchEvent(new Event("change",{bubbles:true}));
  close();
  trigger.focus();
 };
 trigger.onclick=()=>widget.classList.contains("is-open")?close():open();
 items.forEach((item,index)=>{
  item.onclick=()=>choose(item);
  item.onkeydown=event=>{
   if(event.key==="ArrowDown"){event.preventDefault();items[(index+1)%items.length].focus();}
   else if(event.key==="ArrowUp"){event.preventDefault();items[(index-1+items.length)%items.length].focus();}
   else if(event.key==="Enter"||event.key===" "){event.preventDefault();choose(item);}
   else if(event.key==="Escape"){close();trigger.focus();}
  };
 });
 document.addEventListener("pointerdown",event=>{if(!widget.contains(event.target))close();});
 sync();
}
function renderConfirmation(){
 if(!confirmation)return;const order=store.get("fb-latest-order",null);if(!order){confirmation.innerHTML=`<div class="container"><section class="co-panel co-empty"><i class="fa-regular fa-file-lines"></i><h1>No recent order found</h1><p>Complete checkout to see your order confirmation.</p><a class="btn btn-primary" href="shop.html">Continue Shopping</a></section></div>`;return;}
 const destination=order.fulfilment==="pickup"?`${order.pickup.point} pickup point · ${order.pickup.distance}`:`${order.address.house}, ${order.address.building}, ${order.address.street}, ${order.address.area}, ${order.address.city} ${order.address.pin}`;
 const invoiceRows=(order.products||[]).map(item=>`<tr><td><b>${esc(item.name)}</b><small>${esc(item.weight)}</small></td><td>${item.qty}</td><td>${money(item.price)}</td><td>${money(item.price*item.qty)}</td></tr>`).join("");
 const t=order.totals||{};
 confirmation.innerHTML=`<div class="container"><article class="oc-card no-print"><header class="oc-hero"><div class="oc-success"><i class="fa-solid fa-check"></i></div><span class="eyebrow">Order confirmed</span><h1>Thank you for your order!</h1><p>Your order <b>${esc(order.number)}</b> has been saved successfully.</p></header><div class="oc-body"><div class="oc-meta"><div><small>Order number</small><b>${esc(order.number)}</b></div><div><small>Order date</small><b>${esc(order.displayDate)}</b></div><div><small>Type</small><b>${esc(order.deliveryType)}</b></div><div><small>Address / pickup</small><b>${esc(destination)}</b></div><div><small>Date and slot</small><b>${esc(order.date)} · ${esc(order.slot)}</b></div><div><small>Payment</small><b>${esc(order.paymentMethod)}</b></div></div><h2>Ordered products</h2><div class="oc-items">${order.products.map(item=>`<div class="oc-item"><img src="${item.image}" alt="${esc(item.name)}" onerror="imageFallback(this)"><div><b>${esc(item.name)}</b><small>${esc(item.weight)} × ${item.qty}</small></div><b>${money(item.price*item.qty)}</b></div>`).join("")}</div><div class="oc-total"><span>Total</span><b>${money(t.finalTotal)}</b></div><div class="co-saving">You saved ${money(t.totalSavings)} on this order</div><div class="oc-actions"><a class="btn btn-primary" href="track-order.html?order=${encodeURIComponent(order.number)}">Track Order</a><a class="btn btn-outline" href="order-details.html?order=${encodeURIComponent(order.number)}">View Order</a><button class="btn btn-outline" type="button" data-download-invoice><i class="fa-solid fa-file-invoice"></i> Download Invoice</button><a class="btn btn-outline" href="shop.html">Continue Shopping</a></div></div></article>
 <section class="oc-invoice" id="ocInvoice" aria-label="Order invoice"><div class="oc-invoice-sheet"><header class="oc-invoice-head"><div><b>FreshBasket</b><small>Mart · Tax Invoice</small></div><div class="oc-invoice-meta"><span>Invoice / Order</span><strong>${esc(order.number)}</strong><span>${esc(order.displayDate)}</span></div></header><div class="oc-invoice-grid"><div><small>Bill / Ship to</small><b>${esc(order.customer?.name||"Customer")}</b><span>${esc(order.customer?.mobile||"")}</span><span>${esc(destination)}</span></div><div><small>Delivery details</small><b>${esc(order.deliveryType)}</b><span>${esc(order.date)} · ${esc(order.slot)}</span><span>${esc(order.paymentMethod)}</span></div></div><table class="oc-invoice-table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Amount</th></tr></thead><tbody>${invoiceRows}</tbody></table><div class="oc-invoice-totals"><div><span>Subtotal</span><b>${money(t.subtotal||0)}</b></div><div><span>Product discount</span><b>− ${money(t.productDiscount||0)}</b></div><div><span>Coupon</span><b>− ${money(t.couponDiscount||0)}</b></div><div><span>Delivery</span><b>${t.delivery?money(t.delivery):"FREE"}</b></div><div class="oc-invoice-grand"><span>Grand total</span><b>${money(t.finalTotal||0)}</b></div></div><footer class="oc-invoice-foot"><p>You saved ${money(t.totalSavings||0)} on this order.</p><small>Simulated invoice · FreshBasket Mart · No real payment processed</small></footer></div></section></div>`;
 $("[data-download-invoice]").addEventListener("click",()=>{
  const prev=document.title;
  document.title=`FreshBasket Invoice ${order.number}`;
  document.body.classList.add("oc-printing");
  window.print();
  setTimeout(()=>{document.body.classList.remove("oc-printing");document.title=prev;},400);
 });
}
renderCheckout();renderConfirmation();
})(window,document);
