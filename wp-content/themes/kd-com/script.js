console.log("%cMade with %c❤️️ %cby %ckd-com %cfrom 🇫🇷 %chttps://www.kd-com.fr"," color: #3db27c"," color: #e84448"," color: #3db27c"," color: #e84448","color: #3db27c","color: #e84448");

    var siteDomain = String(window.location.host).replace(/^(https?:\/\/)?(www\.)?/,'');

// Initialisation de AOS (Animate On Scroll)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof kdComOptions !== 'undefined' && kdComOptions.aosEnabled && typeof AOS !== 'undefined') {
        AOS.init();
    }
});
/**
 * Auto-injection du SVG flèche pour les éléments avec la classe u-link-arrow
 * Supporte les liens, boutons standards et boutons Divi
 */

(function() {
    'use strict';

    // Template SVG de la flèche
    const arrowSVG = `<svg class="arrow-icon" width="22" height="22" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
        <g fill="none" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
        </g>
    </svg>`;

    /**
     * Ajoute le SVG à un élément s'il n'existe pas déjà
     */
    function addArrowSVG(element) {
        // Vérifier si le SVG existe déjà
        if (element.querySelector('.arrow-icon')) {
            return;
        }

        // Calculer la couleur actuelle du texte de l'élément AVANT d'ajouter le SVG
        const computedStyle = window.getComputedStyle(element);
        const textColor = computedStyle.color;

        // Ajouter le SVG à l'élément
        element.insertAdjacentHTML('beforeend', arrowSVG);
        
        // Maintenant récupérer les éléments SVG qui sont dans le DOM
        const svgElement = element.querySelector('.arrow-icon');
        if (!svgElement) {
            return;
        }

        // Forcer le stroke sur les éléments SVG avec la couleur calculée
        const gElement = svgElement.querySelector('g');
        if (gElement) {
            gElement.setAttribute('stroke', textColor);
            gElement.setAttribute('fill', 'none');
        }
        
        // Forcer aussi sur le cercle et le path individuellement
        const circle = svgElement.querySelector('circle');
        const path = svgElement.querySelector('path');
        if (circle) {
            circle.setAttribute('stroke', textColor);
        }
        if (path) {
            path.setAttribute('stroke', textColor);
        }
    }

    /**
     * Traite tous les éléments avec la classe u-link-arrow
     */
    function processArrowElements() {
        
        // Sélectionner tous les éléments avec la classe u-link-arrow
        // Inclut les liens, boutons standards, et boutons Divi
        const selectors = [
            'a.u-link-arrow',
            'button.u-link-arrow',
            '.wp-block-button__link.u-link-arrow',
            '.wp-element-button.u-link-arrow',
            '.et_pb_button.u-link-arrow',
            '.et_pb_custom_button.u-link-arrow',
            '.et_pb_promo_button.u-link-arrow',
            '.btn.u-link-arrow',
            '.btn-link-arrow'
        ];

        // Traiter dans le document principal
        const elements = document.querySelectorAll(selectors.join(', '));
        
        elements.forEach(function(element) {
            addArrowSVG(element);
        });

        // Cas spécial : Gutenberg avec .u-link-arrow sur le wrapper
        const wrapperSelectors = [
            '.wp-block-button.u-link-arrow',
            'div.u-link-arrow',
            'span.u-link-arrow'
        ];

        wrapperSelectors.forEach(function(selector) {
            const wrappers = document.querySelectorAll(selector);
            
            wrappers.forEach(function(wrapper) {
                // Chercher le lien ou bouton enfant (peut être profond dans l'éditeur)
                let link = wrapper.querySelector('a, button');
                
                // Si pas trouvé, chercher plus profondément (pour l'éditeur Gutenberg)
                if (!link) {
                    link = wrapper.querySelector('.wp-block-button__link, .wp-element-button, a.wp-block-button__link');
                }
                
                // Si toujours pas trouvé, chercher n'importe quel lien dans les descendants
                if (!link) {
                    const allLinks = wrapper.querySelectorAll('a, button, [role="textbox"].wp-block-button__link');
                    if (allLinks.length > 0) {
                        link = allLinks[0]; // Prendre le premier lien trouvé
                    }
                }
                
                if (link) {
                    addArrowSVG(link);
                }
            });
        });

        // TRAITER L'IFRAME DE L'ÉDITEUR GUTENBERG (si elle existe)
        const editorIframe = document.querySelector('iframe[name="editor-canvas"]');
        if (editorIframe) {
            try {
                const iframeDoc = editorIframe.contentDocument || editorIframe.contentWindow.document;
                
                // Traiter les éléments directs dans l'iframe
                const iframeElements = iframeDoc.querySelectorAll(selectors.join(', '));
                
                iframeElements.forEach(function(element) {
                    addArrowSVG(element);
                });

                // Traiter les wrappers dans l'iframe
                wrapperSelectors.forEach(function(selector) {
                    const iframeWrappers = iframeDoc.querySelectorAll(selector);
                    iframeWrappers.forEach(function(wrapper) {
                        const link = wrapper.querySelector('a, button');
                        if (link) {
                            addArrowSVG(link);
                        }
                    });
                });
            } catch (e) {
                // Erreur d'accès à l'iframe (CORS ou autre)
            }
        }
    }

    /**
     * Observer pour les éléments ajoutés dynamiquement (Gutenberg, Divi Builder, etc.)
     */
    function initMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                // Vérifier les nouveaux nœuds ajoutés
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Vérifier si le nœud lui-même a la classe
                        if (node.classList && node.classList.contains('u-link-arrow')) {
                            // Si c'est un lien/bouton direct
                            if (node.tagName === 'A' || node.tagName === 'BUTTON') {
                                addArrowSVG(node);
                            } else {
                                // Si c'est un wrapper, chercher le lien enfant
                                const link = node.querySelector('a, button, .wp-block-button__link');
                                if (link) {
                                    addArrowSVG(link);
                                }
                            }
                        }
                        // Vérifier les descendants
                        const childElements = node.querySelectorAll ? node.querySelectorAll('.u-link-arrow') : [];
                        childElements.forEach(function(child) {
                            if (child.tagName === 'A' || child.tagName === 'BUTTON') {
                                addArrowSVG(child);
                            } else {
                                // Si c'est un wrapper, chercher le lien enfant
                                const link = child.querySelector('a, button, .wp-block-button__link');
                                if (link) {
                                    addArrowSVG(link);
                                }
                            }
                        });
                    }
                });
            });
        });

        // Observer le body pour tous les changements
        const targetNode = document.body || document.documentElement;
        if (targetNode) {
            observer.observe(targetNode, {
                childList: true,
                subtree: true
            });
        }
        
        // Observer aussi l'iframe de l'éditeur Gutenberg
        const observeIframe = function() {
            const editorIframe = document.querySelector('iframe[name="editor-canvas"]');
            if (editorIframe) {
                try {
                    const iframeDoc = editorIframe.contentDocument || editorIframe.contentWindow.document;
                    const iframeBody = iframeDoc.body || iframeDoc.documentElement;
                    
                    if (iframeBody) {
                        observer.observe(iframeBody, {
                            childList: true,
                            subtree: true
                        });
                    }
                } catch (e) {
                    // Erreur d'accès à l'iframe
                }
            }
        };
        
        // Essayer d'observer l'iframe immédiatement et après un délai
        observeIframe();
        setTimeout(observeIframe, 1000);
        setTimeout(observeIframe, 3000);
    }

    /**
     * Initialisation
     */
    function init() {
        // Traiter les éléments existants au chargement
        processArrowElements();

        // Initialiser l'observer pour les éléments dynamiques
        initMutationObserver();
        
        // Re-traiter périodiquement pour Gutenberg (éditeur)
        // Nécessaire car l'éditeur utilise une iframe qui charge le contenu de façon asynchrone
        const checkInterval = setInterval(function() {
            processArrowElements();
        }, 1000);
        
        // Arrêter après 30 secondes pour ne pas surcharger
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 30000);
    }

    // Lancer l'initialisation quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-traiter après le chargement complet (pour les éléments chargés tardivement)
    window.addEventListener('load', function() {
        processArrowElements();
    });

    // Support pour Divi Builder (événement custom)
    document.addEventListener('et_builder_api_ready', processArrowElements);
    
    // Support pour Gutenberg (événement custom)
    if (window.wp && window.wp.domReady) {
        window.wp.domReady(function() {
            processArrowElements();
        });
    }
    
    // Support pour Gutenberg - attendre que l'éditeur soit prêt
    if (window.wp && window.wp.data) {
        let processCount = 0;
        const unsubscribe = window.wp.data.subscribe(function() {
            processCount++;
            processArrowElements();
            
            // Arrêter après 50 exécutions pour ne pas surcharger
            if (processCount > 50) {
                unsubscribe();
            }
        });
    }
    
    // Support pour les blocs Gutenberg ajoutés/modifiés
    if (window.wp && window.wp.hooks) {
        window.wp.hooks.addAction('blocks.setSaveContent', 'auto-arrow-svg', function() {
            setTimeout(processArrowElements, 100);
        });
    }

})();

