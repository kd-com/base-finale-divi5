<?php
/**
 * ========================================
 * PAGE CONFIGURATION NEWSLETTER
 * ========================================
 */

function brevo_newsletter_config_page() {
    // Traiter la sauvegarde
    if (isset($_POST['save_config']) && check_admin_referer('brevo_newsletter_config')) {
        update_option('brevo_api_key', sanitize_text_field($_POST['brevo_api_key']));
        update_option('brevo_list_id', sanitize_text_field($_POST['brevo_list_id']));
        update_option('brevo_sender_email', sanitize_email($_POST['brevo_sender_email']));
        update_option('brevo_sender_name', sanitize_text_field($_POST['brevo_sender_name']));
        
        echo '<div class="notice notice-success"><p>✅ Configuration sauvegardée avec succès !</p></div>';
    }
    
    // Traiter la sauvegarde des options du formulaire
    if (isset($_POST['save_form_options']) && check_admin_referer('brevo_form_options')) {
        $form_options = array(
            'title' => sanitize_text_field($_POST['form_title']),
            'description' => wp_kses_post($_POST['form_description']),
            'placeholder_email' => sanitize_text_field($_POST['placeholder_email']),
            'placeholder_name' => sanitize_text_field($_POST['placeholder_name']),
            'button_text' => sanitize_text_field($_POST['button_text']),
            'success_message' => sanitize_text_field($_POST['success_message'])
        );
        
        update_option('brevo_form_options', $form_options);
        echo '<div class="notice notice-success"><p>✅ Options du formulaire sauvegardées avec succès !</p></div>';
    }
    
    $config = get_brevo_config();
    $api_configured = is_brevo_configured();
    
    // Récupérer les options du formulaire
    $form_options = get_option('brevo_form_options', array(
        'title' => 'Abonnez-vous à notre newsletter',
        'description' => '',
        'placeholder_email' => 'Votre email',
        'placeholder_name' => 'Votre Nom, Prénom (optionnel)',
        'button_text' => "S'abonner",
        'success_message' => 'Merci ! Vous êtes maintenant abonné à notre newsletter.'
    ));
    
    // Tester la connexion API
    $api_test_result = null;
    if ($api_configured && isset($_GET['test_api'])) {
        $api_test_result = brevo_test_api_connection();
    }
    
    ?>
    <div class="wrap">
        <h1>🔑 Configuration de l'API Brevo</h1>
        
        <?php if ($api_test_result !== null) : ?>
            <div class="notice notice-<?php echo $api_test_result['success'] ? 'success' : 'error'; ?>">
                <p><?php echo esc_html($api_test_result['message']); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Configuration API -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">🔌 Connexion à Brevo</h2>
            
            <?php if (!$api_configured) : ?>
                <div class="notice notice-warning inline" style="margin: 15px 0;">
                    <p><strong>⚠️ Configuration requise</strong></p>
                </div>
            <?php else : ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p><strong>✅ Configuration complète</strong></p>
                    <p>
                        <a href="?page=brevo-newsletter-config&test_api=1" class="button button-secondary">
                            🔍 Tester la connexion API
                        </a>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('brevo_newsletter_config'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="brevo_api_key">Clé API Brevo *</label></th>
                        <td>
                            <input type="text" id="brevo_api_key" name="brevo_api_key" 
                                   value="<?php echo esc_attr($config['api_key']); ?>" 
                                   class="regular-text" required>
                            <p class="description">
                                📌 Trouvez votre clé dans Brevo : <strong>Paramètres → Clés API</strong><br>
                                <a href="https://app.brevo.com/settings/keys/api" target="_blank">Ouvrir Brevo →</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="brevo_list_id">ID de la liste d'abonnés *</label></th>
                        <td>
                            <input type="text" id="brevo_list_id" name="brevo_list_id" 
                                   value="<?php echo esc_attr($config['list_id']); ?>" 
                                   class="regular-text" required>
                            <p class="description">
                                📌 Exemple : <code>12</code> - Trouvez l'ID dans <strong>Contacts → Listes</strong><br>
                                <a href="https://app.brevo.com/contact/list" target="_blank">Ouvrir les listes →</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="brevo_sender_email">Email expéditeur *</label></th>
                        <td>
                            <input type="email" id="brevo_sender_email" name="brevo_sender_email" 
                                   value="<?php echo esc_attr($config['sender_email']); ?>" 
                                   class="regular-text" required>
                            <p class="description">
                                ⚠️ Cet email doit être vérifié dans Brevo
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="brevo_sender_name">Nom de l'expéditeur *</label></th>
                        <td>
                            <input type="text" id="brevo_sender_name" name="brevo_sender_name" 
                                   value="<?php echo esc_attr($config['sender_name']); ?>" 
                                   class="regular-text" required>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="save_config" class="button button-primary button-large">
                        💾 Sauvegarder la configuration
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Personnalisation du formulaire -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">🎨 Personnalisation du formulaire d'inscription</h2>
            
            <form method="post">
                <?php wp_nonce_field('brevo_form_options'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="form_title">Titre du formulaire :</label></th>
                        <td>
                            <input type="text" id="form_title" name="form_title"
                                   value="<?php echo esc_attr($form_options['title']); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="form_description">Texte de présentation :</label></th>
                        <td>
                            <?php
                            wp_editor(
                                $form_options['description'],
                                'form_description',
                                array(
                                    'textarea_name' => 'form_description',
                                    'textarea_rows' => 5,
                                    'teeny' => true,
                                    'media_buttons' => false
                                )
                            );
                            ?>
                            <p class="description">Texte qui apparaîtra sous le titre du formulaire.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="placeholder_email">Placeholder email :</label></th>
                        <td>
                            <input type="text" id="placeholder_email" name="placeholder_email"
                                   value="<?php echo esc_attr($form_options['placeholder_email']); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="placeholder_name">Placeholder nom :</label></th>
                        <td>
                            <input type="text" id="placeholder_name" name="placeholder_name"
                                   value="<?php echo esc_attr($form_options['placeholder_name']); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="button_text">Texte du bouton :</label></th>
                        <td>
                            <input type="text" id="button_text" name="button_text"
                                   value="<?php echo esc_attr($form_options['button_text']); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="success_message">Message de succès :</label></th>
                        <td>
                            <input type="text" id="success_message" name="success_message"
                                   value="<?php echo esc_attr($form_options['success_message']); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="save_form_options" class="button button-primary button-large">
                        💾 Sauvegarder les options du formulaire
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Aide -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px; background: #f8f9fa;">
            <h2 style="margin-top: 0;">📚 Guide de configuration</h2>
            
            <div style="padding: 15px; background: white; border-radius: 6px; margin-bottom: 15px;">
                <h3 style="margin-top: 0; font-size: 16px;">1️⃣ Créez un compte Brevo</h3>
                <p>Si vous n'avez pas encore de compte, rendez-vous sur <a href="https://www.brevo.com" target="_blank">brevo.com</a> pour vous inscrire gratuitement.</p>
            </div>
            
            <div style="padding: 15px; background: white; border-radius: 6px; margin-bottom: 15px;">
                <h3 style="margin-top: 0; font-size: 16px;">2️⃣ Créez une liste de contacts</h3>
                <p>Dans Brevo, allez dans <strong>Contacts → Listes</strong> et créez une nouvelle liste pour vos abonnés newsletter.</p>
            </div>
            
            <div style="padding: 15px; background: white; border-radius: 6px; margin-bottom: 15px;">
                <h3 style="margin-top: 0; font-size: 16px;">3️⃣ Vérifiez votre email expéditeur</h3>
                <p>Dans <strong>Paramètres → Expéditeurs</strong>, ajoutez et vérifiez l'adresse email depuis laquelle vous souhaitez envoyer vos newsletters.</p>
            </div>
            
            <div style="padding: 15px; background: white; border-radius: 6px;">
                <h3 style="margin-top: 0; font-size: 16px;">4️⃣ Générez une clé API</h3>
                <p>Dans <strong>Paramètres → Clés API</strong>, créez une nouvelle clé API et copiez-la dans le formulaire ci-dessus.</p>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff7ed; border-left: 4px solid #f97316; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #9a3412;">💡 Astuce</h4>
                <p style="margin: 0; color: #666;">
                    Une fois la configuration terminée, utilisez le bouton "Tester la connexion API" pour vérifier que tout fonctionne correctement.
                </p>
            </div>
        </div>
        
    </div>
    
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            border-radius: 4px;
        }
        .card h2 {
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .notice.inline {
            padding: 10px 15px;
        }
    </style>
    <?php
}