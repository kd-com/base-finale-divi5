document.addEventListener('DOMContentLoaded', function () {

  // ── Injection CSS ───────────────────────────────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    .wp-block-gallery.is-layout-flex {
      display: block !important;
      flex-wrap: unset !important;
      flex-direction: unset !important;
      align-items: unset !important;
      justify-content: unset !important;
      gap: 0 !important;
    }
    .wp-block-gallery {
      position: relative !important;
      width: 100% !important;
    }
    .wp-block-gallery .wp-block-image {
      position: absolute !important;
      margin: 0 !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    .wp-block-gallery .wp-block-image img {
      display: block !important;
      width: 100% !important;
      height: auto !important;
      object-fit: unset !important;
    }
  `;
  document.head.appendChild(style);

  // ── Variables ───────────────────────────────────────────────────────────
  const GAP = 12;

  // ── Helpers ─────────────────────────────────────────────────────────────
  function getCols(gallery) {
    for (let i = 8; i >= 1; i--) {
      if (gallery.classList.contains('columns-' + i)) return i;
    }
    return 3;
  }

  // ── Layout ──────────────────────────────────────────────────────────────
  function doLayout(gallery) {
    const items = Array.from(gallery.querySelectorAll('.wp-block-image'));
    if (!items.length) return;

    let cols = getCols(gallery);
    if (window.innerWidth <= 480) cols = 1;
    else if (window.innerWidth <= 768) cols = Math.min(cols, 2);

    const totalWidth = gallery.offsetWidth;
    const colWidth   = (totalWidth - GAP * (cols - 1)) / cols;
    const colHeights = new Array(cols).fill(0);

    items.forEach(function (item) {
      const img        = item.querySelector('img');
      const imgW       = parseInt(img.getAttribute('width'))  || 1;
      const imgH       = parseInt(img.getAttribute('height')) || 1;
      const itemHeight = Math.round(colWidth * imgH / imgW);

      const minH     = Math.min(...colHeights);
      const colIndex = colHeights.indexOf(minH);

      item.style.width = colWidth + 'px';
      item.style.left  = (colIndex * (colWidth + GAP)) + 'px';
      item.style.top   = colHeights[colIndex] + 'px';

      colHeights[colIndex] += itemHeight + GAP;
    });

    gallery.style.height = Math.max(...colHeights) + 'px';
  }

  // ── Init ────────────────────────────────────────────────────────────────
  function initAll() {
    document.querySelectorAll('.wp-block-gallery').forEach(doLayout);
    document.dispatchEvent(new CustomEvent('masonry:ready'));
  }

  initAll();

  // ── Resize ──────────────────────────────────────────────────────────────
  let resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(initAll, 150);
  });

});