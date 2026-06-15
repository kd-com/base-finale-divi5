<?php
/**
 * ========================================
 * MODULE APERÇU DES NEWSLETTERS ENVOYÉES
 * ========================================
 *
 * Fonctionnalités :
 * - Page admin listant toutes les newsletters envoyées avec liens mirror
 * - Shortcode [newsletter_archives] pour afficher les newsletters en front
 * - Système de cache pour optimiser les performances
 */

// ============================================
// PAGE ADMIN - ARCHIVES DES NEWSLETTERS
// ============================================

/**
 * Ajouter la page "Archives" au menu Newsletter
 */
add_action('admin_menu', 'brevo_newsletter_archives_menu', 20);

function brevo_newsletter_archives_menu() {
    add_submenu_page(
        'brevo-newsletter',
        'Archives des newsletters',
        'Archives',
        'manage_options',
        'brevo-newsletter-archives',
        'brevo_newsletter_archives_page'
    );
}

/**
 * Page d'administration des archives
 */
function brevo_newsletter_archives_page() {
    $config = get_brevo_config();
    $api_configured = is_brevo_configured();

    if (!$api_configured) {
        ?>
        <div class="wrap">
            <h1>📚 Archives des newsletters</h1>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez d'abord <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">configurer votre API Brevo</a></p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Action : vider le cache
    if (isset($_GET['clear_cache']) && check_admin_referer('clear_archives_cache', '_wpnonce')) {
        brevo_invalidate_archives_cache();
        echo '<div class="notice notice-success"><p>✅ Cache vidé ! Les données ont été rechargées.</p></div>';
    }

    // Récupérer toutes les campagnes envoyées
    $campaigns = brevo_get_all_sent_campaigns();

    ?>
    <div class="wrap">
        <h1>📚 Archives des newsletters</h1>

        <p style="margin-bottom: 20px;">
            Liste de toutes les newsletters envoyées avec liens mirror en ligne.
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=brevo-newsletter-archives&clear_cache=1'), 'clear_archives_cache'); ?>" 
               class="button button-secondary" 
               style="margin-left: 10px;">
                🔄 Actualiser les données
            </a>
        </p>

        <!-- Shortcode pour le front -->
        <div class="card" style="margin: 20px 0; padding: 15px;">
            <p>
                <strong>💡 Affichage en front-end :</strong><br>
                Utilisez le shortcode suivant pour afficher les archives sur votre site :
                <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px; display: inline-block; margin-top: 5px;">[newsletter_archives]</code>
            </p>
            <p style="margin-top: 10px;">
                <strong>Options disponibles :</strong><br>
                <code>[newsletter_archives limit="10"]</code> - Limiter le nombre de newsletters affichées<br>
                <code>[newsletter_archives show_stats="true"]</code> - Afficher les statistiques (taux d'ouverture, etc.)
            </p>
        </div>

        <?php if (!empty($campaigns)) : ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 35%;">Nom de la campagne</th>
                    <th style="width: 15%;">Date d'envoi</th>
                    <th style="width: 10%;">Destinataires</th>
                    <th style="width: 10%;">Taux d'ouverture</th>
                    <th style="width: 25%;">Lien Mirror</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign) : ?>
                <tr>
                    <td><?php echo esc_html($campaign['id']); ?></td>
                    <td>
                        <strong><?php echo esc_html($campaign['name']); ?></strong>
                        <?php if (!empty($campaign['subject'])) : ?>
                        <div style="font-size: 12px; color: #666; margin-top: 3px;">
                            <?php echo esc_html($campaign['subject']); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date_i18n('j F Y', strtotime($campaign['sent_date'])); ?></td>
                    <td><?php echo number_format($campaign['recipients'], 0, ',', ' '); ?></td>
                    <td>
                        <strong style="color: <?php echo $campaign['open_rate'] > 20 ? '#28a745' : '#dc3545'; ?>;">
                            <?php echo $campaign['open_rate']; ?>%
                        </strong>
                        <div style="font-size: 11px; color: #666;">
                            <?php echo number_format($campaign['unique_opens'], 0, ',', ' '); ?> ouvertures
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($campaign['mirror_url'])) : ?>
                        <a href="<?php echo esc_url($campaign['mirror_url']); ?>" class="button button-small" target="_blank">
                            🔗 Voir la newsletter
                        </a>
                        <button type="button" class="button button-small copy-url-btn" data-url="<?php echo esc_attr($campaign['mirror_url']); ?>" style="margin-left: 5px;">
                            📋 Copier le lien
                        </button>
                        <div style="margin-top: 5px; font-size: 11px; color: #666; word-break: break-all;">
                            <?php echo esc_html($campaign['mirror_url']); ?>
                        </div>
                        <?php else : ?>
                        <span style="color: #999;">Lien non disponible</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php else : ?>

        <div class="notice notice-warning" style="margin-top: 20px;">
            <p>Aucune newsletter envoyée pour le moment.</p>
        </div>

        <?php endif; ?>

    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.copy-url-btn').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var btn = $(this);

            // Copier dans le presse-papier
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    btn.text('✅ Copié !');
                    setTimeout(function() {
                        btn.text('📋 Copier le lien');
                    }, 2000);
                });
            } else {
                // Fallback pour les navigateurs plus anciens
                var temp = $('<input>');
                $('body').append(temp);
                temp.val(url).select();
                document.execCommand('copy');
                temp.remove();
                btn.text('✅ Copié !');
                setTimeout(function() {
                    btn.text('📋 Copier le lien');
                }, 2000);
            }
        });
    });
    </script>

    <style>
        .copy-url-btn:hover {
            background: #f0f0f0;
        }
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            border-radius: 4px;
        }
    </style>
    <?php
}