// blog actu
jQuery(document).ready(function($) {

    // wrap blog article elements on archive pages
    $('.et_pb_post').contents().filter(function() {
        return this.nodeType == 3 && $.trim(this.nodeValue).length;
        })
        .wrap('<p class="post-content">');

    $('.blog .et_pb_post, .archive .et_pb_post, .search .et_pb_post').each(function () {
        $('>:not(a.entry-featured-image-url)', this).wrapAll('<div class="blog_info"></div>');
    });



});
// positionnement des ancres avec le décrocher du logo
(function($) {
  
    const headerHeight = $('header').outerHeight();
    
    $('.btn.menu-item').on('click', function(e) {
      e.preventDefault();
      let target = $(this).attr('href');
      $('html, body').animate( {
        scrollTop: $(target).offset(80).top - headerHeight
      }, 800);
      return false;
    })
    
  })

// affichage info-box hover 
var $box = $('.info-box');
if ($box.length) {
    $box.find('.info-box__hidden').slideUp('0');
    $('.info-box').hover(function() {
        $(this).find('.info-box__hidden').stop().slideDown('70');
    }, function() {
        $(this).find('.info-box__hidden').stop().slideUp('70').removeAttr("style");
    });
};

// mobile menu

jQuery(document).ready(function($){
    function ds_setup_collapsible_submenus_parent_cickable() {
        var top_level_link = '.et_mobile_menu .menu-item-has-children > a';
        $(top_level_link).after('<span class="menu-closed"></span>');
        $(top_level_link).each(function () {
            $(this).next().next('.sub-menu').toggleClass('menu-hide', 1000);
        });
        $(top_level_link + '+ span').on('click', function (event) {
                event.preventDefault();
                $(this).toggleClass('menu-open');
                $(this).next('.sub-menu').toggleClass('menu-hide', 1000);
            });
        }
    
        setTimeout(function () {
        ds_setup_collapsible_submenus_parent_cickable();
        }, 300);
});












