document.addEventListener('DOMContentLoaded', function () {

  // ── Injection HTML ──────────────────────────────────────────────────────
  document.body.insertAdjacentHTML('beforeend', `
    <div id="kd-lightbox">
      <span id="kd-lightbox-close">&times;</span>
      <span id="kd-lightbox-prev">&#8249;</span>
      <img src="" alt="">
      <span id="kd-lightbox-next">&#8250;</span>
    </div>
  `);

  // ── Injection CSS ───────────────────────────────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    #kd-lightbox {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.92); z-index: 99999;
      cursor: zoom-out; align-items: center; justify-content: center;
    }
    #kd-lightbox.active { display: flex; }
    #kd-lightbox img {
      max-width: 90vw; max-height: 90vh; object-fit: contain;
      border-radius: 2px; box-shadow: 0 8px 60px rgba(0,0,0,0.6);
      transform: scale(0.95); opacity: 0;
      transition: transform 0.25s ease, opacity 0.25s ease;
    }
    #kd-lightbox.active img { transform: scale(1); opacity: 1; }
    #kd-lightbox-close {
      position: fixed; top: 20px; right: 28px; color: #fff;
      font-size: 36px; line-height: 1; cursor: pointer;
      opacity: 0.7; transition: opacity 0.2s; z-index: 100000; user-select: none;
    }
    #kd-lightbox-close:hover { opacity: 1; }
    #kd-lightbox-prev, #kd-lightbox-next {
      position: fixed; top: 50%; transform: translateY(-50%);
      color: #fff; font-size: 48px; cursor: pointer;
      opacity: 0.5; transition: opacity 0.2s;
      z-index: 100000; user-select: none; padding: 0 16px;
    }
    #kd-lightbox-prev { left: 12px; }
    #kd-lightbox-next { right: 12px; }
    #kd-lightbox-prev:hover, #kd-lightbox-next:hover { opacity: 1; }
    .wp-block-gallery .wp-block-image img { cursor: zoom-in; }
  `;
  document.head.appendChild(style);

  // ── Variables ───────────────────────────────────────────────────────────
  const lightbox    = document.getElementById('kd-lightbox');
  const lightboxImg = lightbox.querySelector('img');
  const closeBtn    = document.getElementById('kd-lightbox-close');
  const prevBtn     = document.getElementById('kd-lightbox-prev');
  const nextBtn     = document.getElementById('kd-lightbox-next');

  let images       = [];
  let currentIndex = 0;
  let initialized  = false;

  // ── Collecte des images ─────────────────────────────────────────────────
  function collectImages() {
    images = [];
    document.querySelectorAll('.wp-block-gallery .wp-block-image img').forEach(function (img) {
      images.push({
        src: img.getAttribute('data-full') || img.src,
        alt: img.alt
      });
      // Évite de doubler les listeners si collectImages() est appelé plusieurs fois
      img.replaceWith(img.cloneNode(true));
    });

    // Ré-attache les clicks sur les clones frais
    document.querySelectorAll('.wp-block-gallery .wp-block-image img').forEach(function (img, i) {
      img.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        currentIndex = i;
        openLightbox(currentIndex);
      });
    });

    initialized = true;
  }

  // ── Ouverture ───────────────────────────────────────────────────────────
  function openLightbox(index) {
    lightboxImg.style.opacity   = '0';
    lightboxImg.style.transform = 'scale(0.95)';
    lightbox.classList.add('active');
    lightboxImg.src = images[index].src;
    lightboxImg.alt = images[index].alt;

    lightboxImg.onload = function () {
      lightboxImg.style.opacity   = '1';
      lightboxImg.style.transform = 'scale(1)';
    };
    if (lightboxImg.complete) {
      lightboxImg.style.opacity   = '1';
      lightboxImg.style.transform = 'scale(1)';
    }
    document.body.style.overflow = 'hidden';
  }

  // ── Fermeture ───────────────────────────────────────────────────────────
  function closeLightbox() {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  // ── Navigation ──────────────────────────────────────────────────────────
  function showPrev() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    openLightbox(currentIndex);
  }
  function showNext() {
    currentIndex = (currentIndex + 1) % images.length;
    openLightbox(currentIndex);
  }

  // ── Événements UI ───────────────────────────────────────────────────────
  closeBtn.addEventListener('click', closeLightbox);
  prevBtn.addEventListener('click',  function (e) { e.stopPropagation(); showPrev(); });
  nextBtn.addEventListener('click',  function (e) { e.stopPropagation(); showNext(); });
  lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLightbox(); });
  document.addEventListener('keydown', function (e) {
    if (!lightbox.classList.contains('active')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  showPrev();
    if (e.key === 'ArrowRight') showNext();
  });

  // ── Initialisation : masonry présent ou non ─────────────────────────────
  // Cas 1 : masonry actif → on attend son signal
  document.addEventListener('masonry:ready', function () {
    collectImages();
  });

  // Cas 2 : pas de masonry → on init après un court délai
  setTimeout(function () {
    if (!initialized) collectImages();
  }, 300);

});