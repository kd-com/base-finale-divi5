<?php
/**
 * ========================================
 * MODULE STATISTIQUES NEWSLETTER
 * ========================================
 */

/**
 * Page des statistiques
 */
function brevo_newsletter_stats_page() {
    $config = get_brevo_config();
    $api_configured = is_brevo_configured();
    
    if (!$api_configured) {
        ?>
        <div class="wrap">
            <h1>📊 Statistiques</h1>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez d'abord <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">configurer votre API Brevo</a></p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Récupérer les statistiques
    $stats = brevo_get_global_stats();
    $campaigns = brevo_get_recent_campaigns(20);
    
    // Campagne sélectionnée
    $selected_campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : null;
    $campaign_details = null;
    
    if ($selected_campaign_id) {
        $campaign_details = brevo_get_campaign_details($selected_campaign_id);
    }
    
    ?>
    <div class="wrap">
        <h1>📊 Statistiques des newsletters</h1>
        
        <?php if (!$campaign_details) : ?>
        
        <!-- Vue globale -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            
            <!-- Campagnes envoyées -->
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Campagnes envoyées</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($stats['total_campaigns'], 0, ',', ' '); ?>
                </div>
            </div>
            
            <!-- Emails envoyés -->
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Emails envoyés</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($stats['total_sent'], 0, ',', ' '); ?>
                </div>
            </div>
            
            <!-- Taux d'ouverture moyen -->
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Taux d'ouverture moyen</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($stats['avg_open_rate'], 1); ?>%
                </div>
            </div>
            
            <!-- Taux de clic moyen -->
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Taux de clic moyen</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($stats['avg_click_rate'], 1); ?>%
                </div>
            </div>
            
        </div>
        
        <!-- Graphique des performances -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>📈 Évolution des performances</h2>
            <canvas id="performance-chart" width="400" height="100"></canvas>
        </div>
        
        <!-- Liste des campagnes -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>📋 Historique des campagnes</h2>
            
            <?php if (!empty($campaigns)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;">Campagne</th>
                        <th>Date d'envoi</th>
                        <th>Destinataires</th>
                        <th>Ouvertures</th>
                        <th>Clics</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($campaign['name']); ?></strong>
                            <div style="font-size: 12px; color: #666;">
                                <?php echo esc_html($campaign['subject']); ?>
                            </div>
                        </td>
                        <td><?php echo date_i18n('j M Y à H:i', strtotime($campaign['sent_date'])); ?></td>
                        <td><?php echo number_format($campaign['recipients'], 0, ',', ' '); ?></td>
                        <td>
                            <strong><?php echo $campaign['open_rate']; ?>%</strong>
                            <div style="font-size: 11px; color: #666;">
                                <?php echo number_format($campaign['unique_opens'], 0, ',', ' '); ?> ouvertures
                            </div>
                        </td>
                        <td>
                            <strong><?php echo $campaign['click_rate']; ?>%</strong>
                            <div style="font-size: 11px; color: #666;">
                                <?php echo number_format($campaign['unique_clicks'], 0, ',', ' '); ?> clics
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-stats&campaign_id=' . $campaign['id']); ?>" 
                               class="button button-small">
                                📊 Détails
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else : ?>
            <p style="text-align: center; color: #666; padding: 40px;">
                Aucune campagne envoyée pour le moment
            </p>
            <?php endif; ?>
        </div>
        
        <?php else : ?>
        
        <!-- Vue détaillée d'une campagne -->
        <div style="margin-bottom: 20px;">
            <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-stats'); ?>" class="button">
                ← Retour à la liste
            </a>
        </div>
        
        <div class="card" style="max-width: 100%; padding: 20px;">
            <h2><?php echo esc_html($campaign_details['name']); ?></h2>
            <p style="color: #666;">
                Envoyée le <?php echo date_i18n('j F Y à H:i', strtotime($campaign_details['sent_date'])); ?>
            </p>
            
            <!-- Stats principales -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin: 30px 0;">
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Destinataires</div>
                    <div style="font-size: 28px; font-weight: bold; color: #333;">
                        <?php echo number_format($campaign_details['recipients'], 0, ',', ' '); ?>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Taux de délivrabilité</div>
                    <div style="font-size: 28px; font-weight: bold; color: #28a745;">
                        <?php echo $campaign_details['delivery_rate']; ?>%
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Taux d'ouverture</div>
                    <div style="font-size: 28px; font-weight: bold; color: #007bff;">
                        <?php echo $campaign_details['open_rate']; ?>%
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                        <?php echo number_format($campaign_details['unique_opens'], 0, ',', ' '); ?> ouvertures uniques
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Taux de clic</div>
                    <div style="font-size: 28px; font-weight: bold; color: #6f42c1;">
                        <?php echo $campaign_details['click_rate']; ?>%
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                        <?php echo number_format($campaign_details['unique_clicks'], 0, ',', ' '); ?> clics uniques
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Désabonnements</div>
                    <div style="font-size: 28px; font-weight: bold; color: #dc3545;">
                        <?php echo number_format($campaign_details['unsubscribes'], 0, ',', ' '); ?>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                        <?php echo $campaign_details['unsubscribe_rate']; ?>%
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                    <div style="font-size: 12px; color: #666;">Bounces</div>
                    <div style="font-size: 28px; font-weight: bold; color: #fd7e14;">
                        <?php echo number_format($campaign_details['bounces'], 0, ',', ' '); ?>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">
                        <?php echo $campaign_details['bounce_rate']; ?>%
                    </div>
                </div>
                
            </div>
            
            <!-- Liens cliqués -->
            <?php if (!empty($campaign_details['clicked_links'])) : ?>
            <h3 style="margin-top: 40px;">🔗 Liens cliqués</h3>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Nombre de clics</th>
                        <th>Clics uniques</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaign_details['clicked_links'] as $link) : ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank">
                                <?php echo esc_html($link['url']); ?>
                            </a>
                        </td>
                        <td><?php echo number_format($link['total_clicks'], 0, ',', ' '); ?></td>
                        <td><?php echo number_format($link['unique_clicks'], 0, ',', ' '); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
        </div>
        
        <?php endif; ?>
        
    </div>
    
    <?php if (!$campaign_details && !empty($campaigns)) : ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('performance-chart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_reverse(array_column($campaigns, 'sent_date_short'))); ?>,
            datasets: [
                {
                    label: 'Taux d\'ouverture (%)',
                    data: <?php echo json_encode(array_reverse(array_column($campaigns, 'open_rate'))); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Taux de clic (%)',
                    data: <?php echo json_encode(array_reverse(array_column($campaigns, 'click_rate'))); ?>,
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    </script>
    <?php endif; ?>
    
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

/**
 * Récupérer les statistiques globales
 */
function brevo_get_global_stats() {
    $config = get_brevo_config();
    
    $response = wp_remote_get('https://api.brevo.com/v3/emailCampaigns?limit=50&sort=desc', array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        error_log('Brevo Stats Error: ' . $response->get_error_message());
        return array(
            'total_campaigns' => 0,
            'total_sent' => 0,
            'avg_open_rate' => 0,
            'avg_click_rate' => 0
        );
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Debug: voir la structure complète de la réponse
    error_log('Brevo API Response: ' . print_r($body, true));
    
    if (empty($body['campaigns'])) {
        error_log('Brevo Stats: No campaigns found in response');
        return array(
            'total_campaigns' => 0,
            'total_sent' => 0,
            'avg_open_rate' => 0,
            'avg_click_rate' => 0
        );
    }
    
    $campaigns = $body['campaigns'];
    $total_campaigns = 0;
    $total_sent = 0;
    $total_open_rate = 0;
    $total_click_rate = 0;
    
    foreach ($campaigns as $campaign) {
        // Debug: voir la structure de chaque campagne
        error_log('Campaign structure: ' . print_r($campaign, true));
        
        // Ne compter que les campagnes envoyées
        if (isset($campaign['status']) && $campaign['status'] === 'sent') {
            
            // Les stats sont dans campaignStats, pas globalStats
            $sent = 0;
            $unique_views = 0;
            $unique_clicks = 0;
            
            // Agréger les stats de toutes les listes
            if (isset($campaign['statistics']['campaignStats']) && is_array($campaign['statistics']['campaignStats'])) {
                foreach ($campaign['statistics']['campaignStats'] as $list_stats) {
                    $sent += isset($list_stats['sent']) ? $list_stats['sent'] : 0;
                    $unique_views += isset($list_stats['uniqueViews']) ? $list_stats['uniqueViews'] : 0;
                    $unique_clicks += isset($list_stats['uniqueClicks']) ? $list_stats['uniqueClicks'] : 0;
                }
            }
            
            if ($sent > 0) {
                $total_campaigns++;
                $total_sent += $sent;
                $total_open_rate += ($unique_views / $sent * 100);
                $total_click_rate += ($unique_clicks / $sent * 100);
            }
        }
    }
    
    error_log('Stats calculated - Campaigns: ' . $total_campaigns . ', Sent: ' . $total_sent);
    
    return array(
        'total_campaigns' => $total_campaigns,
        'total_sent' => $total_sent,
        'avg_open_rate' => $total_campaigns > 0 ? ($total_open_rate / $total_campaigns) : 0,
        'avg_click_rate' => $total_campaigns > 0 ? ($total_click_rate / $total_campaigns) : 0
    );
}

/**
 * Récupérer les campagnes récentes
 */
function brevo_get_recent_campaigns($limit = 10) {
    $config = get_brevo_config();
    
    $response = wp_remote_get('https://api.brevo.com/v3/emailCampaigns?limit=' . $limit . '&sort=desc', array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        error_log('Brevo Campaigns Error: ' . $response->get_error_message());
        return array();
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Debug
    error_log('Brevo Campaigns Response: ' . print_r($body, true));
    
    if (empty($body['campaigns'])) {
        error_log('Brevo: No campaigns in response');
        return array();
    }
    
    $campaigns = array();
    
    foreach ($body['campaigns'] as $campaign) {
        // Debug: structure de chaque campagne
        error_log('Processing campaign: ' . print_r($campaign, true));
        
        // Vérifier le statut de la campagne
        if (!isset($campaign['status']) || $campaign['status'] !== 'sent') {
            error_log('Campaign skipped - Status: ' . ($campaign['status'] ?? 'unknown'));
            continue;
        }
        
        if (empty($campaign['statistics'])) {
            error_log('Campaign skipped - No statistics');
            continue;
        }
        
        // Les stats sont dans campaignStats, pas globalStats
        $sent = 0;
        $unique_views = 0;
        $unique_clicks = 0;
        
        // Agréger les stats de toutes les listes
        if (isset($campaign['statistics']['campaignStats']) && is_array($campaign['statistics']['campaignStats'])) {
            foreach ($campaign['statistics']['campaignStats'] as $list_stats) {
                $sent += isset($list_stats['sent']) ? $list_stats['sent'] : 0;
                $unique_views += isset($list_stats['uniqueViews']) ? $list_stats['uniqueViews'] : 0;
                $unique_clicks += isset($list_stats['uniqueClicks']) ? $list_stats['uniqueClicks'] : 0;
            }
        }
        
        if ($sent == 0) {
            error_log('Campaign skipped - Sent is 0');
            continue;
        }
        
        $campaigns[] = array(
            'id' => $campaign['id'],
            'name' => $campaign['name'],
            'subject' => isset($campaign['subject']) ? $campaign['subject'] : '',
            'sent_date' => $campaign['sentDate'],
            'sent_date_short' => date('j M', strtotime($campaign['sentDate'])),
            'recipients' => $sent,
            'unique_opens' => $unique_views,
            'unique_clicks' => $unique_clicks,
            'open_rate' => round(($unique_views / $sent * 100), 1),
            'click_rate' => round(($unique_clicks / $sent * 100), 1)
        );
    }
    
    error_log('Total campaigns processed: ' . count($campaigns));
    
    return $campaigns;
}

/**
 * Récupérer les détails d'une campagne
 */
function brevo_get_campaign_details($campaign_id) {
    $config = get_brevo_config();
    
    $response = wp_remote_get('https://api.brevo.com/v3/emailCampaigns/' . $campaign_id, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        return null;
    }
    
    $campaign = json_decode(wp_remote_retrieve_body($response), true);
    
    if (empty($campaign['statistics'])) {
        return null;
    }
    
    // Les stats sont dans campaignStats, pas globalStats
    $sent = 0;
    $delivered = 0;
    $unique_views = 0;
    $unique_clicks = 0;
    $unsubscriptions = 0;
    $hard_bounces = 0;
    
    // Agréger les stats de toutes les listes
    if (isset($campaign['statistics']['campaignStats']) && is_array($campaign['statistics']['campaignStats'])) {
        foreach ($campaign['statistics']['campaignStats'] as $list_stats) {
            $sent += isset($list_stats['sent']) ? $list_stats['sent'] : 0;
            $delivered += isset($list_stats['delivered']) ? $list_stats['delivered'] : 0;
            $unique_views += isset($list_stats['uniqueViews']) ? $list_stats['uniqueViews'] : 0;
            $unique_clicks += isset($list_stats['uniqueClicks']) ? $list_stats['uniqueClicks'] : 0;
            $unsubscriptions += isset($list_stats['unsubscriptions']) ? $list_stats['unsubscriptions'] : 0;
            $hard_bounces += isset($list_stats['hardBounces']) ? $list_stats['hardBounces'] : 0;
        }
    }
    
    if ($sent == 0) {
        return null;
    }
    
    $details = array(
        'id' => $campaign['id'],
        'name' => $campaign['name'],
        'subject' => isset($campaign['subject']) ? $campaign['subject'] : '',
        'sent_date' => $campaign['sentDate'],
        'recipients' => $sent,
        'delivered' => $delivered,
        'delivery_rate' => round(($delivered / $sent * 100), 1),
        'unique_opens' => $unique_views,
        'open_rate' => round(($unique_views / $sent * 100), 1),
        'unique_clicks' => $unique_clicks,
        'click_rate' => round(($unique_clicks / $sent * 100), 1),
        'unsubscribes' => $unsubscriptions,
        'unsubscribe_rate' => round(($unsubscriptions / $sent * 100), 1),
        'bounces' => $hard_bounces,
        'bounce_rate' => round(($hard_bounces / $sent * 100), 1),
        'clicked_links' => array()
    );
    
    // Récupérer les liens cliqués depuis linksStats
    if (isset($campaign['statistics']['linksStats'])) {
        foreach ($campaign['statistics']['linksStats'] as $url => $clicks) {
            if ($clicks > 0) {
                $details['clicked_links'][] = array(
                    'url' => $url,
                    'total_clicks' => $clicks,
                    'unique_clicks' => $clicks // Brevo ne fournit pas de distinction ici
                );
            }
        }
    }
    
    return $details;
}

/**
 * Récupérer le nombre d'abonnés
 */
function brevo_get_subscribers_count() {
    $config = get_brevo_config();
    
    $response = wp_remote_get('https://api.brevo.com/v3/contacts/lists/' . $config['list_id'], array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        return 0;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    return isset($body['totalSubscribers']) ? $body['totalSubscribers'] : 0;
}

/**
 * Récupérer les statistiques récentes pour le dashboard
 */
function brevo_get_recent_stats() {
    $campaigns = brevo_get_recent_campaigns(1);
    
    if (empty($campaigns)) {
        return array('last_campaign_open_rate' => 0);
    }
    
    return array(
        'last_campaign_open_rate' => $campaigns[0]['open_rate']
    );
}

/**
 * Tester la connexion à l'API
 */
function brevo_test_api_connection() {
    $config = get_brevo_config();
    
    $response = wp_remote_get('https://api.brevo.com/v3/account', array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key']
        ),
        'timeout' => 10
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Erreur de connexion : ' . $response->get_error_message()
        );
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code === 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return array(
            'success' => true,
            'message' => '✅ Connexion réussie ! Compte : ' . $body['email']
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Erreur API : Code ' . $status_code
        );
    }
}