<?php
/**
 * ========================================
 * PAGE GESTION DES ABONNÉS (AVEC IMPORT/EXPORT ET DÉSABONNEMENT)
 * ========================================
 */
/**
 * Récupérer les abonnés de la liste Brevo
 *
 * @param int $limit Nombre d'abonnés à récupérer par page
 * @param int $offset Décalage pour la pagination
 * @return array Tableau contenant les abonnés et le nombre total
 */

// ========================================
// TRAITEMENT DE L'EXPORT CSV (DOIT ÊTRE EN HAUT DU FICHIER)
if (isset($_POST['brevo_export_csv_nonce']) &&
    wp_verify_nonce($_POST['brevo_export_csv_nonce'], 'brevo_export_csv_action')) {

    $config = get_brevo_config();
    if (!is_brevo_configured()) {
        wp_die('La newsletter n\'est pas configurée.');
    }

    // Récupérer tous les abonnés
    $all_subscribers = brevo_get_subscribers(500, 0);
    $contacts = $all_subscribers['contacts'];

    // Générer le CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=abonnés-newsletter-' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Email', 'Nom'));

    foreach ($contacts as $contact) {
        $name = '';
        if (!empty($contact['attributes']['FIRSTNAME'])) {
            $name = $contact['attributes']['FIRSTNAME'];
        }
        if (!empty($contact['attributes']['LASTNAME'])) {
            $name .= ' ' . $contact['attributes']['LASTNAME'];
        }
        fputcsv($output, array($contact['email'], $name));
    }

    fclose($output);
    exit; // Arrêter l'exécution après l'export
}
// ========================================
function brevo_get_subscribers($limit = 50, $offset = 0) {
    $config = get_brevo_config();

    // Vérifier que l'API est configurée
    if (!is_brevo_configured()) {
        return array('contacts' => array(), 'count' => 0);
    }

    // URL de l'API Brevo pour récupérer les contacts d'une liste
    $url = 'https://api.brevo.com/v3/contacts/lists/' . $config['list_id'] . '/contacts?limit=' . $limit . '&offset=' . $offset;

    // Requête HTTP vers l'API Brevo
    $response = wp_remote_get($url, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));

    // Gestion des erreurs de requête
    if (is_wp_error($response)) {
        return array('contacts' => array(), 'count' => 0);
    }

    // Décodage de la réponse JSON
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Retourner les contacts et le nombre total
    return array(
        'contacts' => isset($body['contacts']) ? $body['contacts'] : array(),
        'count' => isset($body['count']) ? $body['count'] : 0
    );
}

