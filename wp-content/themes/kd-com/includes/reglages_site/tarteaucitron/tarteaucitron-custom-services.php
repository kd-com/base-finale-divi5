<?php
/**
 * Services personnalisés pour Tarteaucitron
 * 
 * Ce fichier contient les définitions de services personnalisés
 * qui ne sont pas inclus par défaut dans Tarteaucitron.js
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajouter les services personnalisés Tarteaucitron dans le footer
 */
function kd_tarteaucitron_custom_services() {
    $options = get_option('kd_tarteaucitron_settings', array());
    
    if (empty($options['enabled'])) {
        return;
    }
    // Vérifier si reCAPTCHA est activé
    $recaptcha_enabled = !empty($options['services']['recaptcha']['enabled']);
    
    if ($recaptcha_enabled) {
        ?>
        <script type="text/javascript">
        // Service personnalisé pour reCAPTCHA avec message de fallback amélioré
        (function () {
            if (typeof tarteaucitron !== 'undefined' && tarteaucitron.services && tarteaucitron.services.recaptcha) {
                // Sauvegarder le fallback original
                var originalFallback = tarteaucitron.services.recaptcha.fallback;
                
                // Remplacer par notre fallback personnalisé
                tarteaucitron.services.recaptcha.fallback = function () {
                    "use strict";
                    
                    // Trouver tous les éléments reCAPTCHA
                    var captchaElements = document.querySelectorAll('.tarteaucitronrecaptcha');
                    
                    captchaElements.forEach(function(element) {
                        // Ne pas traiter si déjà un fallback
                        if (element.querySelector('.tac-recaptcha-fallback')) {
                            return;
                        }
                        
                        // Créer le message de fallback
                        var fallbackMsg = document.createElement('div');
                        fallbackMsg.className = 'tac-recaptcha-fallback';
                        fallbackMsg.style.cssText = 'padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 8px; text-align: center; color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-height: 78px; display: flex; flex-direction: column; align-items: center; justify-content: center;';
                        
                        fallbackMsg.innerHTML = '<p style="margin: 0 0 10px 0; font-weight: 600; font-size: 16px; color: #ffffff;">🍪 Protection anti-spam (reCAPTCHA)</p>' +
                            '<p style="margin: 0 0 15px 0; font-size: 14px; opacity: 0.95; color: #ffffff;">Pour soumettre ce formulaire, veuillez accepter les cookies Google reCAPTCHA.</p>' +
                            '<button onclick="tarteaucitron.userInterface.openPanel();" style="display: inline-block; padding: 10px 20px; background: #ffffff; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">Gérer mes cookies</button>';
                        
                        // Ajouter le message
                        element.appendChild(fallbackMsg);
                    });
                    
                    // Appeler le fallback original si nécessaire
                    if (originalFallback) {
                        originalFallback();
                    }
                };
            }
        })();
        </script>
        <?php
    }
    // Vérifier si reCAPTCHA est activé
    $recaptcha_enabled = !empty($options['services']['recaptcha']['enabled']);
    
    if ($recaptcha_enabled) {
        ?>
        <script type="text/javascript">
        // Service personnalisé pour reCAPTCHA avec message de fallback amélioré
        (function () {
            // Attendre que Tarteaucitron soit complètement chargé
            var checkTarteaucitron = setInterval(function() {
                if (typeof tarteaucitron !== 'undefined' && tarteaucitron.services && tarteaucitron.services.recaptcha) {
                    clearInterval(checkTarteaucitron);
                    
                    console.log('Tarteaucitron reCAPTCHA service détecté, ajout du fallback personnalisé...');
                    
                    // Sauvegarder le fallback original
                    var originalFallback = tarteaucitron.services.recaptcha.fallback;
                    
                    // Remplacer par notre fallback personnalisé
                    tarteaucitron.services.recaptcha.fallback = function () {
                        "use strict";
                        
                        console.log('Fallback reCAPTCHA personnalisé appelé');
                        
                        // Trouver tous les éléments reCAPTCHA
                        var captchaElements = document.querySelectorAll('.tarteaucitronrecaptcha');
                        console.log('Éléments reCAPTCHA trouvés:', captchaElements.length);
                        
                        captchaElements.forEach(function(element) {
                            // Ne pas traiter si déjà un fallback
                            if (element.querySelector('.tac-recaptcha-fallback')) {
                                console.log('Fallback déjà présent, skip');
                                return;
                            }
                            
                            console.log('Ajout du message de fallback...');
                            
                            // Créer le message de fallback
                            var fallbackMsg = document.createElement('div');
                            fallbackMsg.className = 'tac-recaptcha-fallback';
                            fallbackMsg.style.cssText = 'padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 8px; text-align: center; color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-height: 78px; display: flex; flex-direction: column; align-items: center; justify-content: center;';
                            
                            fallbackMsg.innerHTML = '<p style="margin: 0 0 10px 0; font-weight: 600; font-size: 16px; color: #ffffff;">🍪 Protection anti-spam (reCAPTCHA)</p>' +
                                '<p style="margin: 0 0 15px 0; font-size: 14px; opacity: 0.95; color: #ffffff;">Pour soumettre ce formulaire, veuillez accepter les cookies Google reCAPTCHA.</p>' +
                                '<button onclick="tarteaucitron.userInterface.openPanel();" style="display: inline-block; padding: 10px 20px; background: #ffffff; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">Gérer mes cookies</button>';
                            
                            // Ajouter le message
                            element.appendChild(fallbackMsg);
                            console.log('Message de fallback ajouté avec succès');
                        });
                        
                        // Appeler le fallback original si nécessaire
                        if (originalFallback) {
                            originalFallback();
                        }
                    };
                    
                    // Forcer l'appel du fallback immédiatement pour les captchas déjà présents
                    tarteaucitron.services.recaptcha.fallback();
                }
            }, 100);
        })();
        </script>
        <?php
    }
    
    // Vérifier si Google Fonts est activé
    $googlefonts_enabled = !empty($options['services']['googlefonts']['enabled']);
    
    if ($googlefonts_enabled) {
        ?>
        <script type="text/javascript">
        // Service personnalisé pour Google Fonts
        tarteaucitron.services.googlefonts = {
            "key": "googlefonts",
            "type": "api",
            "name": "Google Fonts",
            "uri": "https://policies.google.com/privacy",
            "needConsent": true,
            "cookies": ['__Secure-ENID'],
            "js": function () {
                "use strict";
                if (tarteaucitron.user.googleFontsApi === undefined) {
                    return;
                }
                
                // Créer et ajouter le lien CSS pour Google Fonts
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = tarteaucitron.user.googleFontsApi;
                link.type = 'text/css';
                document.head.appendChild(link);
            },
            "fallback": function () {
                "use strict";
                // Optionnel : charger des fonts de fallback locales
                var style = document.createElement('style');
                style.innerHTML = 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }';
                document.head.appendChild(style);
            }
        };
        </script>
        <?php
    }
}
add_action('wp_footer', 'kd_tarteaucitron_custom_services', 5);

/**
 * Ajouter un preconnect pour Google Fonts si le service est activé
 */
function kd_tarteaucitron_googlefonts_preconnect() {
    $options = get_option('kd_tarteaucitron_settings', array());
    
    if (empty($options['enabled'])) {
        return;
    }
    
    $googlefonts_enabled = !empty($options['services']['googlefonts']['enabled']);
    
    if ($googlefonts_enabled) {
        // Ajouter les preconnect seulement si l'utilisateur a donné son consentement
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier le consentement pour Google Fonts
            if (tarteaucitron.state.googlefonts === true) {
                // Ajouter preconnect si pas déjà présent
                if (!document.querySelector('link[href="https://fonts.googleapis.com"]')) {
                    var preconnect1 = document.createElement('link');
                    preconnect1.rel = 'preconnect';
                    preconnect1.href = 'https://fonts.googleapis.com';
                    document.head.appendChild(preconnect1);
                    
                    var preconnect2 = document.createElement('link');
                    preconnect2.rel = 'preconnect';
                    preconnect2.href = 'https://fonts.gstatic.com';
                    preconnect2.crossOrigin = 'anonymous';
                    document.head.appendChild(preconnect2);
                }
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'kd_tarteaucitron_googlefonts_preconnect', 10);