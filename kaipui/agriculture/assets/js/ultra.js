(function(){
  const main = document.querySelector('[data-gallery-main]');
  const thumbs = document.querySelectorAll('[data-gallery-thumb]');
  if(main && thumbs.length){
    thumbs.forEach(t=>{
      t.addEventListener('click', ()=>{
        const src = t.getAttribute('data-src');
        if(src) main.setAttribute('src', src);
      });
    });
  }
})();
