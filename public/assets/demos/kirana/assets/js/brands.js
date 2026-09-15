(function(window){"use strict";
const marks={
wheat:'<path d="M24 44V16M24 20c-7-1-9-5-9-9 6 0 9 3 9 9Zm0 8c7-1 10-5 10-9-7 0-10 3-10 9Zm0 8c-7-1-10-5-10-9 7 0 10 3 10 9Z"/>',
crown:'<path d="m10 19 7 6 7-13 7 13 7-6-3 17H13l-3-17Zm5 21h18"/>',
drop:'<path d="M24 8s13 15 13 24a13 13 0 0 1-26 0C11 23 24 8 24 8Z"/><path d="M18 34c2 3 6 4 9 2"/>',
sun:'<circle cx="22" cy="23" r="8"/><path d="M22 8v5M22 33v6M7 23h6M31 23h7M11 12l4 4M29 30l4 4"/><path d="M24 39c5-8 11-9 17-8-2 7-7 11-17 8Z"/>',
pinch:'<path d="M12 35c9-13 16-17 25-19-2 11-9 19-25 19Z"/><circle cx="15" cy="14" r="2"/><circle cx="23" cy="10" r="2"/><circle cx="31" cy="12" r="2"/>',
sparkle:'<path d="m24 7 3 10 10 3-10 3-3 10-3-10-10-3 10-3 3-10Zm11 25 2 5 5 2-5 2-2 5-2-5-5-2 5-2 2-5Z"/>',
jar:'<path d="M14 17h20l-2 26H16l-2-26Zm2-8h16v8H16V9Z"/><path d="M20 27h8M20 33h8"/>',
bowl:'<path d="M8 23h32c-2 13-8 19-16 19S10 36 8 23Z"/><path d="M14 16h17M34 12l6 4-6 4"/>',
leaf:'<path d="M9 31C12 15 23 8 40 9c-1 17-11 27-31 22Z"/><path d="M11 31c9-8 16-13 27-19M24 23c-3-2-6-3-9-3"/>',
check:'<path d="M12 13h19c2 15-3 25-15 30"/><path d="m20 29 5 5 12-14"/><path d="M12 13c-1 12 1 20 7 25"/>',
basket:'<path d="M9 22h30l-4 20H13L9 22Zm7 0c1-10 15-10 16 0M18 28v8M25 28v8M32 28v8"/>',
bread:'<path d="M10 25c0-9 6-15 14-15s14 6 14 15v17H10V25Z"/><path d="M17 21l4 4M24 17l4 4M30 22l4 4"/>',
silk:'<path d="M8 35c8-23 19-27 34-25-4 15-13 23-27 25 8 0 16 2 23 7-14 2-24 0-30-7Z"/>',
home:'<path d="m7 25 17-14 17 14M12 23v19h24V23"/><path d="m22 26 2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6Z"/>',
coconut:'<circle cx="22" cy="28" r="13"/><path d="M19 15c3-7 8-10 15-9-1 7-5 11-12 12M10 39h30M35 11l5-5"/>'
};
const source=[
["grainroot","GrainRoot","Grains & Staples",12,"#9a6b18","wheat"],["royalfield","RoyalField","Premium Rice",9,"#78542b","crown"],["dailydale","DailyDale","Dairy Essentials",11,"#287aa0","drop"],["sundrop-farm","SunDrop Farm","Farm Fresh Products",8,"#d79516","sun"],["purepinch","PurePinch","Spices & Masala",10,"#a64d32","pinch"],["sparkle","Sparkle","Home Cleaning",7,"#168d8b","sparkle"],["goodday-pantry","GoodDay Pantry","Pantry Essentials",13,"#7c7b2b","jar"],["quickbowl","QuickBowl","Instant Food",8,"#d06a24","bowl"],["morningleaf","MorningLeaf","Tea & Breakfast",9,"#39834d","leaf"],["harvestwise","HarvestWise","Organic Staples",10,"#5b7433","check"],["freshbasket-farms","FreshBasket Farms","Fresh Produce",14,"#0e5a33","basket",true],["bakehouse","BakeHouse","Bakery Products",8,"#9b5d36","bread"],["greendairy","GreenDairy","Milk & Dairy",9,"#4d8a80","drop"],["earthsilk","EarthSilk","Personal Care",7,"#7a705d","silk"],["homeglow","HomeGlow","Household Care",8,"#147b70","home"],["konkan-gold","Konkan Gold","Regional Staples",6,"#a87918","coconut"]];
window.FB_BRANDS=Object.freeze(source.map(([slug,name,category,productCount,accent,type,featured])=>Object.freeze({id:slug,slug,name,category,productCount,accent,featured:Boolean(featured),link:`shop.html?brand=${encodeURIComponent(slug)}`,logoMarkup:`<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">${marks[type]}</svg>`})));
})(window);