// cta expand
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);
  
    (function($){
        let $body = $("body"),
            $highlightable = $(".et_pb_section.dvcs-highlightable"),
            highlighted_class = "dvcs-highlighted",
            highlighted_hidden_class = "dvcs-highlighted-hidden";
            
        if ($highlightable.length) {
            $(window).scroll($.throttle(100, function() {
                var scroll = $(window).scrollTop(),
                    itemHeight = $highlightable.height();
                highlightStart = $highlightable.offset().top - 600;
                highlightEnd = $highlightable.offset().top + itemHeight / 4;
                if (scroll > highlightStart & scroll < highlightEnd) {
                    $highlightable.addClass(highlighted_class);
                    $body.addClass(highlighted_hidden_class);
                } else {
                    $highlightable.removeClass(highlighted_class);
                    $body.removeClass(highlighted_hidden_class);
                }
                if ($(window).scrollTop() + $(window).height() > $(document).height() - 50) {
                    $highlightable.removeClass(highlighted_class);
                    $body.removeClass(highlighted_hidden_class);
                }
            }));
        }
    })(jQuery);


    // load more article sur les page archives
    jQuery(document).ready(function(){
        //For mobile Screens
        if (window.matchMedia('(max-width: 767px)').matches) {
            var initial_show_article = 2;
        var article_reveal = 2;
        jQuery(".pa-blog-load-more article").not( ":nth-child(-n+"+initial_show_article+")" ).css("display","none");
        jQuery("#pa_load_more").on("click", function(event){
            event.preventDefault();
            initial_show_article = initial_show_article + article_reveal;
            jQuery(".pa-blog-load-more article").css("display","block");
            jQuery(".pa-blog-load-more article").not( ":nth-child(-n+"+initial_show_article+")" ).css("display","none");
            var articles_num = jQuery(".pa-blog-load-more article").not('[style*="display: block"]').length
                if(articles_num == 0){
                jQuery(this).addClass("endinfinite");
                }   
        })
        } else {
            //For desktop Screens
            var initial_row_show = 4;
            var row_reveal = 4;
            var total_articles = jQuery(".pa-blog-load-more article").length;
            jQuery(".pa-blog-load-more article").not( ":nth-child(-n+"+initial_row_show+")" ).css("display","none");
            
            jQuery("#pa_load_more").on("click", function(event){
                event.preventDefault();
                initial_row_show = initial_row_show + row_reveal;
                jQuery(".pa-blog-load-more article").css("display","block");
                jQuery(".pa-blog-load-more article").not( ":nth-child(-n+"+initial_row_show+")" ).css("display","none");
                var articles_num = jQuery(".pa-blog-load-more article").not('[style*="display: block"]').length
                if(articles_num == 0){
                    jQuery(this).addClass("endinfinite");   
                }    
            })
        } 
    })

  // compteur animé
    $.fn.countTo = function(options) {
    return this.each(function() {
      //-- Arrange
      var FRAME_RATE = 60; // Predefine default frame rate to be 60fps
      var $el = $(this);
      var countFrom = parseInt($el.attr('data-count-from'));
      var countTo = parseInt($el.attr('data-count-to'));
      var countSpeed = $el.attr('data-count-speed'); // Number increment per second

      //-- Action
      var rafId;
      var increment;
      var currentCount = countFrom;
      var countAction = function() {              // Self looping local function via requestAnimationFrame
        if(currentCount < countTo) {              // Perform number incremeant
          $el.text(Math.floor(currentCount));     // Update HTML display
          increment = countSpeed / FRAME_RATE;    // Calculate increment step
          currentCount += increment;              // Increment counter
          rafId = requestAnimationFrame(countAction);
        } else {                                  // Terminate animation once it reaches the target count number
          $el.text(countTo);                      // Set to the final value before everything stops
          //cancelAnimationFrame(rafId);
        }
      };
      rafId = requestAnimationFrame(countAction); // Initiates the looping function
    });
  };
