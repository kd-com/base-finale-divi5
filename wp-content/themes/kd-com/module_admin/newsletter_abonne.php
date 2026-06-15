<?php
/**
 * ========================================
 * FORMULAIRES ET GESTION DES ABONNÉS
 * ========================================
 */

// ============================================
// 1. SHORTCODE FORMULAIRE D'INSCRIPTION
// ============================================

add_shortcode('brevo_newsletter_form', 'brevo_newsletter_subscription_form');

function brevo_newsletter_subscription_form($atts) {
    // Récupérer les options sauvegardées
    $saved_options = get_option('brevo_form_options', array());

    // Valeurs par défaut
    $defaults = array(
        'title' => isset($saved_options['title']) ? $saved_options['title'] : 'Abonnez-vous à notre newsletter',
        'button_text' => isset($saved_options['button_text']) ? $saved_options['button_text'] : "S'abonner",
        'placeholder_email' => isset($saved_options['placeholder_email']) ? $saved_options['placeholder_email'] : 'Votre email',
        'placeholder_name' => isset($saved_options['placeholder_name']) ? $saved_options['placeholder_name'] : 'Votre Nom, Prénom (optionnel)',
        'success_message' => isset($saved_options['success_message']) ? $saved_options['success_message'] : 'Merci ! Vous êtes maintenant abonné à notre newsletter.',
        'show_name' => 'yes',
        'style' => 'default'
    );

    // Fusionner avec les attributs du shortcode
    $atts = shortcode_atts($defaults, $atts);

    ob_start();
    ?>
    <div class="brevo-newsletter-form-container brevo-style-<?php echo esc_attr($atts['style']); ?>">
        <span><i class="fa-regular fa-envelope"></i></span>
        <?php if (!empty($atts['title'])) : ?>
            <h3 class="brevo-form-title"><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>

        <?php if (!empty($saved_options['description'])) : ?>
            <div class="brevo-form-description">
                <?php echo wp_kses_post($saved_options['description']); ?>
            </div>
        <?php endif; ?>

        <form class="brevo-newsletter-form" method="post" action="">
            <?php wp_nonce_field('brevo_subscribe_action', 'brevo_subscribe_nonce'); ?>

            <div class="brevo-form-fields">
                <?php if ($atts['show_name'] === 'yes') : ?>
                    <div class="brevo-form-field">
                        <input type="text"
                               name="brevo_subscriber_name"
                               placeholder="<?php echo esc_attr($atts['placeholder_name']); ?>"
                               class="brevo-input brevo-input-name">
                    </div>
                <?php endif; ?>

                <div class="brevo-form-field">
                    <input type="email"
                           name="brevo_subscriber_email"
                           placeholder="<?php echo esc_attr($atts['placeholder_email']); ?>"
                           required
                           class="brevo-input brevo-input-email">
                </div>

                <div class="brevo-form-field">
                    <button type="submit" name="brevo_subscribe_submit" class="brevo-submit-button">
                        <?php echo html_entity_decode(esc_html($atts['button_text'])); ?>
                    </button>
                </div>
            </div>

            <div class="brevo-form-messages" style="display: none;"></div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.brevo-newsletter-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $messages = $form.find('.brevo-form-messages');
            var $button = $form.find('.brevo-submit-button');

            // Désactiver le bouton
            $button.prop('disabled', true).text('Envoi en cours...');
            $messages.hide().removeClass('brevo-message-success brevo-message-error');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: $form.serialize() + '&action=brevo_subscribe_ajax',
                success: function(response) {
                    if (response.success) {
                        $messages.addClass('brevo-message-success')
                                .html(response.data.message)
                                .slideDown();
                        $form.find('input[type="email"], input[type="text"]').val('');
                    } else {
                        $messages.addClass('brevo-message-error')
                                .html(response.data.message)
                                .slideDown();
                    }
                },
                error: function() {
                    $messages.addClass('brevo-message-error')
                            .html('Une erreur est survenue. Veuillez réessayer.')
                            .slideDown();
                },
                complete: function() {
                    $button.prop('disabled', false).text('<?php echo html_entity_decode(esc_js($atts['button_text'])); ?>');

                }
            });
        });
    });
    </script>
    <?php

    return ob_get_clean();
}


// ============================================
// 2. TRAITEMENT AJAX DE L'INSCRIPTION
// ============================================

add_action('wp_ajax_brevo_subscribe_ajax', 'brevo_subscribe_ajax_handler');
add_action('wp_ajax_nopriv_brevo_subscribe_ajax', 'brevo_subscribe_ajax_handler');

function brevo_subscribe_ajax_handler() {
    // Vérifier le nonce
    if (!isset($_POST['brevo_subscribe_nonce']) || 
        !wp_verify_nonce($_POST['brevo_subscribe_nonce'], 'brevo_subscribe_action')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité. Veuillez rafraîchir la page.'));
    }
    
    // Récupérer et valider l'email
    $email = sanitize_email($_POST['brevo_subscriber_email']);
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Adresse email invalide.'));
    }
    
    // Récupérer le nom (optionnel)
    $name = isset($_POST['brevo_subscriber_name']) ? sanitize_text_field($_POST['brevo_subscriber_name']) : '';
    
    // Ajouter à Brevo
    $result = brevo_add_subscriber($email, $name);
    
    if ($result['success']) {
        wp_send_json_success(array('message' => 'Merci ! Vous êtes maintenant abonné à notre newsletter.'));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}

// ============================================
// 3. FONCTIONS API BREVO POUR LES ABONNÉS
// ============================================

/**
 * Ajouter un abonné à la liste Brevo
 */
