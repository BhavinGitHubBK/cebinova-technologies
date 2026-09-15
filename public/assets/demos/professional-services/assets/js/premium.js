(function(){
  'use strict';
  var header=document.querySelector('header');
  function onScroll(){header.classList.toggle('scrolled',window.scrollY>28)}
  onScroll();window.addEventListener('scroll',onScroll,{passive:true});

  var navLinks=Array.prototype.slice.call(document.querySelectorAll('.links a[href^="#"]'));
  var navSections=navLinks.map(function(link){return document.querySelector(link.getAttribute('href'))}).filter(Boolean);
  function updateActiveLink(){var marker=window.scrollY+150,current=navSections[0];navSections.forEach(function(section){if(section.offsetTop<=marker&&(!current||section.offsetTop>=current.offsetTop))current=section});navLinks.forEach(function(link){link.classList.toggle('active',current&&link.getAttribute('href')==='#'+current.id)})}
  updateActiveLink();window.addEventListener('scroll',updateActiveLink,{passive:true});

  var revealTargets=document.querySelectorAll('.section h2,.cards article,.practice-card,.stats div,.dashboard,.timeline li,.form');
  revealTargets.forEach(function(el){el.classList.add('reveal')});
  if('IntersectionObserver' in window){
    var observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add('visible');observer.unobserve(entry.target)}})},{threshold:.12});
    revealTargets.forEach(function(el){observer.observe(el)});
  }else{revealTargets.forEach(function(el){el.classList.add('visible')})}

  document.querySelectorAll('.stats div').forEach(function(card,index){
    var trend=document.createElement('span');trend.className='metric-trend';
    trend.textContent=['Established expertise','Matters across Gujarat','Consistently recommended','Same-day acknowledgement'][index];card.appendChild(trend);
  });

  document.querySelectorAll('.reviews article').forEach(function(card,index){
    var names=['RP','ND','KM'],avatar=document.createElement('span');avatar.className='review-avatar';avatar.textContent=names[index];card.insertBefore(avatar,card.firstChild);
    var name=card.querySelector('b');if(name){var verified=document.createElement('span');verified.className='verified';verified.textContent='Verified';name.appendChild(verified)}
  });

  var caseResult=document.getElementById('caseResult');
  if(caseResult){new MutationObserver(function(){var result=caseResult.querySelector('.result');if(result&&result.querySelector('.result-grid')&&!result.querySelector('.case-progress')){var progress=document.createElement('div');progress.className='case-progress';progress.innerHTML='<div class="case-progress-head"><span>Case progress</span><b>68% complete</b></div><div class="case-progress-track"><div class="case-progress-bar"></div></div>';result.insertBefore(progress,result.firstChild);requestAnimationFrame(function(){result.classList.add('ready')})}}).observe(caseResult,{childList:true,subtree:true})}
})();