(jQuery);

//-- Executing
$('.num').countTo();

// if($(".swiper-slide.actu").length == 1) {
//     $('.swiper-wrapper.actu').addClass( "disabled" );
//     $('.swiper-pagination.actu').addClass( "disabled" );
//     $('.upk-salf-nav-pag-wrap.actu').addClass("disabled");
//     $('.swiper-button-next.actu').addClass('disabled');
//     $('.swiper-button-prev.actu').addClass('disabled');
//     $('.container.hero.actu').addClass('disabled');
// }
// var swiperActu = new Swiper("#slider-actu", {
//     grabCursor: true,
//     centeredSlides: false,
//     slidesPerView: "auto",
//     spaceBetween: 30,
//     loop: true,
//     slidesPerView: 1,
//     navigation: {
//         nextEl: '.upk-button-next',
//         prevEl: '.upk-button-prev',
//     },
    
//     keyboard: {
//       enabled: true
//     },
   
//     breakpoints: {
//       560: {
//         slidesPerView: 1
//       },
//       768: {
//         slidesPerView: 1
//       },
//       1024: {
//         slidesPerView: 1
//       }
//     }
//   });

//   let slides = document.querySelectorAll(".swiper-slide");

  
  
//   if($(".swiper-slide.zoom").length == 1) {
//     $('.swiper-wrapper.zoom').addClass( "disabled" );
//     $('.swiper-pagination.zoom').addClass( "disabled" );
//     $('.upk-salf-nav-pag-wrap.zoom').addClass("disabled");
//     $('.swiper-button-next.zoom').addClass('disabled');
//     $('.swiper-button-prev.zoom').addClass('disabled');
//     $('.container.hero.zoom').addClass('disabled');
// }
//   var swiperZoom = new Swiper("#slider-zoom", {
//     grabCursor: true,
//     centeredSlides: false,
//     slidesPerView: "auto",
//     spaceBetween: 30,
//     loop: true,
//     slidesPerView: 1,
//     navigation: {
//         nextEl: '.upk-button-next',
//         prevEl: '.upk-button-prev',
//     },
//     keyboard: {
//       enabled: true
//     },
    
