(function(){
  "use strict";
  requestAnimationFrame(function(){document.body.classList.add("page-ready")});
  var areas=[
    ["Legal Consultancy","Clear, strategic guidance for important personal and business decisions.","Advisory"],
    ["Corporate Law","Practical counsel for governance, compliance and commercial growth.","Business"],
    ["High Court Matters","Prepared and persuasive representation before the High Court.","Litigation"],
    ["Supreme Court Matters","Focused appellate strategy for complex questions of law.","Appeals"],
    ["Land Revenue Law","Advice on title, tenure, records and revenue proceedings.","Property"],
    ["RERA Advisory","End-to-end guidance for developers, buyers and project disputes.","Real Estate"],
    ["Civil & Trial Court","Disciplined pleadings and representation through every stage.","Litigation"],
    ["Family Law","Sensitive, confidential support for families and individuals.","Private Client"],
    ["Criminal Matters","Prompt defence strategy with careful protection of legal rights.","Defence"],
    ["Bail Matters","Time-sensitive preparation and representation for urgent relief.","Urgent Relief"],
    ["NCLT Matters","Commercially informed support for insolvency and company disputes.","Tribunal"],
    ["POCSO Matters","Responsible, discreet representation in highly sensitive matters.","Special Law"],
    ["Consumer Disputes","Effective remedies for defective services and unfair practices.","Consumer"],
    ["Service Law","Representation in employment, disciplinary and benefits matters.","Employment"],
    ["Banking & Recovery","Strategic assistance with recovery, securities and debt disputes.","Finance"],
    ["Arbitration","Efficient dispute resolution outside conventional court proceedings.","Resolution"],
    ["Labour Law","Balanced legal guidance for employers and employees.","Employment"],
    ["Commercial Litigation","Business-focused strategy for high-value commercial disputes.","Business"],
    ["Contract Drafting","Precise agreements designed to prevent ambiguity and risk.","Documentation"],
    ["Intellectual Property","Protection and enforcement for brands, ideas and creative assets.","IP Rights"]
  ];
  var grid=document.getElementById("practiceGrid");
  grid.innerHTML=areas.map(function(area,index){var number=String(index+1).padStart(2,"0");return '<article class="practice-card practice-art-'+number+'"><div class="practice-card-top"><span class="practice-icon"><svg><use href="#i-scale"/></svg></span></div><small>'+area[2]+'</small><h3>'+area[0]+'</h3><p>'+area[1]+'</p><a href="#contact"><span>Learn More</span><svg><use href="#i-arrow"/></svg></a></article>'}).join("");
  grid.querySelectorAll('[data-open="booking"]').forEach(function(button){button.addEventListener("click",openModal)});

  var header=document.querySelector(".site-header"),menu=document.querySelector(".menu-toggle"),nav=document.querySelector(".nav-links");
  menu.addEventListener("click",function(){var open=nav.classList.toggle("open");menu.setAttribute("aria-expanded",String(open))});
  nav.querySelectorAll("a").forEach(function(link){link.addEventListener("click",function(){nav.classList.remove("open");menu.setAttribute("aria-expanded","false")})});
  var navLinks=[].slice.call(nav.querySelectorAll('a[href^="#"]'));
  var back=document.getElementById("backToTop");
  function onScroll(){header.classList.toggle("scrolled",scrollY>20);back.classList.toggle("show",scrollY>600);var marker=scrollY+180,current="#home";navLinks.forEach(function(link){var section=document.querySelector(link.getAttribute("href"));if(section&&section.offsetTop<=marker)current=link.getAttribute("href")});navLinks.forEach(function(link){link.classList.toggle("active",link.getAttribute("href")===current)})}
  addEventListener("scroll",onScroll,{passive:true});onScroll();back.addEventListener("click",function(){scrollTo({top:0,behavior:"smooth"})});

  var modal=document.getElementById("booking"),lastFocus;
  function openModal(){lastFocus=document.activeElement;modal.classList.remove("closing");modal.classList.add("open");modal.setAttribute("aria-hidden","false");document.body.style.overflow="hidden";setTimeout(function(){modal.querySelector("input").focus()},320)}
  function closeModal(){if(!modal.classList.contains("open"))return;modal.classList.add("closing");modal.setAttribute("aria-hidden","true");setTimeout(function(){modal.classList.remove("open","closing");document.body.style.overflow="";if(lastFocus)lastFocus.focus()},280)}
  document.querySelectorAll('[data-open="booking"]').forEach(function(button){button.addEventListener("click",openModal)});
  modal.querySelector(".modal-close").addEventListener("click",closeModal);
  modal.addEventListener("click",function(event){if(event.target===modal)closeModal()});
  addEventListener("keydown",function(event){if(event.key==="Escape"&&modal.classList.contains("open"))closeModal()});
  var dateInput=document.getElementById("appointmentDate"),dateToggle=document.getElementById("dateToggle"),datePicker=document.getElementById("datePicker"),calendarTitle=document.getElementById("calendarTitle"),calendarDays=document.getElementById("calendarDays");
  var today=new Date();today.setHours(0,0,0,0);var viewDate=new Date(today.getFullYear(),today.getMonth(),1),selectedDate=null;
  function sameDate(a,b){return a&&b&&a.getFullYear()===b.getFullYear()&&a.getMonth()===b.getMonth()&&a.getDate()===b.getDate()}
  function formatDate(date){return String(date.getDate()).padStart(2,"0")+" "+date.toLocaleString("en",{month:"short"})+" "+date.getFullYear()}
  function renderCalendar(){
    calendarTitle.textContent=viewDate.toLocaleString("en",{month:"long",year:"numeric"});
    calendarDays.innerHTML="";
    var first=viewDate.getDay(),count=new Date(viewDate.getFullYear(),viewDate.getMonth()+1,0).getDate();
    for(var blank=0;blank<first;blank++){var spacer=document.createElement("i");calendarDays.appendChild(spacer)}
    for(var day=1;day<=count;day++){(function(dayNumber){var date=new Date(viewDate.getFullYear(),viewDate.getMonth(),dayNumber),button=document.createElement("button");button.type="button";button.textContent=dayNumber;if(date<today){button.disabled=true}if(sameDate(date,today))button.classList.add("today");if(sameDate(date,selectedDate))button.classList.add("selected");button.addEventListener("click",function(){selectedDate=date;dateInput.value=formatDate(date);dateInput.setCustomValidity("");closeCalendar();renderCalendar()});calendarDays.appendChild(button)})(day)}
  }
  function openCalendar(){datePicker.hidden=false;dateToggle.setAttribute("aria-expanded","true");renderCalendar()}
  function closeCalendar(){datePicker.hidden=true;dateToggle.setAttribute("aria-expanded","false")}
  dateToggle.addEventListener("click",function(event){event.stopPropagation();datePicker.hidden?openCalendar():closeCalendar()});
  dateInput.addEventListener("click",openCalendar);
  document.getElementById("prevMonth").addEventListener("click",function(){viewDate.setMonth(viewDate.getMonth()-1);renderCalendar()});
  document.getElementById("nextMonth").addEventListener("click",function(){viewDate.setMonth(viewDate.getMonth()+1);renderCalendar()});
  document.getElementById("clearDate").addEventListener("click",function(){selectedDate=null;dateInput.value="";renderCalendar()});
  document.getElementById("todayDate").addEventListener("click",function(){selectedDate=new Date(today);viewDate=new Date(today.getFullYear(),today.getMonth(),1);dateInput.value=formatDate(today);closeCalendar()});
  document.addEventListener("click",function(event){if(!event.target.closest(".date-field"))closeCalendar()});

  var customSelects=[];
  function enhanceSelect(select){
    var wrap=document.createElement("span"),trigger=document.createElement("button"),menu=document.createElement("span");
    wrap.className="custom-select";trigger.type="button";trigger.className="custom-select-trigger";trigger.setAttribute("aria-haspopup","listbox");trigger.setAttribute("aria-expanded","false");menu.className="custom-select-menu";menu.setAttribute("role","listbox");menu.hidden=true;
    trigger.innerHTML='<span>'+select.options[select.selectedIndex].text+'</span><i></i>';
    [].slice.call(select.options).forEach(function(option,index){if(index===0)return;var item=document.createElement("button");item.type="button";item.className="custom-select-option";item.setAttribute("role","option");item.dataset.value=option.value;item.innerHTML='<span>'+option.text+'</span><svg><use href="#i-check"/></svg>';item.addEventListener("click",function(){select.value=option.value;select.dispatchEvent(new Event("change",{bubbles:true}));trigger.querySelector("span").textContent=option.text;menu.querySelectorAll(".selected").forEach(function(el){el.classList.remove("selected");el.setAttribute("aria-selected","false")});item.classList.add("selected");item.setAttribute("aria-selected","true");closeCustomSelect(wrap)});menu.appendChild(item)});
    wrap.appendChild(trigger);wrap.appendChild(menu);select.classList.add("select-native");select.insertAdjacentElement("afterend",wrap);
    trigger.addEventListener("click",function(event){event.stopPropagation();var opening=menu.hidden;customSelects.forEach(closeCustomSelect);if(opening){menu.hidden=false;wrap.classList.add("open");var row=wrap.closest(".form-row");if(row)row.classList.add("dropdown-active");trigger.setAttribute("aria-expanded","true")}});
    wrap.addEventListener("keydown",function(event){var items=[].slice.call(menu.querySelectorAll(".custom-select-option")),current=items.indexOf(document.activeElement);if(event.key==="ArrowDown"){event.preventDefault();(items[current+1]||items[0]).focus()}if(event.key==="ArrowUp"){event.preventDefault();(items[current-1]||items[items.length-1]).focus()}if(event.key==="Escape"){closeCustomSelect(wrap);trigger.focus()}});
    customSelects.push(wrap)
  }
  function closeCustomSelect(wrap){if(!wrap)return;var menu=wrap.querySelector(".custom-select-menu"),trigger=wrap.querySelector(".custom-select-trigger"),row=wrap.closest(".form-row");menu.hidden=true;wrap.classList.remove("open");if(row)row.classList.remove("dropdown-active");trigger.setAttribute("aria-expanded","false")}
  document.querySelectorAll("#bookingForm select").forEach(enhanceSelect);
  document.addEventListener("click",function(){customSelects.forEach(closeCustomSelect)});
  var toast=document.getElementById("toast");
  document.getElementById("bookingForm").addEventListener("submit",function(event){event.preventDefault();var form=event.target,submit=form.querySelector('button[type="submit"]');if(submit.disabled)return;submit.disabled=true;submit.classList.add("loading");submit.dataset.label=submit.textContent;submit.innerHTML='<span class="button-spinner"></span><span>Submitting…</span>';setTimeout(function(){closeModal();toast.innerHTML='<span class="success-check"><svg><use href="#i-check"/></svg></span><span><b>Appointment Request Submitted</b><small>Our legal desk will confirm your preferred time shortly.</small></span>';toast.classList.add("show","success-toast");setTimeout(function(){toast.classList.remove("show")},4200);form.reset();selectedDate=null;submit.disabled=false;submit.classList.remove("loading");submit.textContent=submit.dataset.label;customSelects.forEach(function(wrap){var select=wrap.previousElementSibling;wrap.querySelector(".custom-select-trigger span").textContent=select.options[0].text;wrap.querySelectorAll(".selected").forEach(function(item){item.classList.remove("selected")})})},900)});

  var targets=document.querySelectorAll(".section-heading,.practice-heading>div,.practice-card,.practice-cta,.feature-grid article,.about-grid>*,.stats article,.case-feature,.case-stack article,.journal-feature,.journal-rail article,.journal-cta,.team-grid>*,.recognition span,.testimonial-intro,.reviews article,.office-grid article,.contact-cta .container,.footer-grid>div");
  targets.forEach(function(el,index){el.classList.add("reveal");el.style.setProperty("--reveal-delay",(index%4)*70+"ms")});
  if("IntersectionObserver" in window){var observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add("visible");observer.unobserve(entry.target)}})},{threshold:.1});targets.forEach(function(el){observer.observe(el)})}else{targets.forEach(function(el){el.classList.add("visible")})}

  var progress=document.getElementById("scrollProgress"),hero=document.querySelector(".hero");
  function motionScroll(){var max=document.documentElement.scrollHeight-innerHeight,ratio=max>0?scrollY/max:0;progress.style.transform="scaleX("+ratio+")";if(hero&&scrollY<hero.offsetHeight)hero.style.setProperty("--hero-shift",Math.min(scrollY*.16,80)+"px")}
  addEventListener("scroll",motionScroll,{passive:true});motionScroll();
  document.querySelectorAll(".button,.text-button,.practice-card a,.journal-grid a").forEach(function(el){el.addEventListener("pointermove",function(event){var rect=el.getBoundingClientRect();el.style.setProperty("--mouse-x",(event.clientX-rect.left)+"px");el.style.setProperty("--mouse-y",(event.clientY-rect.top)+"px")})});

  var statNumbers=document.querySelectorAll(".stats b");
  function animateCounter(el){if(el.dataset.counted)return;el.dataset.counted="true";var original=el.textContent.trim(),target=parseFloat(original.replace(/,/g,"")),suffix=original.replace(/[0-9,.]/g,""),start=performance.now(),duration=1500;function tick(now){var progress=Math.min((now-start)/duration,1),eased=1-Math.pow(1-progress,3),value=Math.round(target*eased);el.innerHTML=value.toLocaleString("en-IN")+(suffix?'<sup>'+suffix+'</sup>':"");if(progress<1)requestAnimationFrame(tick)}requestAnimationFrame(tick)}
  if("IntersectionObserver" in window){var counterObserver=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){animateCounter(entry.target);counterObserver.unobserve(entry.target)}})},{threshold:.5});statNumbers.forEach(function(el){counterObserver.observe(el)})}else{statNumbers.forEach(animateCounter)}

  var textarea=document.querySelector("#bookingForm textarea");
  textarea.addEventListener("input",function(){textarea.style.height="auto";textarea.style.height=Math.min(textarea.scrollHeight,150)+"px"});
  var journalRail=document.getElementById("journalRail");
  var journalIndex=0,journalCards=[].slice.call(journalRail.querySelectorAll("article"));
  var journalStories=[
    {date:"18 July 2026",area:"Legal Guidance",time:"6 min read",title:"How to Choose the Right Advocate for Your Matter",description:"The right legal relationship begins with clarity. Explore the questions that reveal experience, communication style and strategic fit before you engage counsel.",image:"assets/images/rajan-legal-hero.png"},
    {date:"09 July 2026",area:"Property",time:"4 min read",title:"Important Steps in Property Verification",description:"A practical overview of records, title checks and essential documents.",image:"assets/images/practice/practice-05.jpg"},
    {date:"26 June 2026",area:"Criminal Law",time:"7 min read",title:"Understanding the Bail Application Process",description:"What applicants and families should know before approaching the court.",image:"assets/images/practice/practice-10.jpg"},
    {date:"12 June 2026",area:"Family Law",time:"5 min read",title:"Legal Rights in Sensitive Family Disputes",description:"Protecting dignity, privacy and long-term interests with informed advice.",image:"assets/images/practice/practice-08.jpg"},
    {date:"04 June 2026",area:"Business",time:"8 min read",title:"Clauses Every Commercial Contract Should Address",description:"Essential protections that reduce ambiguity and commercial risk.",image:"assets/images/practice/practice-19.jpg"},
    {date:"25 May 2026",area:"Real Estate",time:"6 min read",title:"What Buyers Should Verify Before a Property Purchase",description:"Key legal and documentary checks that support a more confident transaction.",image:"assets/images/practice/practice-06.jpg"},
    {date:"14 May 2026",area:"Employment",time:"5 min read",title:"Responding to a Workplace Disciplinary Notice",description:"A measured approach to records, procedure and professional representation.",image:"assets/images/practice/practice-14.jpg"},
    {date:"02 May 2026",area:"Intellectual Property",time:"7 min read",title:"Protecting Brand Identity in a Competitive Market",description:"Practical steps for securing and enforcing valuable creative and commercial assets.",image:"assets/images/practice/practice-20.jpg"}
  ];
  var journalFeature=document.querySelector(".journal-feature"),journalFeatureImage=document.querySelector(".journal-feature-image"),journalFeatureMeta=[].slice.call(document.querySelectorAll(".journal-feature .article-meta span")),journalFeatureTitle=document.querySelector(".journal-feature-copy>h3"),journalFeatureDescription=document.querySelector(".journal-feature-copy>p");
  function renderJournal(){
    var featured=journalStories[journalIndex];
    journalFeatureImage.style.backgroundImage='url("'+featured.image+'")';
    journalFeatureMeta[0].textContent=featured.area;journalFeatureMeta[1].textContent=featured.date;journalFeatureMeta[2].textContent=featured.time;
    journalFeatureTitle.textContent=featured.title;journalFeatureDescription.textContent=featured.description;
    journalCards.forEach(function(card,index){
      var story=journalStories[(journalIndex+index+1)%journalStories.length],image=card.querySelector(".journal-card-image"),readTime=image.querySelector("span");
      image.style.backgroundImage='url("'+story.image+'")';
      readTime.textContent=story.time;
      card.querySelector("small").textContent=story.date+" · "+story.area;
      card.querySelector("h3").textContent=story.title;
      card.classList.toggle("editorial-active",index===0);
    });
  }
  function moveJournal(direction){
    journalIndex=(journalIndex+direction+journalStories.length)%journalStories.length;
    var editorial=document.querySelector(".journal-editorial");
    if(editorial){editorial.classList.remove("journal-changing");void editorial.offsetWidth;editorial.classList.add("journal-changing")}
    renderJournal();
  }
  document.getElementById("journalPrev").addEventListener("click",function(){moveJournal(-1)});
  document.getElementById("journalNext").addEventListener("click",function(){moveJournal(1)});
  journalRail.addEventListener("keydown",function(event){if(event.key==="ArrowLeft"){event.preventDefault();moveJournal(-1)}if(event.key==="ArrowRight"){event.preventDefault();moveJournal(1)}});
  journalRail.addEventListener("wheel",function(event){var direction=Math.sign(event.deltaY),canMove=direction>0?journalRail.scrollLeft+journalRail.clientWidth<journalRail.scrollWidth-2:journalRail.scrollLeft>2;if(Math.abs(event.deltaY)>Math.abs(event.deltaX)&&canMove){event.preventDefault();journalRail.scrollLeft+=event.deltaY}},{passive:false});
  var railDragging=false,railStartX=0,railStartScroll=0;
  journalRail.addEventListener("pointerdown",function(event){if(event.target.closest("a,button"))return;railDragging=true;railStartX=event.clientX;railStartScroll=journalRail.scrollLeft;journalRail.classList.add("dragging");journalRail.setPointerCapture(event.pointerId)});
  journalRail.addEventListener("pointermove",function(event){if(railDragging)journalRail.scrollLeft=railStartScroll-(event.clientX-railStartX)});
  journalRail.addEventListener("pointerup",function(){railDragging=false;journalRail.classList.remove("dragging")});
  document.querySelectorAll(".journal .bookmark").forEach(function(button){button.addEventListener("click",function(){var active=button.classList.toggle("saved");button.textContent=active?"◆":"◇";button.setAttribute("aria-pressed",String(active))})});
  var caseCards=[].slice.call(document.querySelectorAll(".case-stack article")),caseIndex=0;
  var caseStories=[
    {date:"09 July 2026",area:"Criminal Law",time:"6 min",title:"Safeguarding Due Process in a Complex Criminal Matter",description:"A focused legal strategy supported fair procedure and timely relief at a critical stage.",image:"assets/images/gavel-case.png",result:"Interim Relief Granted"},
    {date:"05 July 2026",area:"Property",time:"5 min",title:"Resolving a Multi-Party Property Title Dispute",description:"Disciplined document review established clarity and created a practical route to settlement.",image:"assets/images/practice/practice-05.jpg",result:"Settlement Path Secured"},
    {date:"28 June 2026",area:"Bail Matters",time:"4 min",title:"Preparing a Time-Sensitive Bail Application",description:"Precise pleadings and prompt representation supported an effective urgent hearing.",image:"assets/images/practice/practice-10.jpg",result:"Urgent Hearing Supported"},
    {date:"16 June 2026",area:"Corporate",time:"6 min",title:"Negotiating a Commercial Contract Resolution",description:"A measured negotiation strategy protected business continuity and commercial value.",image:"assets/images/practice/practice-02.jpg",result:"Commercial Continuity"},
    {date:"02 June 2026",area:"Family Law",time:"5 min",title:"Finding a Constructive Path Through a Family Dispute",description:"Confidential advice protected dignity while keeping long-term family interests central.",image:"assets/images/practice/practice-08.jpg",result:"Confidential Resolution"},
    {date:"24 May 2026",area:"Banking & Recovery",time:"7 min",title:"Structuring a Practical Recovery Strategy",description:"Evidence-led planning balanced commercial urgency with a proportionate legal response.",image:"assets/images/practice/practice-15.jpg",result:"Recovery Plan Agreed"},
    {date:"11 May 2026",area:"Arbitration",time:"6 min",title:"Resolving a Business Dispute Outside Court",description:"Focused preparation and structured dialogue moved a complex disagreement toward resolution.",image:"assets/images/practice/practice-16.jpg",result:"Dispute Resolved"},
    {date:"29 April 2026",area:"Intellectual Property",time:"5 min",title:"Protecting a Growing Brand from Misuse",description:"Early action secured valuable brand assets and reduced the risk of further commercial harm.",image:"assets/images/practice/practice-20.jpg",result:"Brand Rights Protected"}
  ];
  var caseFeature=document.querySelector(".case-feature"),caseFeatureImage=document.querySelector(".case-feature-image"),caseFeatureMeta=document.querySelector(".case-feature-content>small"),caseFeatureTitle=document.querySelector(".case-feature-content>h3"),caseFeatureDescription=document.querySelector(".case-feature-content>p"),caseFeatureResult=document.querySelector(".case-feature .result-badge");
  function renderCases(){
    if(!caseFeature)return;
    var featured=caseStories[caseIndex];
    caseFeatureImage.style.backgroundImage='url("'+featured.image+'")';
    caseFeatureMeta.textContent=featured.date+" · "+featured.area+" · "+featured.time;
    caseFeatureTitle.textContent=featured.title;
    caseFeatureDescription.textContent=featured.description;
    if(caseFeatureResult){var resultText=caseFeatureResult.lastChild;if(resultText&&resultText.nodeType===3)resultText.nodeValue=featured.result;else caseFeatureResult.appendChild(document.createTextNode(featured.result))}
    caseCards.forEach(function(card,index){
      var story=caseStories[(caseIndex+index+1)%caseStories.length],thumb=card.querySelector(".case-thumb"),meta=card.querySelector("small"),title=card.querySelector("h3");
      thumb.style.backgroundImage='url("'+story.image+'")';
      meta.textContent=story.date+" · "+story.area+" · "+story.time;
      title.textContent=story.title;
      card.classList.toggle("editorial-active",index===0);
    });
  }
  function selectCase(direction){
    caseIndex=(caseIndex+direction+caseStories.length)%caseStories.length;
    var editorial=document.querySelector(".case-editorial");
    if(editorial){editorial.classList.remove("case-changing");void editorial.offsetWidth;editorial.classList.add("case-changing")}
    renderCases();
  }
  caseCards.forEach(function(card){card.tabIndex=0});journalCards.forEach(function(card){card.tabIndex=0});
  renderCases();renderJournal();
  document.getElementById("casePrev").addEventListener("click",function(){selectCase(-1)});
  document.getElementById("caseNext").addEventListener("click",function(){selectCase(1)});
  var reviewsViewport=document.getElementById("reviewsViewport"),reviewsTrack=document.getElementById("reviewsTrack");
  if(reviewsViewport&&reviewsTrack){
    var reviewCards=[].slice.call(reviewsTrack.querySelectorAll("article")),reviewIndex=0,reviewTimer;
    function visibleReviewCount(){return innerWidth<=768?1:2}
    function moveReviews(){
      if(!reviewCards.length)return;
      var maxStart=Math.max(0,reviewCards.length-visibleReviewCount());
      reviewIndex=reviewIndex>=maxStart?0:reviewIndex+1;
      reviewsViewport.scrollTo({left:reviewCards[reviewIndex].offsetLeft-reviewsTrack.offsetLeft,behavior:"smooth"});
    }
    function startReviews(){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;clearInterval(reviewTimer);reviewTimer=setInterval(moveReviews,4200)}
    reviewsViewport.addEventListener("pointerenter",function(){clearInterval(reviewTimer)});
    reviewsViewport.addEventListener("pointerleave",startReviews);
    reviewsViewport.addEventListener("focusin",function(){clearInterval(reviewTimer)});
    reviewsViewport.addEventListener("focusout",startReviews);
    addEventListener("resize",function(){reviewIndex=0;reviewsViewport.scrollLeft=0;startReviews});
    startReviews();
  }
  if(matchMedia("(pointer:fine)").matches&&!matchMedia("(prefers-reduced-motion: reduce)").matches){
    var cursorDot=document.createElement("span"),cursorRing=document.createElement("span"),mouseX=-100,mouseY=-100,ringX=-100,ringY=-100;cursorDot.className="premium-cursor-dot";cursorRing.className="premium-cursor-ring";document.body.appendChild(cursorDot);document.body.appendChild(cursorRing);addEventListener("pointermove",function(event){mouseX=event.clientX;mouseY=event.clientY;cursorDot.style.transform="translate3d("+(mouseX-3)+"px,"+(mouseY-3)+"px,0)"});function cursorFrame(){ringX+=(mouseX-ringX)*.16;ringY+=(mouseY-ringY)*.16;cursorRing.style.transform="translate3d("+(ringX-18)+"px,"+(ringY-18)+"px,0)";requestAnimationFrame(cursorFrame)}cursorFrame();document.querySelectorAll("a,button,input,select,textarea,.tilt-card").forEach(function(item){item.addEventListener("pointerenter",function(){document.body.classList.add("cursor-active")});item.addEventListener("pointerleave",function(){document.body.classList.remove("cursor-active")})});
    document.querySelectorAll(".hero .button,.practice-cta .button,.contact-cta .button").forEach(function(button){button.classList.add("magnetic");button.addEventListener("pointermove",function(event){var rect=button.getBoundingClientRect(),x=(event.clientX-rect.left-rect.width/2)*.12,y=(event.clientY-rect.top-rect.height/2)*.16;button.style.transform="translate3d("+x+"px,"+y+"px,0)"});button.addEventListener("pointerleave",function(){button.style.transform=""})});
    document.querySelectorAll(".feature-grid article,.practice-card,.case-feature,.case-stack article,.journal-feature,.journal-rail article,.office-grid article,.reviews article").forEach(function(card){card.classList.add("tilt-card");var glare=document.createElement("span");glare.className="tilt-glare";glare.setAttribute("aria-hidden","true");card.appendChild(glare);card.addEventListener("pointermove",function(event){var rect=card.getBoundingClientRect(),px=(event.clientX-rect.left)/rect.width,py=(event.clientY-rect.top)/rect.height;card.style.setProperty("--tilt-x",((.5-py)*7).toFixed(2)+"deg");card.style.setProperty("--tilt-y",((px-.5)*7).toFixed(2)+"deg");card.style.setProperty("--glare-x",(px*100).toFixed(1)+"%");card.style.setProperty("--glare-y",(py*100).toFixed(1)+"%")});card.addEventListener("pointerleave",function(){card.style.setProperty("--tilt-x","0deg");card.style.setProperty("--tilt-y","0deg")})})
  }
})();