function brevo_newsletter_subscribers_page() {
    $config = get_brevo_config();
    $api_configured = is_brevo_configured();

    if (!$api_configured) {
        ?>
        <div class="wrap">
            <h1>👥 Abonnés</h1>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez d'abord <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">configurer votre API Brevo</a></p>
            </div>
        </div>
        <?php
        return;
    }

    // Pagination
    $per_page = 50;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Récupérer les abonnés
    $subscribers_data = brevo_get_subscribers($per_page, $offset);
    $subscribers = $subscribers_data['contacts'];
    $total_subscribers = $subscribers_data['count'];
    $total_pages = ceil($total_subscribers / $per_page);

    // Traiter la soumission du formulaire d'ajout
    if (isset($_POST['brevo_add_subscriber_nonce']) &&
        wp_verify_nonce($_POST['brevo_add_subscriber_nonce'], 'brevo_add_subscriber_action')) {

        $email = sanitize_email($_POST['brevo_subscriber_email']);
        $name = isset($_POST['brevo_subscriber_name']) ? sanitize_text_field($_POST['brevo_subscriber_name']) : '';

        if (!is_email($email)) {
            $add_message = '<div class="notice notice-error"><p>Adresse email invalide.</p></div>';
        } else {
            $result = brevo_add_subscriber($email, $name);
            $add_message = '<div class="notice notice-' . ($result['success'] ? 'success' : 'error') . '"><p>' .
                           esc_html($result['message']) . '</p></div>';
        }
    }

    // Traiter la soumission du formulaire de désabonnement
    if (isset($_POST['brevo_unsubscribe_nonce']) &&
        wp_verify_nonce($_POST['brevo_unsubscribe_nonce'], 'brevo_unsubscribe_action')) {

        $email = sanitize_email($_POST['brevo_unsubscribe_email']);

        if (!is_email($email)) {
            $unsubscribe_message = '<div class="notice notice-error"><p>Adresse email invalide.</p></div>';
        } else {
            $result = brevo_unsubscribe_contact($email);
            $unsubscribe_message = '<div class="notice notice-' . ($result['success'] ? 'success' : 'error') . '"><p>' .
                                    esc_html($result['message']) . '</p></div>';
        }
    }

    // Traiter l'import CSV
    if (isset($_POST['brevo_import_csv_nonce']) &&
        wp_verify_nonce($_POST['brevo_import_csv_nonce'], 'brevo_import_csv_action')) {

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            $line = 0;
            $success = 0;
            $errors = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $line++;
                if ($line === 1) continue; // Sauter l'en-tête

                if (isset($data[0]) && is_email($data[0])) {
                    $email = sanitize_email($data[0]);
                    $name = isset($data[1]) ? sanitize_text_field($data[1]) : '';
                    $result = brevo_add_subscriber($email, $name);
                    if ($result['success']) {
                        $success++;
                    } else {
                        $errors++;
                    }
                } else {
                    $errors++;
                }
            }
            fclose($handle);

            $import_message = '<div class="notice notice-success"><p>' .
                              sprintf(__('Import terminé : %d abonnés ajoutés, %d erreurs.'), $success, $errors) .
                              '</p></div>';
        } else {
            $import_message = '<div class="notice notice-error"><p>Veuillez sélectionner un fichier CSV.</p></div>';
        }
    }

    // Traiter l'export CSV
    if (isset($_POST['brevo_export_csv_nonce']) &&
        wp_verify_nonce($_POST['brevo_export_csv_nonce'], 'brevo_export_csv_action')) {

        $all_subscribers = brevo_get_subscribers(500, 0);
        $contacts = $all_subscribers['contacts'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=abonnés-newsletter-' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Email', 'Nom'));
        foreach ($contacts as $contact) {
            $name = '';
            if (!empty($contact['attributes']['FIRSTNAME'])) {
                $name = $contact['attributes']['FIRSTNAME'];
            }
            if (!empty($contact['attributes']['LASTNAME'])) {
                $name .= ' ' . $contact['attributes']['LASTNAME'];
            }
            fputcsv($output, array($contact['email'], $name));
        }
        fclose($output);
        exit;
    }

    ?>
    <div class="wrap">
        <h1>👥 Gestion des abonnés</h1>

        <!-- Statistiques -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Total abonnés</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($total_subscribers, 0, ',', ' '); ?>
                </div>
            </div>
        </div>
        <!-- Formulaire d'inscription -->
            <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
                <h2>📝 Intégration du formulaire</h2>
                <p>Copiez ce shortcode pour afficher un formulaire d'inscription sur votre site :</p>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                    <code style="font-size: 14px;">[brevo_newsletter_form]</code>
                    <button type="button" class="button button-small" style="margin-left: 10px;"
                            onclick="navigator.clipboard.writeText('[brevo_newsletter_form]'); alert('Shortcode copié !');">
                        📋 Copier
                    </button>
                    <p>copiez ce shortcode pour afficher un formulaire d'inscription style minimal :</p>
                    <code style="font-size: 14px;">[brevo_newsletter_form style="minimal"]</code>
                    <button type="button" class="button button-small" style="margin-left: 10px;"
                            onclick="navigator.clipboard.writeText('[brevo_newsletter_form style=\'minimal\']'); alert('Shortcode copié !');">
                        📋 Copier
                    </button>
                    <p>copiez ce shortcode pour afficher un formulaire d'inscription style en ligne :</p>
                    <code style="font-size: 14px;">[brevo_newsletter_form style="inline"]</code>
                    <button type="button" class="button button-small" style="margin-left: 10px;"
                            onclick="navigator.clipboard.writeText('[brevo_newsletter_form style=\'inline\']'); alert('Shortcode copié !');">
                        📋 Copier
                    </button>
                </div>
                <p><strong>Page de désinscription :</strong></p>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                    <code style="font-size: 12px; word-break: break-all;">
                        <?php echo home_url('/?brevo_unsubscribe=1&email={{contact.EMAIL}}'); ?>
                    </code>
                </div>
            </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">

            <!-- Ajouter un abonné -->
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h2>➕ Ajouter un abonné manuellement</h2>
                <?php if (isset($add_message)) echo $add_message; ?>
                <form method="post" action="">
                    <?php wp_nonce_field('brevo_add_subscriber_action', 'brevo_add_subscriber_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="brevo_subscriber_name">Nom (optionnel)</label></th>
                            <td><input type="text" name="brevo_subscriber_name" id="brevo_subscriber_name" placeholder="Nom, Prénom" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="brevo_subscriber_email">Email *</label></th>
                            <td><input type="email" name="brevo_subscriber_email" id="brevo_subscriber_email" placeholder="Email" required class="regular-text"></td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Ajouter l'abonné"></p>
                </form>
            </div>

            <!-- Désabonner un abonné -->
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h2>❌ Désabonner un abonné</h2>
                <?php if (isset($unsubscribe_message)) echo $unsubscribe_message; ?>
                <form method="post" action="">
                    <?php wp_nonce_field('brevo_unsubscribe_action', 'brevo_unsubscribe_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="brevo_unsubscribe_email">Email *</label></th>
                            <td><input type="email" name="brevo_unsubscribe_email" id="brevo_unsubscribe_email" placeholder="Email de l'abonné" required class="regular-text"></td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-secondary" value="Désabonner"></p>
                </form>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <!-- Import CSV -->
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h2>📤 Importer des abonnés (CSV)</h2>
                <?php if (isset($import_message)) echo $import_message; ?>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('brevo_import_csv_action', 'brevo_import_csv_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="csv_file">Fichier CSV *</label></th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                                <p class="description">Format attendu : email (colonne 1), nom (colonne 2, optionnel).</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Importer"></p>
                </form>
            </div>

            <!-- Export CSV -->
            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h2>📥 Exporter les abonnés (CSV)</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('brevo_export_csv_action', 'brevo_export_csv_nonce'); ?>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Exporter"></p>
                </form>
            </div>
        </div>

        <!-- Liste des abonnés -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>📋 Liste des abonnés (<?php echo number_format($total_subscribers, 0, ',', ' '); ?>)</h2>

            <?php if (!empty($subscribers)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;">Email</th>
                        <th>Nom</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $subscriber) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($subscriber['email']); ?></strong></td>
                        <td>
                            <?php
                            $name_parts = array();
                            if (!empty($subscriber['attributes']['FIRSTNAME'])) {
                                $name_parts[] = $subscriber['attributes']['FIRSTNAME'];
                            }
                            if (!empty($subscriber['attributes']['LASTNAME'])) {
                                $name_parts[] = $subscriber['attributes']['LASTNAME'];
                            }
                            echo !empty($name_parts) ? esc_html(implode(' ', $name_parts)) : '—';
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($subscriber['createdAt'])) {
                                echo date_i18n('j M Y', strtotime($subscriber['createdAt']));
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($subscriber['emailBlacklisted']) : ?>
                                <span style="color: #dc3545;">❌ Bloqué</span>
                            <?php else : ?>
                                <span style="color: #28a745;">✅ Actif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
            <div style="margin-top: 20px; text-align: center;">
                <?php
                $base_url = admin_url('admin.php?page=brevo-newsletter-subscribers');

                if ($current_page > 1) {
                    echo '<a href="' . esc_url($base_url . '&paged=' . ($current_page - 1)) . '" class="button">« Précédent</a> ';
                }

                echo '<span style="margin: 0 15px;">Page ' . $current_page . ' sur ' . $total_pages . '</span>';

                if ($current_page < $total_pages) {
                    echo ' <a href="' . esc_url($base_url . '&paged=' . ($current_page + 1)) . '" class="button">Suivant »</a>';
                }
                ?>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <p style="text-align: center; color: #666; padding: 40px;">
                Aucun abonné pour le moment
            </p>
            <?php endif; ?>
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
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
    <?php
}