//     breakpoints: {
//       560: {
//         slidesPerView: 1
//       },
//       768: {
//         slidesPerView: 1
//       },
//       1024: {
//         slidesPerView: 1
//       }
//     }
//   });

//   let slidesZoom = document.querySelectorAll(".swiper-slide");

//   var swiperChiffre = new Swiper("#slider-chiffre", {
//     grabCursor: true,
//     centeredSlides: false,
//     slidesPerView: "auto",
//     spaceBetween: 30,
//     loop: true,
//     slidesPerView: 1,
//     autoplay: {
//         disableOnInteraction: false,
//         pauseOnMouseEnter: true,
//         delay: 2000,
//         },
//         speed: 4000,
//     keyboard: {
//       enabled: true
//     },
//     mousewheel: {
//       thresholdDelta: 70
//     },
//     breakpoints: {
//       560: {
//         slidesPerView: 1
//       },
//       768: {
//         slidesPerView: 1
//       },
//       1024: {
//         slidesPerView: 1
//       }
//     }
//   });

  jQuery(function($){
    $('#categoryfilter') && $('#secteurfilter').on('change', function () {
      $('#send').prop('disabled', !$(this).val());
  }).trigger('change');
  $('#secteurfilter').on('change', function () {
    $('#send').prop('disabled', !$(this).val());
}).trigger('change');
	$('#filter-2').submit(function(){
		var filter = $('#filter-2');
		$.ajax({
			url:filter.attr('action'),
			data:filter.serialize(), // form data
			type:filter.attr('method'), // POST
			beforeSend:function(xhr){
				filter.find('button').text('Recherche...'); // changing the button label
			},
			success:function(data){
				filter.find('button').text('Filtrer'); // changing the button label back
				$('#response').html(data); // insert data
                filter.find('a').removeClass('disable');
			}
		});
		return false;
	});
});

// masquer chapeau si vide, uniquement si sous-pages activé
if (typeof window.sousPagesOption !== 'undefined' ? window.sousPagesOption === '1' : (typeof sous_pages_option !== 'undefined' ? sous_pages_option === '1' : false)) {
  var div = document.getElementById('chapeau');
  if (div && div.innerHTML.trim() === '') {
    div.style.display = 'none';
  }
}

jQuery(document).ready(function($){
    // Initialisation Swiper uniquement si un slider est présent
    if ($('.swiper').length) {
        var mySwiper = new Swiper('.swiper', {
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            // autres options Swiper à adapter selon vos besoins
        });
    }
});

// HERO SLIDER conditionné et Swiper chargé
jQuery(document).ready(function($){
    if ($('.swiper-slide.hero').length) {
        var waitForSwiperHero = setInterval(function() {
            if (typeof Swiper !== 'undefined') {
                clearInterval(waitForSwiperHero);
                if ($('.swiper-slide.hero').length == 1 || $('.swiper-slide.hero.video').length == 1) {
                    $('.swiper-wrapper.hero').addClass( "disabled" );
                    $('.swiper-pagination.hero').addClass( "disabled" );
                    $('.upk-salf-nav-pag-wrap.hero').addClass("disabled");
                    $('.swiper-button-next.hero').addClass('disabled');
                    $('.swiper-button-prev.hero').addClass('disabled');
                    $('.container.hero').addClass('disabled');
                }
                var menu = [];
                jQuery('.swiper-slide.hero').each( function(index){
                    menu.push( jQuery(this).find('.slide-inner').attr("data-text") );
                });
                var interleaveOffset = 0.5;
                var swiperOptions = {
                    loop: true,
                    speed: 1000,
                    parallax: true,
                    autoplay: {
                        delay: 6500,
                        disableOnInteraction: false,
                    },
                    watchSlidesProgress: true,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                        renderBullet: function(index, className) {
                            return '<span class="' + className + ' swiper-pagination-bullet--svg-animation"><svg width="28" height="28" viewBox="0 0 28 28"><circle class="svg__circle" cx="14" cy="14" r="10" fill="none" stroke-width="2"></circle><circle class="svg__circle-inner" cx="14" cy="14" r="2" stroke-width="3"></circle></svg></span>';
                        },
                    },
                    navigation: {
                        nextEl: '.upk-button-next',
                        prevEl: '.upk-button-prev',
                    },
                    on: {
                        progress: function() {
                            var swiper = this;
                            for (var i = 0; i < swiper.slides.length; i++) {
                                var slideProgress = swiper.slides[i].progress;
                                var innerOffset = swiper.width * interleaveOffset;
                                var innerTranslate = slideProgress;
                                swiper.slides[i].querySelector(".slide-inner").style.transform =
                                "translate3d(" + innerTranslate + "px, 0, 0)";
                            }      
                        },
                        touchStart: function() {
                          var swiper = this;
                          for (var i = 0; i < swiper.slides.length; i++) {
                            swiper.slides[i].style.transition = "";
                          }
                        },
                        setTransition: function(speed) {
                            var swiper = this;
                            for (var i = 0; i < swiper.slides.length; i++) {
                                swiper.slides[i].style.transition = speed + "ms";
                                swiper.slides[i].querySelector(".slide-inner").style.transition =
                                speed + "ms";
                            }
                        }
                    }
                };
                var swiper = new Swiper(".swiper-container", swiperOptions);
                // DATA BACKGROUND IMAGE
                var sliderBgSetting = $(".slide-bg-image");
                sliderBgSetting.each(function(indx){
                    if ($(this).attr("data-background")){
                        $(this).css("background-image", "url(" + $(this).data("background") + ")");
                    }
                });
            }
        }, 20);
    }
});