// ============================================
// FONCTION API - RÉCUPÉRER TOUTES LES CAMPAGNES
// ============================================

/**
 * Récupérer toutes les campagnes envoyées avec liens mirror
 */
function brevo_get_all_sent_campaigns($limit = 100) {
    $config = get_brevo_config();

    // Cache de 1 heure
    $cache_key = 'brevo_all_campaigns_' . md5($config['api_key']);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $response = wp_remote_get('https://api.brevo.com/v3/emailCampaigns?limit=' . $limit . '&sort=desc&status=sent', array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        error_log('Brevo Archives Error: ' . $response->get_error_message());
        return array();
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($body['campaigns'])) {
        return array();
    }

    $campaigns = array();

    foreach ($body['campaigns'] as $campaign) {
        // Ne garder que les campagnes envoyées
        if (!isset($campaign['status']) || $campaign['status'] !== 'sent') {
            continue;
        }

        // Agréger les statistiques
        $sent = 0;
        $unique_views = 0;

        if (isset($campaign['statistics']['campaignStats']) && is_array($campaign['statistics']['campaignStats'])) {
            foreach ($campaign['statistics']['campaignStats'] as $list_stats) {
                $sent += isset($list_stats['sent']) ? $list_stats['sent'] : 0;
                $unique_views += isset($list_stats['uniqueViews']) ? $list_stats['uniqueViews'] : 0;
            }
        }

        // Récupérer uniquement le lien mirror
        $mirror_url = brevo_get_campaign_mirror_url($campaign['id']);

        $campaigns[] = array(
            'id' => $campaign['id'],
            'name' => $campaign['name'],
            'subject' => isset($campaign['subject']) ? $campaign['subject'] : '',
            'sent_date' => $campaign['sentDate'],
            'recipients' => $sent,
            'unique_opens' => $unique_views,
            'open_rate' => $sent > 0 ? round(($unique_views / $sent * 100), 1) : 0,
            'mirror_url' => $mirror_url
        );
    }

    // Mettre en cache pour 1 heure
    set_transient($cache_key, $campaigns, HOUR_IN_SECONDS);

    return $campaigns;
}

/**
 * Récupérer uniquement le lien mirror d'une campagne
 */
