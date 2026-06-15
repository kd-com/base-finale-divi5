<?php
/**
 * ========================================
 * PAGE ADMIN - GESTION DU CRON AUTONOME
 * Avec intégration automatique cron-job.org
 * ========================================
 *
 * À inclure dans newsletter.php :
 * require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/cron.php';
 *
 * Et ajouter dans brevo_newsletter_admin_menu() :
 * add_submenu_page('brevo-newsletter', 'Cron autonome', 'Cron autonome', $capability, 'brevo-newsletter-cron', 'brevo_newsletter_cron_page');
 */

// ============================================
// INITIALISATION - CLÉ SECRÈTE
// ============================================

function brevo_newsletter_cron_init() {
    if (empty(get_option('brevo_newsletter_cron_key', ''))) {
        update_option('brevo_newsletter_cron_key', brevo_generate_cron_key());
    }
}
brevo_newsletter_cron_init();

function brevo_generate_cron_key() {
    return bin2hex(random_bytes(32));
}

// ============================================
// FONCTIONS API CRON-JOB.ORG
// ============================================

/**
 * Appel générique à l'API cron-job.org
 */
function cronjob_api($method, $endpoint, $data = null) {
    $api_key = get_option('brevo_cronjob_api_key', '');

    if (empty($api_key)) {
        return ['success' => false, 'message' => 'Clé API cron-job.org manquante.'];
    }

    $args = [
        'method'  => $method,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'timeout' => 15,
    ];

    if ($data !== null) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request('https://api.cron-job.org' . $endpoint, $args);

    if (is_wp_error($response)) {
        return ['success' => false, 'message' => $response->get_error_message()];
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    return [
        'success' => ($code >= 200 && $code < 300),
        'code'    => $code,
        'body'    => $body,
        'message' => $body['error'] ?? ($code >= 200 && $code < 300 ? 'OK' : 'Erreur ' . $code),
    ];
}

/**
 * Créer ou mettre à jour la tâche cron sur cron-job.org
 */
function cronjob_create_or_update($cron_url, $send_hour, $send_minute) {
    $existing_id = get_option('brevo_cronjob_job_id', '');

    // Définition de la tâche : toutes les heures
    $job = [
        'job' => [
            'url'     => $cron_url,
            'enabled' => true,
            'title'   => get_bloginfo('name') . ' - Newsletter auto',
            'schedule' => [
                'timezone'  => wp_timezone_string(),
                'hours'     => [-1],   // toutes les heures
                'mdays'     => [-1],
                'minutes'   => [0],    // à la minute 0 de chaque heure
                'months'    => [-1],
                'wdays'     => [-1],
            ],
            'requestMethod' => 0, // GET
            'saveResponses' => true,
            'notification'  => [
                'onFailure'   => true,
                'onSuccess'   => false,
                'onDisable'   => true,
            ],
        ],
    ];

    if (!empty($existing_id)) {
        // Mettre à jour
        $result = cronjob_api('PATCH', '/jobs/' . $existing_id, $job);
        if ($result['success']) {
            return ['success' => true, 'message' => 'Tâche cron-job.org mise à jour (ID: ' . $existing_id . ').', 'job_id' => $existing_id];
        }
        // Si la mise à jour échoue (job supprimé manuellement), on en crée un nouveau
    }

    // Créer
    $result = cronjob_api('PUT', '/jobs', $job);

    if ($result['success'] && isset($result['body']['jobId'])) {
        $job_id = $result['body']['jobId'];
        update_option('brevo_cronjob_job_id', $job_id);
        return ['success' => true, 'message' => 'Tâche créée sur cron-job.org (ID: ' . $job_id . ').', 'job_id' => $job_id];
    }

    return ['success' => false, 'message' => 'Erreur création : ' . ($result['message'] ?? 'Inconnue')];
}

/**
 * Récupérer le statut de la tâche sur cron-job.org
 */
function cronjob_get_status() {
    $job_id = get_option('brevo_cronjob_job_id', '');
    if (empty($job_id)) return null;

    $result = cronjob_api('GET', '/jobs/' . $job_id);
    if ($result['success'] && isset($result['body']['jobDetails'])) {
        return $result['body']['jobDetails'];
    }
    return null;
}

/**
 * Supprimer la tâche sur cron-job.org
 */
function cronjob_delete() {
    $job_id = get_option('brevo_cronjob_job_id', '');
    if (empty($job_id)) return ['success' => false, 'message' => 'Aucune tâche à supprimer.'];

    $result = cronjob_api('DELETE', '/jobs/' . $job_id);
    if ($result['success']) {
        delete_option('brevo_cronjob_job_id');
        return ['success' => true, 'message' => 'Tâche supprimée de cron-job.org.'];
    }
    return ['success' => false, 'message' => $result['message']];
}

// ============================================
// PAGE D'ADMINISTRATION
// ============================================

function brevo_newsletter_cron_page() {

    $cron_key    = get_option('brevo_newsletter_cron_key', '');
    $cron_url    = get_stylesheet_directory_uri() . '/newsletter-cron.php?cron_key=' . $cron_key;
    $send_hour   = (int) get_option('brevo_newsletter_send_hour', 9);
    $send_minute = (int) get_option('brevo_newsletter_send_minute', 0);

    // --- ACTIONS ---

    // Sauvegarder la clé API cron-job.org
    if (isset($_POST['save_cronjob_api']) && check_admin_referer('brevo_cron_settings')) {
        $api_key = sanitize_text_field($_POST['cronjob_api_key']);
        update_option('brevo_cronjob_api_key', $api_key);
        echo '<div class="notice notice-success"><p>✅ Clé API cron-job.org sauvegardée.</p></div>';
    }

    // Créer / mettre à jour la tâche sur cron-job.org
    if (isset($_POST['create_cronjob']) && check_admin_referer('brevo_cron_settings')) {
        $result = cronjob_create_or_update($cron_url, $send_hour, $send_minute);
        $type   = $result['success'] ? 'success' : 'error';
        echo '<div class="notice notice-' . $type . '"><p>' . ($result['success'] ? '✅' : '❌') . ' ' . esc_html($result['message']) . '</p></div>';
    }

    // Supprimer la tâche sur cron-job.org
    if (isset($_POST['delete_cronjob']) && check_admin_referer('brevo_cron_settings')) {
        $result = cronjob_delete();
        $type   = $result['success'] ? 'success' : 'error';
        echo '<div class="notice notice-' . $type . '"><p>' . ($result['success'] ? '✅' : '❌') . ' ' . esc_html($result['message']) . '</p></div>';
    }

    // Régénérer la clé secrète
    if (isset($_POST['regenerate_key']) && check_admin_referer('brevo_cron_settings')) {
        $new_key  = brevo_generate_cron_key();
        update_option('brevo_newsletter_cron_key', $new_key);
        $cron_key = $new_key;
        $cron_url = get_stylesheet_directory_uri() . '/newsletter-cron.php?cron_key=' . $cron_key;
        echo '<div class="notice notice-warning"><p>⚠️ Nouvelle clé générée. Cliquez sur "Mettre à jour cron-job.org" pour synchroniser.</p></div>';
    }

    // Sauvegarder la tolérance
    if (isset($_POST['save_cron_settings']) && check_admin_referer('brevo_cron_settings')) {
        update_option('brevo_newsletter_cron_tolerance', max(5, min(120, intval($_POST['cron_tolerance']))));
        echo '<div class="notice notice-success"><p>✅ Paramètres sauvegardés.</p></div>';
    }

    // Vider l'historique
    if (isset($_POST['clear_history']) && check_admin_referer('brevo_cron_settings')) {
        delete_option('brevo_newsletter_cron_history');
        echo '<div class="notice notice-success"><p>✅ Historique vidé.</p></div>';
    }

    // --- DONNÉES ---
    $cronjob_api_key     = get_option('brevo_cronjob_api_key', '');
    $cronjob_job_id      = get_option('brevo_cronjob_job_id', '');
    $tolerance           = get_option('brevo_newsletter_cron_tolerance', 30);
    $last_run            = get_option('brevo_newsletter_cron_last_run', '');
    $history             = get_option('brevo_newsletter_cron_history', []);
    $send_day            = get_option('brevo_newsletter_send_day', 1);
    $auto_send           = get_option('brevo_newsletter_auto_send', '0');
    $cron_script_path    = get_stylesheet_directory() . '/newsletter-cron.php';
    $cron_script_exists  = file_exists($cron_script_path);
    $cron_url_debug      = $cron_url . '&debug=1';
    $wp_cron_disabled    = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;

    // Statut cron-job.org
    $cronjob_status = null;
    if (!empty($cronjob_api_key) && !empty($cronjob_job_id)) {
        $cronjob_status = cronjob_get_status();
    }

    $cronjob_configured = !empty($cronjob_api_key) && !empty($cronjob_job_id);

    // Logs
    $upload_dir = wp_upload_dir();
    $log_file   = $upload_dir['basedir'] . '/newsletter-cron.log';
    $log_exists = file_exists($log_file);
    $log_tail   = '';
    if ($log_exists) {
        $lines    = file($log_file);
        $log_tail = implode('', array_slice($lines, -30));
    }

    ?>
    <div class="wrap">
        <h1>⚙️ Cron Autonome – Newsletter</h1>
        <p style="color:#666;">Envoi automatique via cron-job.org — aucun accès serveur requis, fonctionne en preprod et en prod.</p>

        <!-- STATUT GLOBAL -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin:20px 0;">

            <div style="background:linear-gradient(135deg,<?php echo $cron_script_exists ? '#4facfe,#00f2fe' : '#f5576c,#f093fb'; ?>); color:#fff; padding:18px; border-radius:8px;">
                <div style="font-size:12px; opacity:.85;">Script cron</div>
                <div style="font-size:22px; font-weight:bold; margin:6px 0;"><?php echo $cron_script_exists ? '✅ Présent' : '❌ Absent'; ?></div>
                <div style="font-size:11px; opacity:.8; word-break:break-all;"><?php echo esc_html($cron_script_path); ?></div>
            </div>

            <div style="background:linear-gradient(135deg,<?php echo $cronjob_configured ? '#43e97b,#38f9d7' : '#fa709a,#fee140'; ?>); color:#fff; padding:18px; border-radius:8px;">
                <div style="font-size:12px; opacity:.85;">cron-job.org</div>
                <div style="font-size:22px; font-weight:bold; margin:6px 0;">
                    <?php
                    if ($cronjob_configured && $cronjob_status) {
                        echo $cronjob_status['enabled'] ? '✅ Actif' : '⏸ Désactivé';
                    } elseif ($cronjob_configured) {
                        echo '⚠️ Configuré';
                    } else {
                        echo '❌ Non configuré';
                    }
                    ?>
                </div>
                <?php if (!empty($cronjob_job_id)) : ?>
                <div style="font-size:11px; opacity:.8;">ID: <?php echo esc_html($cronjob_job_id); ?></div>
                <?php endif; ?>
            </div>

            <div style="background:linear-gradient(135deg,<?php echo $auto_send === '1' ? '#667eea,#764ba2' : '#fa709a,#fee140'; ?>); color:#fff; padding:18px; border-radius:8px;">
                <div style="font-size:12px; opacity:.85;">Envoi automatique</div>
                <div style="font-size:22px; font-weight:bold; margin:6px 0;"><?php echo $auto_send === '1' ? '✅ Activé' : '⏸ Désactivé'; ?></div>
                <div style="font-size:11px; opacity:.8;">Le <?php echo $send_day; ?> à <?php echo sprintf('%02d:%02d', $send_hour, $send_minute); ?></div>
            </div>

            <div style="background:linear-gradient(135deg,#f093fb,#f5576c); color:#fff; padding:18px; border-radius:8px;">
                <div style="font-size:12px; opacity:.85;">Dernière exécution</div>
                <div style="font-size:16px; font-weight:bold; margin:6px 0;">
                    <?php echo $last_run ? esc_html(date_i18n('j M Y à H:i', strtotime($last_run))) : 'Jamais'; ?>
                </div>
            </div>

        </div>

        <!-- ÉTAPE 1 : SCRIPT -->
        <div class="nl-card">
            <h2>📁 Étape 1 – Script cron dans le thème</h2>
            <?php if ($cron_script_exists) : ?>
                <div class="nl-alert nl-alert-success">✅ Le fichier <code>newsletter-cron.php</code> est présent dans le thème.</div>
            <?php else : ?>
                <div class="nl-alert nl-alert-warning">
                    ⚠️ Le fichier <code>newsletter-cron.php</code> est absent.<br>
                    Déposez-le dans : <code><?php echo esc_html($cron_script_path); ?></code>
                </div>
                <p>Sur Docker :</p>
                <pre class="code-block">docker cp newsletter-cron.php <nom_container>:<?php echo esc_html($cron_script_path); ?></pre>
                <p>Sur serveur (FTP/SSH) : uploadez le fichier dans <code>wp-content/themes/kd-com/</code></p>
            <?php endif; ?>
        </div>

        <!-- ÉTAPE 2 : CRON-JOB.ORG -->
        <div class="nl-card">
            <h2>🤖 Étape 2 – Connecter cron-job.org</h2>
            <p>
                cron-job.org est un service gratuit qui appellera votre script toutes les heures,
                même sans visiteur, en preprod comme en prod.
            </p>

            <!-- Sous-étape 2a : Créer un compte et récupérer la clé API -->
            <div class="nl-step">
                <div class="nl-step-number">1</div>
                <div class="nl-step-content">
                    <strong>Créez un compte gratuit</strong> sur
                    <a href="https://console.cron-job.org" target="_blank">cron-job.org</a>,
                    puis allez dans <strong>Paramètres → API Keys</strong> et cliquez sur
                    <strong>"Create API Key"</strong> pour générer une clé.
                    <br>
                    <a href="https://console.cron-job.org" target="_blank" class="button button-secondary" style="margin-top:8px;">
                        Ouvrir cron-job.org →
                    </a>
                </div>
            </div>

            <!-- Sous-étape 2b : Saisir la clé API -->
            <div class="nl-step">
                <div class="nl-step-number">2</div>
                <div class="nl-step-content">
                    <strong>Entrez votre clé API cron-job.org</strong> :
                    <form method="post" style="margin-top:10px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <?php wp_nonce_field('brevo_cron_settings'); ?>
                        <input type="text"
                               name="cronjob_api_key"
                               value="<?php echo esc_attr($cronjob_api_key); ?>"
                               placeholder="Votre clé API cron-job.org"
                               class="regular-text"
                               style="flex:1; min-width:300px;">
                        <button type="submit" name="save_cronjob_api" class="button button-primary">
                            💾 Sauvegarder la clé
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sous-étape 2c : Créer la tâche automatiquement -->
            <div class="nl-step">
                <div class="nl-step-number">3</div>
                <div class="nl-step-content">
                    <strong>Créer la tâche automatiquement</strong> sur cron-job.org :

                    <?php if (empty($cronjob_api_key)) : ?>
                        <div class="nl-alert nl-alert-warning" style="margin-top:10px;">
                            ⚠️ Entrez d'abord votre clé API à l'étape 2.
                        </div>
                    <?php else : ?>
                        <div style="margin-top:10px;">
                            <p style="color:#555; font-size:13px;">
                                URL qui sera appelée toutes les heures :<br>
                                <code style="font-size:11px; word-break:break-all;"><?php echo esc_html($cron_url); ?></code>
                            </p>

                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('brevo_cron_settings'); ?>
                                <button type="submit" name="create_cronjob" class="button button-primary button-large">
                                    🚀 <?php echo $cronjob_configured ? 'Mettre à jour sur cron-job.org' : 'Créer la tâche sur cron-job.org'; ?>
                                </button>
                            </form>

                            <?php if ($cronjob_configured) : ?>
                            <form method="post" style="display:inline; margin-left:10px;">
                                <?php wp_nonce_field('brevo_cron_settings'); ?>
                                <button type="submit" name="delete_cronjob"
                                        class="button button-link-delete"
                                        onclick="return confirm('Supprimer la tâche de cron-job.org ?');">
                                    🗑️ Supprimer
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($cronjob_status) : ?>
                        <div class="nl-alert <?php echo $cronjob_status['enabled'] ? 'nl-alert-success' : 'nl-alert-warning'; ?>" style="margin-top:15px;">
                            <?php echo $cronjob_status['enabled'] ? '✅ Tâche active' : '⏸ Tâche désactivée'; ?>
                            <?php if (!empty($cronjob_status['lastExecution']['date'])) : ?>
                                — Dernière exécution cron-job.org :
                                <strong><?php echo esc_html(date_i18n('j M Y à H:i', strtotime($cronjob_status['lastExecution']['date']))); ?></strong>
                                (statut HTTP : <?php echo esc_html($cronjob_status['lastExecution']['httpStatus'] ?? 'N/A'); ?>)
                            <?php endif; ?>
                            <?php if (!empty($cronjob_status['nextExecution'])) : ?>
                                <br>Prochaine exécution :
                                <strong><?php echo esc_html(date_i18n('j M Y à H:i', strtotime($cronjob_status['nextExecution']))); ?></strong>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- CLÉ SECRÈTE -->
        <div class="nl-card">
            <h2>🔑 Clé secrète du script</h2>
            <p>Cette clé protège l'accès au script. Elle est automatiquement incluse dans l'URL transmise à cron-job.org.</p>

            <div style="display:flex; align-items:center; gap:10px; margin:10px 0; flex-wrap:wrap;">
                <code style="background:#f0f0f0; padding:10px 15px; border-radius:4px; font-size:13px; flex:1; word-break:break-all; min-width:200px;">
                    <?php echo esc_html($cron_key ?: '(aucune clé)'); ?>
                </code>
                <button type="button" class="button" onclick="copyToClipboard('<?php echo esc_js($cron_key); ?>')">
                    📋 Copier
                </button>
            </div>

            <form method="post" style="display:inline;">
                <?php wp_nonce_field('brevo_cron_settings'); ?>
                <button type="submit" name="regenerate_key" class="button button-secondary"
                        onclick="return confirm('Regénérer la clé ? Pensez à resynchroniser cron-job.org ensuite.');">
                    🔄 Regénérer la clé
                </button>
            </form>
            <p class="description" style="margin-top:8px;">
                ⚠️ Si vous regénérez la clé, cliquez ensuite sur "Mettre à jour sur cron-job.org" pour synchroniser la nouvelle URL.
            </p>
        </div>

        <!-- PARAMÈTRES -->
        <div class="nl-card">
            <h2>⚙️ Paramètres</h2>
            <form method="post">
                <?php wp_nonce_field('brevo_cron_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="cron_tolerance">Tolérance horaire</label></th>
                        <td>
                            <input type="number" id="cron_tolerance" name="cron_tolerance"
                                   value="<?php echo esc_attr($tolerance); ?>"
                                   min="5" max="120" style="width:80px;"> minutes
                            <p class="description">
                                Écart accepté entre l'heure d'exécution et l'heure d'envoi configurée.<br>
                                <strong>Recommandé : 30 min</strong> (cron-job.org tourne toutes les heures).
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="save_cron_settings" class="button button-primary">💾 Sauvegarder</button>
                </p>
            </form>
        </div>

        <!-- TEST MANUEL -->
        <div class="nl-card" style="background:#fff7ed; border-left:4px solid #f97316;">
            <h2 style="color:#9a3412;">🧪 Test manuel</h2>
            <p>Testez l'exécution du script directement depuis le navigateur :</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="<?php echo esc_url($cron_url_debug); ?>" target="_blank" class="button button-primary">
                    🧪 Tester en mode debug
                </a>
                <a href="<?php echo esc_url($cron_url); ?>" target="_blank" class="button button-secondary">
                    ▶️ Exécuter silencieusement
                </a>
            </div>
            <p class="description" style="margin-top:10px;">
                Le script vérifie toutes les conditions (bon jour, bonne heure ±<?php echo $tolerance; ?> min, nouveau contenu, pas déjà envoyé ce mois).
                Si une condition n'est pas remplie, il s'arrête sans envoyer.
            </p>
        </div>

        <!-- HISTORIQUE -->
        <div class="nl-card">
            <h2>📋 Historique des exécutions</h2>
            <?php if (!empty($history)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:20%;">Date</th>
                            <th style="width:15%;">Statut</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($history) as $entry) : ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n('j M Y à H:i', strtotime($entry['date']))); ?></td>
                            <td>
                                <?php
                                $labels = [
                                    'success' => '<span style="color:#28a745;font-weight:bold;">✅ Succès</span>',
                                    'error'   => '<span style="color:#dc3545;font-weight:bold;">❌ Erreur</span>',
                                    'skipped' => '<span style="color:#6c757d;">⏭ Ignoré</span>',
                                ];
                                echo $labels[$entry['status']] ?? esc_html($entry['status']);
                                ?>
                            </td>
                            <td><?php echo esc_html($entry['message']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" style="margin-top:15px;">
                    <?php wp_nonce_field('brevo_cron_settings'); ?>
                    <button type="submit" name="clear_history" class="button button-secondary"
                            onclick="return confirm('Vider tout l\'historique ?');">
                        🗑️ Vider l'historique
                    </button>
                </form>
            <?php else : ?>
                <p style="color:#666; text-align:center; padding:30px;">
                    Aucune exécution enregistrée. L'historique apparaîtra ici après la première exécution.
                </p>
            <?php endif; ?>
        </div>

        <!-- LOG FICHIER -->
        <?php if ($log_exists) : ?>
        <div class="nl-card">
            <h2>📄 Journal (30 dernières lignes)</h2>
            <p style="color:#666;">Fichier : <code><?php echo esc_html($log_file); ?></code></p>
            <pre style="background:#1e1e1e; color:#d4d4d4; padding:15px; border-radius:4px; overflow:auto; font-size:12px; max-height:400px;"><?php echo esc_html($log_tail ?: 'Log vide.'); ?></pre>
        </div>
        <?php endif; ?>

    </div>

    <style>
        .nl-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            border-radius: 6px;
            padding: 20px 25px;
            margin-top: 20px;
        }
        .nl-card h2 {
            margin-top: 0;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
            font-size: 18px;
        }
        .nl-alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 13px;
        }
        .nl-alert-success { background:#d1fae5; border-left:4px solid #10b981; color:#065f46; }
        .nl-alert-warning { background:#fff7ed; border-left:4px solid #f97316; color:#92400e; }
        .nl-alert-info    { background:#eff6ff; border-left:4px solid #3b82f6; color:#1e40af; }
        .nl-step {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .nl-step-number {
            background: #0073aa;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        .nl-step-content { flex: 1; }
        .code-block {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>

    <script>
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                const t = document.title;
                document.title = '✅ Copié !';
                setTimeout(() => document.title = t, 1500);
            });
        } else {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        }
    }
    </script>
    <?php
}