// slider logo conditionné et Swiper chargé
(function(){
    window.addEventListener('DOMContentLoaded', function(){
        let sliders = document.querySelectorAll('.logo-slider');
        if (sliders.length) {
            var waitForSwiperLogo = setInterval(function() {
                if (typeof Swiper !== 'undefined') {
                    clearInterval(waitForSwiperLogo);
                    sliders.forEach(function( slider ) {
                        swiper_init(slider)
                    });
                }
            }, 20);
        }
    });
      
    function swiper_init(slider){
         // configuration
         if(slider === null) return;
         if (slider.dataset.swiperInit) return;
         // extra controls
         let extraControls = '';
         // If we need pagination 
         extraControls += '';
         // If we need navigation buttons 
        extraControls += '';
        if (!slider.querySelector('.swiper-container-logo')) {
            slider.innerHTML = '<div class="swiper-container-logo" style="overflow:hidden">' + slider.innerHTML + '</div>' ;
        }

         // Wait for Swiper
        var waitForSwiper = setInterval( function () {
            if (typeof Swiper != "undefined") { 
                clearInterval(waitForSwiper);
                let carousel_container = slider.querySelector('.swiper-container-logo');
                const swiper = new Swiper( carousel_container , {
                    slidesPerView: 2, // mobile value
                    loop: true,
                    spaceBetween: 0, // mobile value
                    autoplay: {
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                    delay: 2000,
                    },
                    speed: 4000,
                    // If we need pagination
                    breakpoints: {
                    768: { // Tablet
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    981: { // Desktop
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    1321: { // desktop wide
                        slidesPerView: 4,
                        spaceBetween: 40,
                    }
                    }
                });
                slider.dataset.swiperInit = '1';
            }
        }, 20);
   }
})();

// Slider contenu (pages/articles) conditionné et Swiper chargé
jQuery(document).ready(function($){
    if ($('.slider-contenu-swiper').length) {
        var waitForSwiperContenu = setInterval(function() {
            if (typeof Swiper !== 'undefined') {
                clearInterval(waitForSwiperContenu);
                const swiperContenu = new Swiper('.slider-contenu-swiper', {
                    parallax: true,
                    speed: 1200,
                    effect: 'slide',
                    direction: 'vertical',
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    navigation: {
                        nextEl: '.upk-button-next-contenu',
                        prevEl: '.upk-button-prev-contenu',
                    },
                    pagination: {
                        el: '.slider-contenu-pagination',
                        clickable: true,
                        renderBullet: function(index, className) {
                            return '<span class="' + className + ' swiper-pagination-bullet--svg-animation"><svg width="28" height="28" viewBox="0 0 28 28"><circle class="svg__circle" cx="14" cy="14" r="10" fill="none" stroke-width="2"></circle><circle class="svg__circle-inner" cx="14" cy="14" r="2" stroke-width="3"></circle></svg></span>';
                        },
                    },
                    keyboard: {
                        enabled: true
                    },
                    mousewheel: {
                        enabled: true,
                        sensitivity: 1,
                    },
                    on: {
                        init: function () {
                            // Animation de la flèche de scroll
                            if($('.upk-page-scroll').length) {
                                $('.upk-page-scroll').addClass('active');
                            }
                        },
                    }
                });
            }
        }, 20);
    }
});

// Slider sous-pages conditionné par l'option WordPress
jQuery(document).ready(function($){
    var sousPagesActive = (typeof window.sousPagesOption !== 'undefined') ? window.sousPagesOption : (typeof sous_pages_option !== 'undefined' ? sous_pages_option : null);
    // Si l'option sous-pages n'est pas activée, on ne lance pas le slider
    if (sousPagesActive !== '1') return;

    // Slider actu
    if ($('.swiper-slide.actu').length) {
        var waitForSwiperActu = setInterval(function() {
            if (typeof Swiper !== 'undefined') {
                clearInterval(waitForSwiperActu);
                if ($('.swiper-slide.actu').length == 1) {
                    $('.swiper-wrapper.actu').addClass( "disabled" );
                    $('.swiper-pagination.actu').addClass( "disabled" );
                    $('.upk-salf-nav-pag-wrap.actu').addClass("disabled");
                    $('.swiper-button-next.actu').addClass('disabled');
                    $('.swiper-button-prev.actu').addClass('disabled');
                    $('.container.hero.actu').addClass('disabled');
                }
                var swiperActu = new Swiper("#slider-actu", {
                    grabCursor: true,
                    centeredSlides: false,
                    slidesPerView: "auto",
                    spaceBetween: 30,
                    loop: true,
                    slidesPerView: 1,
                    navigation: {
                        nextEl: '.upk-button-next',
                        prevEl: '.upk-button-prev',
                    },
                    keyboard: { enabled: true },
                    breakpoints: {
                        560: { slidesPerView: 1 },
                        768: { slidesPerView: 1 },
                        1024: { slidesPerView: 1 }
                    }
                });
            }
        }, 20);
    }

    // Slider zoom
    if ($('.swiper-slide.zoom').length) {
        var waitForSwiperZoom = setInterval(function() {
            if (typeof Swiper !== 'undefined') {
                clearInterval(waitForSwiperZoom);
                if ($('.swiper-slide.zoom').length == 1) {
                    $('.swiper-wrapper.zoom').addClass( "disabled" );
                    $('.swiper-pagination.zoom').addClass( "disabled" );
                    $('.upk-salf-nav-pag-wrap.zoom').addClass("disabled");
                    $('.swiper-button-next.zoom').addClass('disabled');
                    $('.swiper-button-prev.zoom').addClass('disabled');
                    $('.container.hero.zoom').addClass('disabled');
                }
                var swiperZoom = new Swiper("#slider-zoom", {
                    grabCursor: true,
                    centeredSlides: false,
                    slidesPerView: "auto",
                    spaceBetween: 30,
                    loop: true,
                    slidesPerView: 1,
                    navigation: {
                        nextEl: '.upk-button-next',
                        prevEl: '.upk-button-prev',
                    },
                    keyboard: { enabled: true },
                    breakpoints: {
                        560: { slidesPerView: 1 },
                        768: { slidesPerView: 1 },
                        1024: { slidesPerView: 1 }
                    }
                });
            }
        }, 20);
    }

    // Slider chiffre
    if ($('.swiper-slide.chiffre').length) {
        var waitForSwiperChiffre = setInterval(function() {
            if (typeof Swiper !== 'undefined') {
                clearInterval(waitForSwiperChiffre);
                var swiperChiffre = new Swiper("#slider-chiffre", {
                    grabCursor: true,
                    centeredSlides: false,
                    slidesPerView: "auto",
                    spaceBetween: 30,
                    loop: true,
                    slidesPerView: 1,
                    autoplay: {
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                        delay: 2000,
                    },
                    speed: 4000,
                    keyboard: { enabled: true },
                    mousewheel: { thresholdDelta: 70 },
                    breakpoints: {
                        560: { slidesPerView: 1 },
                        768: { slidesPerView: 1 },
                        1024: { slidesPerView: 1 }
                    }
                });
            }
        }, 20);
    }
});
// ajout de class sur les boutons forminator
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.forminator-button:not(.u-link-arrow)').forEach(function(btn) {
    btn.classList.add('u-link-arrow');
  });
});








