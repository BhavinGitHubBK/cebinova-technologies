(function(window,document){
"use strict";
const $=(selector,context=document)=>context.querySelector(selector);
const $$=(selector,context=document)=>[...context.querySelectorAll(selector)];
const ui=window.FBUI,store=window.FBStorage,money=ui.money;
const root=$("[data-catalog-page]");if(!root)return;
const mode=root.dataset.catalogPage;
const slug=value=>String(value||"").toLowerCase().replace(/&/g,"and").replace(/[^a-z0-9]+/g,"-").replace(/(^-|-$)/g,"");
const params=new URLSearchParams(location.search);
const categories=window.CATEGORY_METADATA||[];
const categoryAliases={grocery:"rice-atta-and-grains",dairy:"dairy-and-bakery","packaged-food":"snacks-and-namkeen","home-care":"household-cleaning",appliances:"small-appliances"};
const categoryFrom=value=>{const requested=slug(value),resolved=categoryAliases[requested]||requested;return categories.find(category=>category.slug===resolved||category.name.toLowerCase()===String(value||"").toLowerCase());};
const product=id=>window.getProductById?window.getProductById(id):window.PRODUCTS.find(item=>item.id===Number(id));
const state={categories:[],subcategories:[],brands:[],minPrice:0,maxPrice:5000,discount:0,rating:0,availability:false,dietary:"",pack:"",sort:"relevance",view:"grid",limit:20};
let baseProducts=[...window.PRODUCTS],searchTerm=params.get("q")||"",pageCategory=null;
let autoLoadObserver=null,autoLoadBusy=false;
function setupBase(){
  if(mode==="category"){pageCategory=categoryFrom(params.get("category")||"");if(pageCategory)baseProducts=window.PRODUCTS.filter(item=>item.categorySlug===pageCategory.slug);const sub=params.get("subcategory");if(sub)state.subcategories=[slug(sub)];}
  if(mode==="shop"&&params.get("brand")){const requestedBrand=slug(params.get("brand"));baseProducts=window.PRODUCTS.filter(item=>slug(item.brand)===requestedBrand);state.brands=[requestedBrand];}
  if(mode==="search")baseProducts=window.searchProducts(searchTerm);
  if(mode==="offers")baseProducts=window.getDealProducts().sort((a,b)=>b.discount-a.discount);
  if(mode==="new")baseProducts=window.getNewArrivalProducts();
  if(mode==="best")baseProducts=window.getBestSellerProducts();
}
function packGroup(item){const weight=item.weight.toLowerCase(),number=parseFloat(weight)||1;if(weight.includes("kg"))return number>=5?"bulk":"medium";if(weight.includes("l"))return number>=2?"bulk":"medium";if(weight.includes("g"))return number<=500?"small":"medium";return "small";}
function card(item){
  const entry=store.get("fb-cart",[]).find(row=>Number(row.id)===item.id),wished=store.get("fb-wish",[]).map(Number).includes(item.id);
  return `<article class="catalog-card" data-catalog-product="${item.id}"><a class="catalog-card-image" href="product-details.html?id=${item.id}"><img src="${item.image}" alt="${item.imageAlt||item.name}" loading="lazy" onerror="imageFallback(this)"></a><span class="catalog-discount">${item.discount}% OFF</span><button class="catalog-wish ${wished?"is-active":""}" type="button" data-list-wish="${item.id}" aria-label="Wishlist ${item.name}"><i class="${wished?"fa-solid":"fa-regular"} fa-heart"></i></button>
  <div class="catalog-meta">${item.brand} · ${item.weight}</div><a href="product-details.html?id=${item.id}"><h3>${item.name}</h3></a><div class="catalog-rating">★ ${item.rating} (${item.reviewCount})</div><div class="catalog-price"><strong>${money(item.sellingPrice)}</strong><del>MRP ${money(item.mrp)}</del></div><div class="catalog-save">You Save ${money(item.savings)}</div><div class="catalog-delivery"><i class="fa-solid fa-bolt"></i> ${item.deliveryTime}</div>
  <div class="catalog-card-actions">${entry?`<div class="catalog-qty"><button type="button" data-list-qty="${item.id}" data-delta="-1">−</button><b>${entry.qty}</b><button type="button" data-list-qty="${item.id}" data-delta="1">+</button></div>`:`<button class="btn btn-primary" type="button" data-list-add="${item.id}"><i class="fa-solid fa-basket-shopping"></i> Add</button><button class="catalog-quick" type="button" data-list-quick="${item.id}" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button>`}</div></article>`;
}
function filterData(){
  let list=[...baseProducts];
  if(state.categories.length)list=list.filter(item=>state.categories.includes(item.categorySlug));
  if(state.subcategories.length)list=list.filter(item=>state.subcategories.includes(item.subcategorySlug));
  if(state.brands.length)list=list.filter(item=>state.brands.includes(slug(item.brand)));
  list=list.filter(item=>item.sellingPrice>=state.minPrice&&item.sellingPrice<=state.maxPrice&&item.discount>=state.discount&&item.rating>=state.rating);
  if(state.availability)list=list.filter(item=>item.stock>0);
  if(state.dietary)list=list.filter(item=>slug(item.dietaryType)===state.dietary);
  if(state.pack)list=list.filter(item=>packGroup(item)===state.pack);
  if(state.sort==="popularity")list.sort((a,b)=>b.reviewCount-a.reviewCount);
  if(state.sort==="price-asc")list.sort((a,b)=>a.sellingPrice-b.sellingPrice);
  if(state.sort==="price-desc")list.sort((a,b)=>b.sellingPrice-a.sellingPrice);
  if(state.sort==="discount")list.sort((a,b)=>b.discount-a.discount);
  if(state.sort==="rating")list.sort((a,b)=>b.rating-a.rating);
  if(state.sort==="newest")list.sort((a,b)=>Number(b.newArrival)-Number(a.newArrival)||b.id-a.id);
  if(state.sort==="relevance"&&(mode==="best"||mode==="offers"))list.sort((a,b)=>Number(b.bestseller)-Number(a.bestseller)||b.discount-a.discount);
  return list;
}
function values(key){return [...new Map(baseProducts.map(item=>[slug(item[key]),{slug:slug(item[key]),name:item[key]}])).values()].filter(item=>item.slug);}
function filterMarkup(){
  const availableCategories=mode==="category"?[]:categories.filter(category=>baseProducts.some(item=>item.categorySlug===category.slug));
  const subcategories=values("subcategory"),brands=values("brand"),dietaries=[...new Set(baseProducts.map(item=>item.dietaryType))].filter(Boolean);
  const checks=(name,items)=>items.map(item=>`<label class="catalog-filter-option"><input type="checkbox" data-filter="${name}" value="${item.slug}"> ${item.name}</label>`).join("");
  return `${availableCategories.length?`<div class="catalog-filter-group"><h3>Category</h3>${checks("categories",availableCategories)}</div>`:""}<div class="catalog-filter-group"><h3>Subcategory</h3>${checks("subcategories",subcategories.slice(0,12))}</div><div class="catalog-filter-group"><h3>Brand</h3>${checks("brands",brands.slice(0,14))}</div>
    <div class="catalog-filter-group"><h3>Price</h3><div class="catalog-price-row"><input type="number" data-price="min" min="0" value="0" aria-label="Minimum price"><input type="number" data-price="max" min="0" value="5000" aria-label="Maximum price"></div></div>
    <div class="catalog-filter-group"><h3>Discount</h3>${[10,15,20,25].map(value=>`<label class="catalog-filter-option"><input type="radio" name="discount" data-single-filter="discount" value="${value}"> ${value}% and above</label>`).join("")}</div>
    <div class="catalog-filter-group"><h3>Rating</h3>${[4,4.5].map(value=>`<label class="catalog-filter-option"><input type="radio" name="rating" data-single-filter="rating" value="${value}"> ${value}★ and above</label>`).join("")}</div>
    <div class="catalog-filter-group"><h3>Availability</h3><label class="catalog-filter-option"><input type="checkbox" data-availability> In stock only</label></div>
    <div class="catalog-filter-group"><h3>Dietary preference</h3><select class="form-control" data-select-filter="dietary"><option value="">All</option>${dietaries.map(value=>`<option value="${slug(value)}">${value}</option>`).join("")}</select></div>
    <div class="catalog-filter-group"><h3>Pack size</h3><select class="form-control" data-select-filter="pack"><option value="">All sizes</option><option value="small">Small pack</option><option value="medium">Regular pack</option><option value="bulk">Bulk pack</option></select></div>`;
}
function renderFilters(){const html=filterMarkup();$("#catalog-filters").innerHTML=html;$("#catalog-sheet-filters").innerHTML=html;}
function readFilters(context=document){
  ["categories","subcategories","brands"].forEach(key=>{state[key]=$$(`[data-filter="${key}"]:checked`,context).map(input=>input.value);});
  state.minPrice=Number($('[data-price="min"]',context)?.value||0);state.maxPrice=Number($('[data-price="max"]',context)?.value||5000);
  state.discount=Number($('[data-single-filter="discount"]:checked',context)?.value||0);state.rating=Number($('[data-single-filter="rating"]:checked',context)?.value||0);
  state.availability=Boolean($("[data-availability]",context)?.checked);state.dietary=$('[data-select-filter="dietary"]',context)?.value||"";state.pack=$('[data-select-filter="pack"]',context)?.value||"";state.limit=20;
}
function syncFilterInputs(){
  ["#catalog-filters","#catalog-sheet-filters"].forEach(selector=>{const context=$(selector);if(!context)return;["categories","subcategories","brands"].forEach(key=>$$(`[data-filter="${key}"]`,context).forEach(input=>input.checked=state[key].includes(input.value)));const set=(selector,value)=>{const node=$(selector,context);if(node)node.value=value};set('[data-price="min"]',state.minPrice);set('[data-price="max"]',state.maxPrice);$$('[data-single-filter="discount"]',context).forEach(input=>input.checked=Number(input.value)===state.discount);$$('[data-single-filter="rating"]',context).forEach(input=>input.checked=Number(input.value)===state.rating);const stock=$("[data-availability]",context);if(stock)stock.checked=state.availability;set('[data-select-filter="dietary"]',state.dietary);set('[data-select-filter="pack"]',state.pack);});
}
function activeChips(){
  const labels=[];["categories","subcategories","brands"].forEach(key=>state[key].forEach(value=>labels.push({key,value,label:value.replace(/-/g," ")})));
  if(state.minPrice>0)labels.push({key:"minPrice",label:`Min ${money(state.minPrice)}`});if(state.maxPrice<5000)labels.push({key:"maxPrice",label:`Max ${money(state.maxPrice)}`});if(state.discount)labels.push({key:"discount",label:`${state.discount}%+ off`});if(state.rating)labels.push({key:"rating",label:`${state.rating}★+`});if(state.availability)labels.push({key:"availability",label:"In stock"});if(state.dietary)labels.push({key:"dietary",label:state.dietary.replace(/-/g," ")});if(state.pack)labels.push({key:"pack",label:`${state.pack} pack`});
  $("#catalog-active").innerHTML=labels.map(item=>`<button class="catalog-filter-chip" type="button" data-remove-filter="${item.key}" data-value="${item.value||""}">${item.label} ×</button>`).join("")+(labels.length?'<button class="catalog-clear" type="button" data-clear-filters>Clear all</button>':"");
}
function render(){
  const list=filterData(),visible=list.slice(0,state.limit),grid=$("#catalog-grid");grid.classList.toggle("is-list",state.view==="list");grid.innerHTML=visible.length?visible.map(card).join(""):`<div class="catalog-empty"><i class="fa-solid fa-magnifying-glass"></i><h2>No products found</h2><p>Try another search or remove some filters.</p><button class="btn btn-primary" type="button" data-clear-filters>Clear filters</button></div>`;
  $("#catalog-count").textContent=`${list.length} products`;$("#catalog-load").innerHTML=visible.length<list.length?`<div class="catalog-auto-status" role="status"><i class="fa-solid fa-spinner" aria-hidden="true"></i><span>Loading more products automatically</span></div><button class="btn btn-outline catalog-load-fallback" type="button" data-load-more>Load More (${list.length-visible.length} remaining)</button>`:"";activeChips();observeAutoLoad();
}
function observeAutoLoad(){
  const target=$("#catalog-load"),button=$("[data-load-more]",target);
  if(!target||!button)return;
  if(!("IntersectionObserver" in window)){button.classList.remove("catalog-load-fallback");return;}
  if(!autoLoadObserver)autoLoadObserver=new IntersectionObserver(entries=>{
    if(!entries.some(entry=>entry.isIntersecting)||autoLoadBusy)return;
    const more=$("[data-load-more]",target);if(!more)return;
    autoLoadBusy=true;state.limit+=20;render();
    requestAnimationFrame(()=>{autoLoadBusy=false;observeAutoLoad();});
  },{rootMargin:"0px 0px 500px 0px",threshold:0.01});
  autoLoadObserver.disconnect();
  requestAnimationFrame(()=>autoLoadObserver.observe(target));
}
function clearFilters(){Object.assign(state,{categories:[],subcategories:[],brands:[],minPrice:0,maxPrice:5000,discount:0,rating:0,availability:false,dietary:"",pack:"",limit:20});syncFilterInputs();render();}
function removeFilter(key,value){if(Array.isArray(state[key]))state[key]=state[key].filter(item=>item!==value);else state[key]=key==="maxPrice"?5000:key==="availability"?false:key==="dietary"||key==="pack"?"":0;syncFilterInputs();render();}
function quickView(id){const item=product(id);const modal=ui.openModal({title:item.name,content:`<div class="catalog-quick-modal"><img src="${item.image}" alt="${item.imageAlt||item.name}" onerror="imageFallback(this)"><div><span class="eyebrow">${item.brand}</span><p>${item.weight} · <span class="rating">★ ${item.rating} (${item.reviewCount})</span></p><div class="catalog-price"><strong style="font-size:1.4rem">${money(item.sellingPrice)}</strong><del>MRP ${money(item.mrp)}</del></div><p class="catalog-save">You Save ${money(item.savings)}</p><p>${item.description}</p><button class="btn btn-primary btn-block" type="button" data-quick-add="${item.id}">Add to basket</button></div></div>`});$("[data-quick-add]",modal).onclick=()=>{window.addCart(item.id);ui.closeModal(modal);render();};}
function openSheet(type){const sheet=$("#catalog-filter-sheet");sheet.dataset.sheet=type;$(".catalog-sheet-title").textContent=type==="sort"?"Sort products":"Filter products";$("#catalog-sheet-filters").style.display=type==="sort"?"none":"block";$("#catalog-mobile-sort").style.display=type==="sort"?"block":"none";sheet.classList.add("is-open");$(".catalog-sheet-backdrop").classList.add("is-open");document.body.classList.add("scroll-lock");}
function closeSheet(){$("#catalog-filter-sheet").classList.remove("is-open");$(".catalog-sheet-backdrop").classList.remove("is-open");document.body.classList.remove("scroll-lock");}
function renderPageMeta(){
  const title=$("#catalog-title"),description=$("#catalog-description"),image=$("#catalog-banner-img"),crumb=$("#catalog-current");
  let heading="Shop all groceries",copy="Browse supermarket essentials across every department.",heroProduct=baseProducts[0];
  if(mode==="category"&&pageCategory){heading=pageCategory.name;copy=pageCategory.description;image.src=pageCategory.image;image.alt=pageCategory.imageAlt;crumb.textContent=pageCategory.name;$("#catalog-subcategories").innerHTML=`<button class="catalog-subcategory ${state.subcategories.length?"":"is-active"}" data-subcategory="">All</button>${pageCategory.subcategories.map(sub=>`<button class="catalog-subcategory ${state.subcategories.includes(sub.slug)?"is-active":""}" data-subcategory="${sub.slug}">${sub.name} (${sub.productCount})</button>`).join("")}`;}
  if(mode==="search"){heading=searchTerm?`Results for “${searchTerm}”`:"Search products";copy=`Products matching names, brands, categories, tags and keywords.`;$("#catalog-suggestions").innerHTML=["rice","milk","atta","tea","detergent","baby care"].map(term=>`<a href="search-results.html?q=${encodeURIComponent(term)}">${term}</a>`).join("");const related=[...new Set(baseProducts.map(item=>item.category))].slice(0,5);$("#catalog-related").innerHTML=related.map(name=>`<a href="category.html?category=${encodeURIComponent(name)}">${name}</a>`).join("");}
  if(mode==="offers"){heading="Offers and savings";copy="Limited-time product deals, coupons and delivery savings in one place.";}
  if(mode==="new"){heading="New arrivals";copy="Discover the newest products added to FreshBasket.";}
  if(mode==="best"){heading="Best sellers";copy="Customer favourites chosen for popularity, ratings and repeat purchases.";}
  title.textContent=heading;description.textContent=copy;crumb.textContent=heading;if(mode!=="category"&&heroProduct){image.src=heroProduct.image;image.alt=heroProduct.imageAlt||heroProduct.name;}
}
function bind(){
  document.addEventListener("change",event=>{if(event.target.matches("#catalog-sort,#catalog-mobile-sort")){state.sort=event.target.value;$("#catalog-sort").value=state.sort;$("#catalog-mobile-sort").value=state.sort;render();return;}if(event.target.closest("#catalog-filters")){readFilters($("#catalog-filters"));syncFilterInputs();render();}});
  document.addEventListener("click",event=>{const button=event.target.closest("button");if(!button)return;
    if(button.dataset.listAdd){window.addCart(Number(button.dataset.listAdd));render();}if(button.dataset.listQty){const id=Number(button.dataset.listQty),item=product(id),entry=store.get("fb-cart",[]).find(row=>Number(row.id)===id),delta=Number(button.dataset.delta);if(delta>0&&entry.qty>=item.stock){ui.toast(`Only ${item.stock} available`);return;}window.qty(id,delta);render();}
    if(button.dataset.listWish){window.toggleWish(Number(button.dataset.listWish));render();}if(button.dataset.listQuick)quickView(Number(button.dataset.listQuick));
    if(button.dataset.view){state.view=button.dataset.view;$$("[data-view]").forEach(node=>{node.classList.remove("active");node.classList.toggle("is-active",node.dataset.view===state.view);node.setAttribute("aria-pressed",String(node.dataset.view===state.view));});render();}if(button.dataset.loadMore!==undefined){state.limit+=20;render();}
    if(button.dataset.clearFilters!==undefined)clearFilters();if(button.dataset.removeFilter)removeFilter(button.dataset.removeFilter,button.dataset.value);
    if(button.dataset.subcategory!==undefined){state.subcategories=button.dataset.subcategory?[button.dataset.subcategory]:[];syncFilterInputs();$$("[data-subcategory]").forEach(node=>node.classList.toggle("is-active",node===button));render();}
    if(button.dataset.mobileFilters!==undefined){syncFilterInputs();openSheet("filter");}if(button.dataset.mobileSort!==undefined)openSheet("sort");if(button.dataset.sheetClose!==undefined)closeSheet();if(button.dataset.sheetClear!==undefined)clearFilters();if(button.dataset.sheetApply!==undefined){if($("#catalog-filter-sheet").dataset.sheet==="filter")readFilters($("#catalog-sheet-filters"));else state.sort=$("#catalog-mobile-sort").value;syncFilterInputs();render();closeSheet();}
  });$(".catalog-sheet-backdrop").onclick=closeSheet;document.addEventListener("keydown",event=>{if(event.key==="Escape")closeSheet();});
}
function init(){setupBase();renderPageMeta();renderFilters();syncFilterInputs();render();bind();}
window.FreshBasketListing=Object.freeze({render,clearFilters,state});
init();
const premiumStyles=document.createElement("link");premiumStyles.rel="stylesheet";premiumStyles.href="assets/css/catalog-premium.css";document.head.appendChild(premiumStyles);
const premiumScript=document.createElement("script");premiumScript.src="assets/js/catalog-premium.js";document.body.appendChild(premiumScript);
})(window,document);