function brevo_get_campaign_mirror_url($campaign_id) {
    $config = get_brevo_config();

    $response = wp_remote_get('https://api.brevo.com/v3/emailCampaigns/' . $campaign_id, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 10
    ));

    if (is_wp_error($response)) {
        error_log('Brevo Mirror URL Error for campaign ' . $campaign_id . ': ' . $response->get_error_message());
        return '';
    }

    $campaign = json_decode(wp_remote_retrieve_body($response), true);
    
    // Debug : voir la structure complète
    error_log('Campaign ' . $campaign_id . ' data: ' . print_r($campaign, true));

    // Vérifier plusieurs emplacements possibles du lien mirror
    $mirror_url = '';
    
    // Emplacement 1 : shareLink (utilisé par Brevo/Sendinblue)
    if (isset($campaign['shareLink']) && !empty($campaign['shareLink'])) {
        $mirror_url = $campaign['shareLink'];
    }
    // Emplacement 2 : mirrorUrl
    elseif (isset($campaign['mirrorUrl']) && !empty($campaign['mirrorUrl'])) {
        $mirror_url = $campaign['mirrorUrl'];
    }
    // Emplacement 3 : htmlUrl
    elseif (isset($campaign['htmlUrl']) && !empty($campaign['htmlUrl'])) {
        $mirror_url = $campaign['htmlUrl'];
    }
    
    error_log('Campaign ' . $campaign_id . ' mirror URL: ' . $mirror_url);
    
    return $mirror_url;
}

// ============================================
// SHORTCODE FRONT-END
// ============================================

/**
 * Shortcode pour afficher les archives de newsletters en front
 * Usage: [newsletter_archives limit="10" show_stats="true"]
 */
add_shortcode('newsletter_archives', 'brevo_newsletter_archives_shortcode');

function brevo_newsletter_archives_shortcode($atts) {
    // Paramètres du shortcode
    $atts = shortcode_atts(array(
        'limit' => 20,
        'show_stats' => 'false',
        'title' => 'Archives des newsletters'
    ), $atts);

    // Vérifier que Brevo est configuré
    if (!is_brevo_configured()) {
        return '<p>Les archives de newsletters ne sont pas disponibles pour le moment.</p>';
    }

    // Récupérer les campagnes
    $campaigns = brevo_get_all_sent_campaigns($atts['limit']);

    if (empty($campaigns)) {
        return '<p>Aucune newsletter disponible pour le moment.</p>';
    }

    // Générer le HTML
    ob_start();
    ?>

    <div class="brevo-newsletter-archives">

        <?php if (!empty($atts['title'])) : ?>
        <h2 class="newsletter-archives-title"><?php echo esc_html($atts['title']); ?></h2>
        <?php endif; ?>

        <div class="newsletter-list">
            <?php foreach ($campaigns as $campaign) : ?>

            <div class="newsletter-item">
                <div class="newsletter-header">
                    <h3 class="newsletter-name">
                        <?php if (!empty($campaign['mirror_url'])) : ?>
                        <a href="<?php echo esc_url($campaign['mirror_url']); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html($campaign['name']); ?>
                        </a>
                        <?php else : ?>
                        <?php echo esc_html($campaign['name']); ?>
                        <?php endif; ?>
                    </h3>
                    <span class="newsletter-date">
                        <?php echo date_i18n('j F Y', strtotime($campaign['sent_date'])); ?>
                    </span>
                </div>

                <?php if (!empty($campaign['subject'])) : ?>
                <p class="newsletter-subject">
                    <?php echo esc_html($campaign['subject']); ?>
                </p>
                <?php endif; ?>

                <?php if ($atts['show_stats'] === 'true') : ?>
                <div class="newsletter-stats">
                    <span class="stat-item">
                        📊 <?php echo number_format($campaign['recipients'], 0, ',', ' '); ?> destinataires
                    </span>
                    <span class="stat-item">
                        👁️ <?php echo $campaign['open_rate']; ?>% d'ouverture
                    </span>
                </div>
                <?php endif; ?>

                <div class="newsletter-links">
                    <?php if (!empty($campaign['mirror_url'])) : ?>
                    <a href="<?php echo esc_url($campaign['mirror_url']); ?>"
                       class="newsletter-link"
                       target="_blank"
                       rel="noopener">
                        🔗 Lire la newsletter →
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php endforeach; ?>
        </div>

    </div>

    

    <?php
    return ob_get_clean();
}

// ============================================
// INVALIDATION DU CACHE
// ============================================

/**
 * Invalider le cache quand une newsletter est envoyée
 */
add_action('brevo_newsletter_sent', 'brevo_invalidate_archives_cache');

function brevo_invalidate_archives_cache() {
    $config = get_brevo_config();
    $cache_key = 'brevo_all_campaigns_' . md5($config['api_key']);
    delete_transient($cache_key);
}