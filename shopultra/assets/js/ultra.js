(function(){
  const wrap = document.querySelector('[data-gallery-wrap]');
  if(!wrap) return;

  const main = wrap.querySelector('[data-gallery-main]');
  const thumbs = wrap.querySelectorAll('[data-gallery-thumb]');
  const counter = wrap.querySelector('[data-counter]');

  const lb = document.querySelector('[data-lightbox]');
  const lbImg = lb ? lb.querySelector('[data-lb-img]') : null;
  const stage = lb ? lb.querySelector('[data-stage]') : null;

  if(!main || !thumbs.length) return;

  // ensure indexes
  thumbs.forEach((b, i)=>{ if(!b.dataset.index) b.dataset.index = i; });

  const sources = Array.from(thumbs).map(b => b.getAttribute('data-src')).filter(Boolean);
  let idx = 0;

  // ===== Blur preload helper =====
  function setBlurLoading(imgEl, src){
    if(!imgEl || !src) return;

    imgEl.classList.remove('img-sharp');
    imgEl.classList.add('img-blur');

    const pre = new Image();
    pre.onload = () => {
      imgEl.src = src;
      requestAnimationFrame(() => {
        imgEl.classList.add('img-sharp');
        imgEl.classList.remove('img-blur');
      });
    };
    pre.src = src;
  }

  // ===== Lightbox Zoom / Pan =====
  let scale = 1;
  let tx = 0, ty = 0;
  let isPanning = false;
  let startX = 0, startY = 0;

  // pinch
  let pinchStartDist = null;
  let pinchStartScale = 1;

  function applyTransform(){
    if(!lbImg) return;

    scale = Math.max(1, Math.min(5, scale));

    // clamp translate
    const maxT = 600 * (scale - 1);
    tx = Math.max(-maxT, Math.min(maxT, tx));
    ty = Math.max(-maxT, Math.min(maxT, ty));

    lbImg.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
  }

  function resetZoom(){
    scale = 1; tx = 0; ty = 0;
    applyTransform();
  }

  function zoomBy(delta){
    scale = scale + delta;
    applyTransform();
  }

  // ===== Gallery navigation =====
  function setActive(i){
    idx = (i + sources.length) % sources.length;

    setBlurLoading(main, sources[idx]);

    thumbs.forEach(b => b.classList.remove('active'));
    const currentBtn = Array.from(thumbs).find(b => Number(b.dataset.index) === idx);
    if(currentBtn){
      currentBtn.classList.add('active');
      currentBtn.scrollIntoView({behavior:'smooth', inline:'nearest', block:'nearest'});
    }

    if(counter) counter.textContent = `${idx+1} / ${sources.length}`;

    if(lb && lbImg && !lb.hasAttribute('hidden')){
      resetZoom();
      setBlurLoading(lbImg, sources[idx]);
    }
  }

  // thumbs click
  thumbs.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const i = Number(btn.dataset.index || 0);
      setActive(i);
    });
  });

  // arrows on main
  const prevBtn = wrap.querySelector('[data-prev]');
  const nextBtn = wrap.querySelector('[data-next]');
  if(prevBtn) prevBtn.addEventListener('click', ()=> setActive(idx - 1));
  if(nextBtn) nextBtn.addEventListener('click', ()=> setActive(idx + 1));

  // ===== Lightbox open/close =====
  let autoTimer = null;
  let isAuto = false;

  function stopAuto(){
    isAuto = false;
    const autoBtn = lb ? lb.querySelector('[data-auto]') : null;
    if(autoBtn) autoBtn.textContent = '▶';
    if(autoTimer){ clearInterval(autoTimer); autoTimer = null; }
  }

  function openLB(){
    if(!lb || !lbImg) return;
    lb.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    stopAuto();
    resetZoom();
    setBlurLoading(lbImg, sources[idx]);
  }

  function closeLB(){
    if(!lb) return;
    stopAuto();
    lb.setAttribute('hidden','');
    document.body.style.overflow = '';
  }

  main.addEventListener('click', openLB);

  if(lb){
    const closeBtn = lb.querySelector('[data-close]');
    const lbPrev = lb.querySelector('[data-lb-prev]');
    const lbNext = lb.querySelector('[data-lb-next]');
    const zIn = lb.querySelector('[data-zoom-in]');
    const zOut = lb.querySelector('[data-zoom-out]');
    const autoBtn = lb.querySelector('[data-auto]');

    if(closeBtn) closeBtn.addEventListener('click', closeLB);
    if(lbPrev) lbPrev.addEventListener('click', (e)=>{ e.stopPropagation(); setActive(idx - 1); });
    if(lbNext) lbNext.addEventListener('click', (e)=>{ e.stopPropagation(); setActive(idx + 1); });

    if(zIn) zIn.addEventListener('click', ()=> zoomBy(0.25));
    if(zOut) zOut.addEventListener('click', ()=> zoomBy(-0.25));

    if(autoBtn){
      autoBtn.addEventListener('click', (e)=>{
        e.stopPropagation();
        if(isAuto){
          stopAuto();
        } else {
          isAuto = true;
          autoBtn.textContent = '⏸';
          autoTimer = setInterval(()=> setActive(idx + 1), 2500);
        }
      });
    }

    // click background closes
    lb.addEventListener('click', (e)=>{
      if(e.target === lb) closeLB();
    });

    // keyboard
    document.addEventListener('keydown', (e)=>{
      if(lb.hasAttribute('hidden')) return;
      if(e.key === 'Escape') closeLB();
      if(e.key === 'ArrowLeft') setActive(idx - 1);
      if(e.key === 'ArrowRight') setActive(idx + 1);
      if(e.key === '+') zoomBy(0.25);
      if(e.key === '-') zoomBy(-0.25);
    });

    // wheel zoom
    lb.addEventListener('wheel', (e)=>{
      if(lb.hasAttribute('hidden')) return;
      e.preventDefault();
      const delta = e.deltaY > 0 ? -0.15 : 0.15;
      zoomBy(delta);
    }, {passive:false});

    // double click toggle zoom
    if(lbImg){
      lbImg.addEventListener('dblclick', (e)=>{
        e.preventDefault();
        if(scale <= 1.05) { scale = 2; } else { resetZoom(); }
        applyTransform();
      });
    }

    // pan (drag) using pointer events
    function pointerDown(e){
      if(lb.hasAttribute('hidden')) return;
      if(scale <= 1.01) return; // ซูมก่อนแล้วค่อยลาก
      isPanning = true;
      startX = e.clientX - tx;
      startY = e.clientY - ty;
      lbImg && lbImg.setPointerCapture?.(e.pointerId);
    }
    function pointerMove(e){
      if(!isPanning) return;
      tx = e.clientX - startX;
      ty = e.clientY - startY;
      applyTransform();
    }
    function pointerUp(){ isPanning = false; }

    if(stage){
      stage.addEventListener('pointerdown', pointerDown);
      stage.addEventListener('pointermove', pointerMove);
      stage.addEventListener('pointerup', pointerUp);
      stage.addEventListener('pointercancel', pointerUp);
      stage.addEventListener('pointerleave', pointerUp);
    }

    // pinch zoom for touch
    function dist(t1, t2){
      const dx = t2.clientX - t1.clientX;
      const dy = t2.clientY - t1.clientY;
      return Math.hypot(dx, dy);
    }

    if(stage){
      stage.addEventListener('touchstart', (e)=>{
        if(e.touches.length === 2){
          pinchStartDist = dist(e.touches[0], e.touches[1]);
          pinchStartScale = scale;
        }
      }, {passive:true});

      stage.addEventListener('touchmove', (e)=>{
        if(e.touches.length === 2 && pinchStartDist){
          e.preventDefault();
          const d = dist(e.touches[0], e.touches[1]);
          const ratio = d / pinchStartDist;
          scale = pinchStartScale * ratio;
          applyTransform();
        }
      }, {passive:false});

      stage.addEventListener('touchend', ()=>{
        pinchStartDist = null;
      }, {passive:true});
    }
  }

  // ===== Swipe (mobile) =====
  function bindSwipe(el){
    if(!el) return;
    let x0=null, y0=null;

    el.addEventListener('touchstart', (e)=>{
      const t=e.touches[0]; x0=t.clientX; y0=t.clientY;
    }, {passive:true});

    el.addEventListener('touchend', (e)=>{
      if(x0===null||y0===null) return;
      const t=e.changedTouches[0];
      const dx=t.clientX-x0; const dy=t.clientY-y0;
      x0=y0=null;

      if(Math.abs(dx) < 30 || Math.abs(dx) < Math.abs(dy)) return;
      if(dx < 0) setActive(idx + 1);
      else setActive(idx - 1);
    }, {passive:true});
  }

  bindSwipe(main);
  if(lbImg) bindSwipe(lbImg);

  // init
  setActive(0);
})();
