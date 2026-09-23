(function(window,document){
"use strict";
const root=document.querySelector("[data-categories-page]");if(!root)return;
const categories=window.CATEGORY_METADATA||[];
const escape=value=>window.FBUI?.escapeHTML?window.FBUI.escapeHTML(String(value||"")):String(value||"");
function card(category){
  return `<article class="catalog-category-card">
    <a class="catalog-category-image" href="category.html?category=${encodeURIComponent(category.slug)}">
      <img src="${category.image}" alt="${escape(category.name)} category" loading="lazy" onerror="imageFallback(this)">
    </a>
    <div class="catalog-category-copy">
      <span class="catalog-category-count">${category.productCount} products</span>
      <h3>${escape(category.name)}</h3>
      <p>${escape(category.description)}</p>
      <a class="btn btn-outline" href="category.html?category=${encodeURIComponent(category.slug)}">View Products</a>
    </div>
  </article>`;
}
function render(id,list){const host=document.getElementById(id);if(host)host.innerHTML=list.map(card).join("");}
const popular=[...categories].sort((a,b)=>(b.productCount-a.productCount)||Number(b.featured)-Number(a.featured)).slice(0,8);
const seasonalSlugs=["fruits-and-vegetables","beverages-and-juices","pooja-essentials","health-and-wellness"];
const seasonal=seasonalSlugs.map(slug=>categories.find(category=>category.slug===slug)).filter(Boolean);
const popularSlugs=new Set(popular.map(category=>category.slug));
let featured=categories.filter(category=>category.featured&&!popularSlugs.has(category.slug));
if(featured.length<4)featured=[...categories].filter(category=>!popularSlugs.has(category.slug)).slice(0,8);
render("popular-categories",popular);
render("all-categories",categories);
render("seasonal-categories",seasonal);
render("featured-collections",featured.slice(0,8));
})(window,document);