function brevo_add_subscriber($email, $name = '') {
    $config = get_brevo_config();
    
    if (!is_brevo_configured()) {
        return array(
            'success' => false,
            'message' => 'La newsletter n\'est pas configurée.'
        );
    }
    
    $url = 'https://api.brevo.com/v3/contacts';
    
    $data = array(
        'email' => $email,
        'listIds' => array(intval($config['list_id'])),
        'updateEnabled' => true
    );
    
    // Ajouter le nom si fourni
    if (!empty($name)) {
        // Séparer prénom et nom si possible
        $name_parts = explode(' ', $name, 2);
        $data['attributes'] = array(
            'FIRSTNAME' => $name_parts[0]
        );
        if (isset($name_parts[1])) {
            $data['attributes']['LASTNAME'] = $name_parts[1];
        }
    }
    
    $response = wp_remote_post($url, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key'],
            'content-type' => 'application/json'
        ),
        'body' => json_encode($data),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Erreur de connexion. Veuillez réessayer.'
        );
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code >= 200 && $status_code < 300) {
        return array(
            'success' => true,
            'message' => 'Abonnement réussi !',
            'data' => $body
        );
    } elseif ($status_code === 400 && isset($body['code']) && $body['code'] === 'duplicate_parameter') {
        return array(
            'success' => true,
            'message' => 'Vous êtes déjà abonné à notre newsletter.',
            'duplicate' => true
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Une erreur est survenue. Veuillez réessayer.',
            'error' => $body
        );
    }
}

/**
 * Désabonner un contact de la liste
 */
function brevo_unsubscribe_contact($email) {
    $config = get_brevo_config();
    
    if (!is_brevo_configured()) {
        return array(
            'success' => false,
            'message' => 'La newsletter n\'est pas configurée.'
        );
    }
    
    $url = 'https://api.brevo.com/v3/contacts/lists/' . $config['list_id'] . '/contacts/remove';
    
    $data = array(
        'emails' => array($email)
    );
    
    $response = wp_remote_post($url, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key'],
            'content-type' => 'application/json'
        ),
        'body' => json_encode($data),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Erreur de connexion.'
        );
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code >= 200 && $status_code < 300) {
        return array(
            'success' => true,
            'message' => 'Vous avez été désabonné avec succès.'
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Email introuvable ou déjà désabonné.'
        );
    }
}

// ============================================
// 4. PAGE DE DÉSABONNEMENT
// ============================================

add_action('template_redirect', 'brevo_handle_unsubscribe_page');

function brevo_handle_unsubscribe_page() {
    // Vérifier si on est sur la page de désabonnement
    if (!isset($_GET['brevo_unsubscribe'])) {
        return;
    }
    
    $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
    $action = isset($_POST['confirm_unsubscribe']) ? 'confirm' : 'form';
    
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Désinscription Newsletter - <?php bloginfo('name'); ?></title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
                background: #f5f5f5;
                margin: 0;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .unsubscribe-container {
                background: white;
                max-width: 500px;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            h1 {
                color: #264653;
                margin-top: 0;
            }
            .icon {
                font-size: 48px;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin: 15px 0;
            }
            .email {
                font-weight: bold;
                color: #264653;
            }
            .buttons {
                margin-top: 30px;
                display: flex;
                gap: 10px;
                justify-content: center;
            }
            .button {
                padding: 12px 24px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: 600;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }
            .button-primary {
                background: #dc3545;
                color: white;
            }
            .button-primary:hover {
                background: #c82333;
            }
            .button-secondary {
                background: #6c757d;
                color: white;
            }
            .button-secondary:hover {
                background: #5a6268;
            }
            .success-message {
                background: #d4edda;
                color: #155724;
                padding: 15px;
                border-radius: 4px;
                margin: 20px 0;
            }
            .error-message {
                background: #f8d7da;
                color: #721c24;
                padding: 15px;
                border-radius: 4px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="unsubscribe-container">
            <?php if ($action === 'confirm' && !empty($email)) : 
                // Traiter la désinscription
                check_admin_referer('brevo_unsubscribe_' . $email);
                $result = brevo_unsubscribe_contact($email);
                
                if ($result['success']) : ?>
                    <div class="icon">✓</div>
                    <h1>Désinscription confirmée</h1>
                    <div class="success-message">
                        <?php echo esc_html($result['message']); ?>
                    </div>
                    <p>Vous ne recevrez plus nos newsletters à l'adresse :<br>
                    <span class="email"><?php echo esc_html($email); ?></span></p>
                    <p>Nous sommes désolés de vous voir partir !</p>
                    <div class="buttons">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="button button-secondary">
                            Retour au site
                        </a>
                    </div>
                <?php else : ?>
                    <div class="icon">✗</div>
                    <h1>Erreur</h1>
                    <div class="error-message">
                        <?php echo esc_html($result['message']); ?>
                    </div>
                    <div class="buttons">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="button button-secondary">
                            Retour au site
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else : ?>
                <div class="icon">✉️</div>
                <h1>Désinscription de la newsletter</h1>
                
                <?php if (!empty($email)) : ?>
                    <p>Souhaitez-vous vraiment vous désabonner de notre newsletter ?</p>
                    <p>Email : <span class="email"><?php echo esc_html($email); ?></span></p>
                    
                    <form method="post">
                        <?php wp_nonce_field('brevo_unsubscribe_' . $email); ?>
                        <div class="buttons">
                            <button type="submit" name="confirm_unsubscribe" class="button button-primary">
                                Confirmer la désinscription
                            </button>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="button button-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="error-message">
                        Email manquant. Veuillez utiliser le lien fourni dans votre newsletter.
                    </div>
                    <div class="buttons">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="button button-secondary">
                            Retour au site
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}