// gestion du slider block
(function () {
  const initGallerySliders = function () {
    const galleries = document.querySelectorAll('.galerie-slider');

    galleries.forEach(function (gallery) {
      const mainEl = gallery.querySelector('.main-slider');
      const thumbEl = gallery.querySelector('.thumb-slider');

      if (!mainEl || !thumbEl) {
        return;
      }

      const mainSlides = mainEl.querySelectorAll('.swiper-slide');
      const thumbSlides = thumbEl.querySelectorAll('.swiper-slide');

      if (!mainSlides.length || !thumbSlides.length) {
        return;
      }

      const thumbSwiper = new Swiper(thumbEl, {
        spaceBetween: 10,
        slidesPerView: Math.min(3, thumbSlides.length),
        slidesPerGroup: 1,
        freeMode: false,
        watchSlidesProgress: true,
        watchOverflow: true,
        loop: true,
        grabCursor: true,
      });

      const mainSwiper = new Swiper(mainEl, {
        spaceBetween: 0,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        loop: mainSlides.length > 1,
        thumbs: {
          swiper: thumbSwiper,
        },
      });

      gallery.addEventListener('mouseenter', function () {
        mainSwiper.autoplay.stop();
      });

      gallery.addEventListener('mouseleave', function () {
        mainSwiper.autoplay.start();
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGallerySliders);
  } else {
    initGallerySliders();
  }
})();