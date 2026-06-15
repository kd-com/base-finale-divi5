/**
 * Ajoute les attributs AOS aux modules Divi en fonction des classes CSS
 */
(function($) {
    'use strict';
    function addAOSAttributesToModule() {
        $('.et_pb_module, .et_pb_section, .et_pb_row').each(function() {
            const $module = $(this);
            const classes = $module.attr('class') || '';
            // Ne traiter que les effets AOS valides, ignorer aos-init et aos-animate
            const aosMatch = classes.match(/aos-(fade-up|fade-down|fade-left|fade-right|zoom-in|zoom-out|flip-left|flip-right|slide-up|slide-down|slide-left|slide-right|none)/);
            const delayMatch = classes.match(/aos-delay-(\d+)/);
            if (aosMatch) {
                const aosType = aosMatch[1];
                const aosDelay = delayMatch ? delayMatch[1] : '100';
                $module.attr('data-aos', aosType);
                if (aosDelay) {
                    $module.attr('data-aos-delay', aosDelay);
                }
                // Supprimer les classes aos-* et aos-delay-*, sauf aos-init et aos-animate
                $module.removeClass(function(idx, cls) {
                    return (cls.match(/aos-(fade-up|fade-down|fade-left|fade-right|zoom-in|zoom-out|flip-left|flip-right|slide-up|slide-down|slide-left|slide-right|none)/g) || []).concat(cls.match(/aos-delay-\d+/g) || []).join(' ');
                });
            }
        });
    }
    // Exécution après le chargement complet de la page
    $(window).on('load', function() {
        setTimeout(function() {
            addAOSAttributesToModule();
            if (typeof AOS !== 'undefined' && typeof AOS.refreshHard === 'function') {
                AOS.refreshHard();
            } else if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        }, 500);
    });
    // Exécution aussi après chaque scroll et resize (Divi peut recharger dynamiquement)
    $(window).on('scroll resize', function() {
        setTimeout(function() {
            addAOSAttributesToModule();
            if (typeof AOS !== 'undefined' && typeof AOS.refreshHard === 'function') {
                AOS.refreshHard();
            } else if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        }, 200);
    });
    // Visual Builder : intervalle régulier
    if (window.et_pb_preview_mode) {
        setInterval(function() {
            addAOSAttributesToModule();
            if (typeof AOS !== 'undefined' && typeof AOS.refreshHard === 'function') {
                AOS.refreshHard();
            } else if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        }, 2000);
    }
})(jQuery);
