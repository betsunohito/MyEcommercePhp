(function () {
  // Helpers
  function closestPolyfill(el, sel) {
    if (el.closest) return el.closest(sel);
    for (let n = el; n; n = n.parentElement) {
      if (n.matches && n.matches(sel)) return n;
    }
    return null;
  }
  function getGallery(el) {
    return (el.closest ? el.closest('.pd-product-pics') : closestPolyfill(el, '.pd-product-pics'));
  }
  function getMainBox(g) { return g ? g.querySelector('.pd-product__details__pic__item') : null; }
  function getMainImg(g) { const box = getMainBox(g); return box ? box.querySelector('img') : null; }

  function collect(g) {
    const slider = g.querySelector('.pd-product__details__pic__slider');
    const thumbs = slider ? Array.from(slider.querySelectorAll('img')) : [];
    const sources = [];
    const map = new Map();
    thumbs.forEach(img => {
      const src = img.dataset.imgbigurl || img.getAttribute('src');
      if (!src) return;
      if (!map.has(src)) {
        map.set(src, img);
        sources.push(src);
      }
      img.classList.remove('active');
    });
    return { sources, map, thumbs };
  }

  function normalizeUrl(s) {
    try { return new URL(s, location.href).href; } catch { return s; }
  }
  function findIndex(sources, currentSrc) {
    const cur = normalizeUrl(currentSrc);
    for (let i = 0; i < sources.length; i++) {
      if (normalizeUrl(sources[i]) === cur) return i;
    }
    return -1;
  }

  function show(g, idx, ctx) {
    if (!ctx.sources.length) return;
    const main = getMainImg(g);
    if (!main) return;

    idx = (idx + ctx.sources.length) % ctx.sources.length;
    const newSrc = ctx.sources[idx];

    // fade + preload + swap
    main.style.opacity = '0';
    const pre = new Image();
    pre.onload = function () {
      main.src = newSrc;
      main.dataset.imgbigurl = newSrc;
      main.onload = function () { main.style.opacity = '1'; };
    };
    pre.src = newSrc;

    // active thumb state
    ctx.thumbs.forEach(t => t.classList.remove('active'));
    const t = ctx.map.get(newSrc);
    if (t) {
      t.classList.add('active');
      try { t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }); } catch {}
    }

    g.dataset.idx = String(idx);
  }

  function step(el, delta) {
    const g = getGallery(el);
    if (!g) return;
    const ctx = collect(g);
    if (!ctx.sources.length) return;

    const main = getMainImg(g);
    const current = main ? (main.dataset.imgbigurl || main.src) : '';
    let idx = g.dataset.idx !== undefined ? parseInt(g.dataset.idx, 10) : findIndex(ctx.sources, current);
    if (isNaN(idx) || idx < 0) idx = 0;

    show(g, idx + delta, ctx);
  }

  // Expose globals for inline onclick
  window.nextPic = function (el) { step(el, +1); };
  window.prevPic = function (el) { step(el, -1); };
  window.selectPic = function (imgEl) {
    const g = getGallery(imgEl);
    if (!g) return;
    const ctx = collect(g);
    const src = imgEl.dataset.imgbigurl || imgEl.getAttribute('src');
    // Try direct index; fallback to normalized compare
    let i = ctx.sources.indexOf(src);
    if (i === -1) i = findIndex(ctx.sources, src);
    if (i === -1) i = 0;
    show(g, i, ctx);
  };
})();

document.addEventListener('click', function (e) {
  const btn = e.target.closest('.pd-product__details__quantity .qtybtn');
  if (!btn) return;

  const input = btn.parentElement.querySelector('input');
  let v = parseInt(input.value, 10) || 1;

  v += btn.classList.contains('inc') ? 1 : -1;
  if (v < 1) v = 1;           // minimum 1
  input.value = v;
});