<?php
/**
 * ========================================
 * PAGE NEWSLETTER AUTOMATIQUE
 * ========================================
 */

function brevo_newsletter_auto_page() {
    // Traiter les actions
    if (isset($_POST['send_test']) && check_admin_referer('brevo_newsletter_action')) {
        $test_email = sanitize_email($_POST['test_email']);
        $result = send_newsletter_via_brevo(true, $test_email);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    if (isset($_POST['send_now']) && check_admin_referer('brevo_newsletter_action')) {
        $result = send_newsletter_via_brevo(false);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        } elseif (isset($result['no_content']) && $result['no_content']) {
            echo '<div class="notice notice-warning"><p>⚠️ ' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    if (isset($_POST['update_schedule']) && check_admin_referer('brevo_newsletter_action')) {
        $auto_send = isset($_POST['auto_send']) ? '1' : '0';
        $send_day = intval($_POST['send_day']);
        $send_hour = intval($_POST['send_hour']);
        $send_minute = intval($_POST['send_minute']);
        
        update_option('brevo_newsletter_auto_send', $auto_send);
        update_option('brevo_newsletter_send_day', $send_day);
        update_option('brevo_newsletter_send_hour', $send_hour);
        update_option('brevo_newsletter_send_minute', $send_minute);
        
        echo '<div class="notice notice-success"><p>✅ Paramètres d\'envoi sauvegardés avec succès !</p></div>';
    }
    
    if (isset($_POST['reset_sent_posts']) && check_admin_referer('brevo_newsletter_action')) {
        reset_sent_posts();
        echo '<div class="notice notice-success"><p>✅ Liste des posts envoyés réinitialisée !</p></div>';
    }
    
    // Récupérer les paramètres
    $config = get_brevo_config();
    $auto_send = get_option('brevo_newsletter_auto_send', '0');
    $send_day = get_option('brevo_newsletter_send_day', 1);
    $send_hour = get_option('brevo_newsletter_send_hour', 9);
    $send_minute = get_option('brevo_newsletter_send_minute', 0);
    $last_sent = get_option('brevo_newsletter_last_sent', 'Jamais');
    $api_configured = is_brevo_configured();
    
    ?>
    <div class="wrap">
        <h1>📅 Newsletter automatique</h1>
        
        <?php if (!$api_configured) : ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez d'abord <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">configurer votre API Brevo</a></p>
            </div>
            <?php return; ?>
        <?php endif; ?>
        
        <!-- Informations -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>📊 Informations</h2>
            <table class="form-table">
                <tr>
                    <th>Dernier envoi :</th>
                    <td><strong><?php echo esc_html($last_sent); ?></strong></td>
                </tr>
                <tr>
                    <th>Envoi automatique :</th>
                    <td>
                        <?php if ($auto_send === '1') : ?>
                            <span style="color: green;">✅ Activé</span> - 
                            Le <?php echo esc_html($send_day); ?> de chaque mois à 
                            <?php echo sprintf('%02d:%02d', $send_hour, $send_minute); ?>
                        <?php else : ?>
                            <span style="color: orange;">⏸️ Désactivé</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Nouveaux posts disponibles :</th>
                    <td>
                        <?php if (has_new_posts_to_send()) : ?>
                            <span style="color: green;">✅ Oui - Prêt à envoyer</span>
                        <?php else : ?>
                            <span style="color: orange;">⏸️ Non - Pas de nouveau contenu</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Posts déjà envoyés :</th>
                    <td>
                        <?php 
                        $sent_posts = get_sent_posts();
                        echo count($sent_posts) . ' post(s)';
                        ?>
                        <?php if (!empty($sent_posts)) : ?>
                            <form method="post" style="display: inline; margin-left: 10px;">
                                <?php wp_nonce_field('brevo_newsletter_action'); ?>
                                <button type="submit" name="reset_sent_posts" class="button button-small"
                                        onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser la liste des posts envoyés ? Tous les posts pourront être renvoyés.');">
                                    🔄 Réinitialiser
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php
                        $all_posts = get_posts(array(
                            'post_type'      => 'post',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                        ));

                        $unsent_posts = array_filter($all_posts, function($post) use ($sent_posts) {
                            return !in_array($post->ID, $sent_posts);
                        });
                        // Traitement de la soumission pour marquer un post comme envoyé
                        if (isset($_POST['mark_as_sent']) && wp_verify_nonce($_POST['_wpnonce'], 'brevo_newsletter_action')) {
                            $post_id = intval($_POST['post_id']);
                            if (!in_array($post_id, $sent_posts)) {
                                $sent_posts[] = $post_id;
                                update_sent_posts($sent_posts);
                                echo '<div class="notice notice-success"><p>Post marqué comme envoyé !</p></div>';
                                // Rafraîchir la page pour éviter les soumissions multiples
                                echo '<meta http-equiv="refresh" content="0">';
                            }
                        }
                        ?>
                        <!-- afficher la liste des posts envoyé -->
                        <?php if (!empty($sent_posts)) : ?>
                            <details style="margin-top: 10px;">
                                <summary>Voir les posts envoyés</summary>
                                <ul>
                                    <?php foreach ($sent_posts as $post_id) : 
                                        $post_title = get_the_title($post_id);
                                    ?>
                                        <li><?php echo esc_html($post_title); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <ul>
                                    </ul>
                            </details>
                        <?php endif; ?>
                        <?php
                        if (!empty($unsent_posts)) : ?>
                            <p><?php echo count($unsent_posts); ?> post(s) non envoyé(s)</p>
                            <details style="margin-top: 10px;">
                                <summary>Voir les posts non envoyés</summary>
                                <ul>
                                    <?php foreach ($unsent_posts as $post) : ?>
                                        <li>
                                            <?php echo esc_html($post->post_title); ?>
                                            <form method="post" style="display: inline; margin-left: 10px;">
                                                <?php wp_nonce_field('brevo_newsletter_action'); ?>
                                                <input type="hidden" name="post_id" value="<?php echo esc_attr($post->ID); ?>">
                                                <button type="submit" name="mark_as_sent" class="button button-small"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir marquer ce post comme envoyé ?');">
                                                    ✅ Marquer comme envoyé
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </details>
                        <?php else : ?>
                            <p><strong>Aucun post non envoyé.</strong></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Liste Brevo :</th>
                    <td>ID : <code><?php echo esc_html($config['list_id']); ?> <?php echo esc_html($config['list_name']); ?></code></td>
                </tr>
                <tr>
                    <th>Expéditeur :</th>
                    <td><?php echo esc_html($config['sender_name']); ?> &lt;<?php echo esc_html($config['sender_email']); ?>&gt;</td>
                </tr>
            </table>
        </div>
        
        <!-- Automatisation -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>⚙️ Configuration de l'envoi automatique</h2>
            <form method="post">
                <?php wp_nonce_field('brevo_newsletter_action'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="auto_send">Activer l'envoi automatique :</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="auto_send" name="auto_send" value="1" 
                                       <?php checked($auto_send, '1'); ?>>
                                <strong>Envoyer automatiquement chaque mois</strong>
                            </label>
                            <p class="description">
                                ℹ️ La newsletter sera envoyée automatiquement uniquement s'il y a de nouveaux posts
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="send_day">Jour d'envoi :</label></th>
                        <td>
                            <select id="send_day" name="send_day" style="width: 200px;">
                                <?php for ($i = 1; $i <= 28; $i++) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected($send_day, $i); ?>>
                                        Le <?php echo $i; ?> de chaque mois
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <p class="description">
                                ⚠️ Les dates 29, 30 et 31 ne sont pas disponibles (tous les mois n'ont pas ces dates)
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Heure d'envoi :</label></th>
                        <td>
                            <select name="send_hour" style="width: 100px;">
                                <?php for ($h = 0; $h < 24; $h++) : ?>
                                    <option value="<?php echo $h; ?>" <?php selected($send_hour, $h); ?>>
                                        <?php echo sprintf('%02d', $h); ?>h
                                    </option>
                                <?php endfor; ?>
                            </select>
                            
                            <select name="send_minute" style="width: 100px;">
                                <?php for ($m = 0; $m < 60; $m += 5) : ?>
                                    <option value="<?php echo $m; ?>" <?php selected($send_minute, $m); ?>>
                                        <?php echo sprintf('%02d', $m); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            
                            <p class="description">
                                📅 Prochain envoi planifié : 
                                <strong>
                                    <?php 
                                    if ($auto_send === '1') {
                                        $next_send = brevo_get_next_send_date($send_day, $send_hour, $send_minute);
                                        echo date_i18n('l j F Y à H:i', $next_send);
                                    } else {
                                        echo 'Envoi automatique désactivé';
                                    }
                                    ?>
                                </strong>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="update_schedule" class="button button-primary button-large">
                        💾 Sauvegarder les paramètres
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Envoi test -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>🧪 Envoyer un test</h2>
            <form method="post">
                <?php wp_nonce_field('brevo_newsletter_action'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="test_email">Email de test :</label></th>
                        <td>
                            <input type="email" id="test_email" name="test_email" 
                                   value="<?php echo esc_attr(get_option('admin_email')); ?>" 
                                   class="regular-text" required>
                            <p class="description">La newsletter sera envoyée à cette adresse pour test</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="send_test" class="button button-secondary button-large">
                        📨 Envoyer un email de test
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Envoi immédiat -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>🚀 Envoi immédiat</h2>
            <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir envoyer la newsletter à tous vos abonnés ?');">
                <?php wp_nonce_field('brevo_newsletter_action'); ?>
                <p>
                    <button type="submit" name="send_now" class="button button-primary button-large">
                        📮 Envoyer la newsletter maintenant
                    </button>
                </p>
                <p class="description">⚠️ La newsletter sera envoyée à tous les abonnés dans 2 minutes.</p>
            </form>
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
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style>
    <?php
}
// La fonction brevo_get_next_send_date() est définie dans newsletter.php