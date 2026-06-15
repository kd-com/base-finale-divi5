<?php
/**
 * ========================================
 * PAGE TABLEAU DE BORD NEWSLETTER
 * ========================================
 */

function brevo_newsletter_dashboard_page() {
    $config = get_brevo_config();
    $is_configured = is_brevo_configured();
    
    // Récupérer les statistiques récentes
    $stats = brevo_get_recent_stats();
    $subscribers_count = brevo_get_subscribers_count();
    
    ?>
    <div class="wrap">
        <h1>📧 Newsletter - Tableau de bord</h1>
        
        <?php if (!$is_configured) : ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez configurer votre API Brevo dans <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">Configuration</a></p>
            </div>
        <?php endif; ?>
        
        <!-- Cartes statistiques -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
            
            <!-- Abonnés -->
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Abonnés actifs</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo number_format($subscribers_count, 0, ',', ' '); ?>
                </div>
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-subscribers'); ?>" 
                   style="color: white; text-decoration: none; opacity: 0.9;">
                    Voir la liste →
                </a>
            </div>
            
            <!-- Dernière campagne -->
            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Dernière campagne</div>
                <div style="font-size: 20px; font-weight: bold; margin: 10px 0;">
                    <?php 
                    $last_sent = get_option('brevo_newsletter_last_sent', 'Jamais');
                    echo $last_sent !== 'Jamais' ? date_i18n('j M Y', strtotime($last_sent)) : 'Jamais';
                    ?>
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    Taux d'ouverture: <?php echo isset($stats['last_campaign_open_rate']) ? $stats['last_campaign_open_rate'] . '%' : 'N/A'; ?>
                </div>
            </div>
            
            <!-- Nouveaux contenus -->
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Nouveaux contenus</div>
                <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">
                    <?php echo has_new_posts_to_send() ? '✓' : '—'; ?>
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    <?php echo has_new_posts_to_send() ? 'Prêt à envoyer' : 'Aucun nouveau contenu'; ?>
                </div>
            </div>
            
            <!-- Envoi automatique -->
            <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 14px; opacity: 0.9;">Envoi automatique</div>
                <div style="font-size: 20px; font-weight: bold; margin: 10px 0;">
                    <?php 
                    $auto_send = get_option('brevo_newsletter_auto_send', '0');
                    echo $auto_send === '1' ? '✓ Activé' : '— Désactivé';
                    ?>
                </div>
                <?php if ($auto_send === '1') : 
                    $send_day = get_option('brevo_newsletter_send_day', 1);
                    $send_hour = get_option('brevo_newsletter_send_hour', 9);
                    $send_minute = get_option('brevo_newsletter_send_minute', 0);
                ?>
                <div style="font-size: 12px; opacity: 0.9;">
                    Le <?php echo $send_day; ?> à <?php echo sprintf('%02d:%02d', $send_hour, $send_minute); ?>
                </div>
                <?php endif; ?>
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
                   style="color: white; text-decoration: none; opacity: 0.9;">
                    Configurer →
                </a>
            </div>
            
        </div>
        
        <!-- Actions rapides -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>⚡ Actions rapides</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-libre'); ?>" 
                   class="button button-primary button-large">
                    ✉️ Créer une newsletter
                </a>
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
                   class="button button-secondary button-large">
                    ⚙️ Newsletter automatique
                </a>
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-stats'); ?>" 
                   class="button button-secondary button-large">
                    📊 Voir les statistiques
                </a>
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-subscribers'); ?>" 
                   class="button button-secondary button-large">
                    👥 Gérer les abonnés
                </a>
            </div>
        </div>
        
        <?php if ($is_configured) : ?>
        
        <!-- Prochain envoi planifié -->
        <?php if ($auto_send === '1') : 
            $send_day = get_option('brevo_newsletter_send_day', 1);
            $send_hour = get_option('brevo_newsletter_send_hour', 9);
            $send_minute = get_option('brevo_newsletter_send_minute', 0);
            $next_send = brevo_get_next_send_date($send_day, $send_hour, $send_minute);
        ?>
        <div class="card" style="margin-top: 20px; padding: 20px; background: #f0f9ff; border-left: 4px solid #0ea5e9;">
            <h3 style="margin-top: 0; color: #0c4a6e;">📅 Prochain envoi planifié</h3>
            <p style="font-size: 18px; margin: 10px 0;">
                <strong><?php echo date_i18n('l j F Y à H:i', $next_send); ?></strong>
            </p>
            <p style="margin: 0; color: #666;">
                <?php if (has_new_posts_to_send()) : ?>
                    ✅ Du nouveau contenu est disponible pour l'envoi
                <?php else : ?>
                    ⚠️ Aucun nouveau contenu - L'envoi sera annulé automatiquement
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Dernières campagnes -->
        <?php 
        $recent_campaigns = brevo_get_recent_campaigns(5);
        if (!empty($recent_campaigns)) :
        ?>
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
            <h2>📋 Dernières campagnes</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 35%;">Nom</th>
                        <th>Date d'envoi</th>
                        <th>Destinataires</th>
                        <th>Taux d'ouverture</th>
                        <th>Taux de clic</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_campaigns as $campaign) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($campaign['name']); ?></strong>
                            <?php if (!empty($campaign['subject'])) : ?>
                            <div style="font-size: 12px; color: #666;">
                                <?php echo esc_html($campaign['subject']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date_i18n('j M Y', strtotime($campaign['sent_date'])); ?></td>
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
                            <strong style="color: <?php echo $campaign['click_rate'] > 2 ? '#28a745' : '#6c757d'; ?>;">
                                <?php echo $campaign['click_rate']; ?>%
                            </strong>
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
            
            <p style="margin-top: 15px; text-align: center;">
                <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-stats'); ?>" class="button">
                    Voir toutes les statistiques →
                </a>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Guide de démarrage -->
        <?php if (!$is_configured || empty($recent_campaigns)) : ?>
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px; background: #fff7ed; border-left: 4px solid #f97316;">
            <h2 style="margin-top: 0; color: #9a3412;">🚀 Guide de démarrage rapide</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <!-- Étape 1 -->
                <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #fed7aa;">
                    <div style="font-size: 24px; margin-bottom: 10px;">1️⃣</div>
                    <h3 style="margin: 0 0 10px 0; font-size: 16px;">Configurer l'API</h3>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        Connectez votre compte Brevo pour commencer
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>" 
                       class="button button-small">
                        Configuration →
                    </a>
                </div>
                
                <!-- Étape 2 -->
                <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #fed7aa;">
                    <div style="font-size: 24px; margin-bottom: 10px;">2️⃣</div>
                    <h3 style="margin: 0 0 10px 0; font-size: 16px;">Ajouter le formulaire</h3>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        Intégrez le formulaire d'inscription sur votre site
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-subscribers'); ?>" 
                       class="button button-small">
                        Voir le shortcode →
                    </a>
                </div>
                
                <!-- Étape 3 -->
                <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #fed7aa;">
                    <div style="font-size: 24px; margin-bottom: 10px;">3️⃣</div>
                    <h3 style="margin: 0 0 10px 0; font-size: 16px;">Créer une newsletter</h3>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        Envoyez votre première newsletter
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-libre'); ?>" 
                       class="button button-small">
                        Créer →
                    </a>
                </div>
                
                <!-- Étape 4 -->
                <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #fed7aa;">
                    <div style="font-size: 24px; margin-bottom: 10px;">4️⃣</div>
                    <h3 style="margin: 0 0 10px 0; font-size: 16px;">Automatiser l'envoi</h3>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        Programmez vos newsletters mensuelles
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
                       class="button button-small">
                        Automatiser →
                    </a>
                </div>
                
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Conseils et astuces -->
        <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px; background: #f8fafc;">
            <h2>💡 Conseils et astuces</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 15px;">
                
                <div style="padding: 15px; background: white; border-radius: 6px; border-left: 3px solid #0ea5e9;">
                    <h4 style="margin: 0 0 8px 0; color: #0c4a6e;">📈 Optimisez vos taux d'ouverture</h4>
                    <p style="margin: 0; font-size: 13px; color: #666;">
                        • Personnalisez l'objet de vos emails<br>
                        • Envoyez au bon moment (9h-11h ou 14h-16h)<br>
                        • Testez différents jours de la semaine
                    </p>
                </div>
                
                <div style="padding: 15px; background: white; border-radius: 6px; border-left: 3px solid #8b5cf6;">
                    <h4 style="margin: 0 0 8px 0; color: #5b21b6;">✏️ Créez du contenu engageant</h4>
                    <p style="margin: 0; font-size: 13px; color: #666;">
                        • Variez les types de contenu (texte, images, boutons)<br>
                        • Gardez vos messages concis et clairs<br>
                        • Utilisez des appels à l'action visibles
                    </p>
                </div>
                
                <div style="padding: 15px; background: white; border-radius: 6px; border-left: 3px solid #10b981;">
                    <h4 style="margin: 0 0 8px 0; color: #065f46;">🎯 Surveillez vos performances</h4>
                    <p style="margin: 0; font-size: 13px; color: #666;">
                        • Taux d'ouverture idéal : >20%<br>
                        • Taux de clic idéal : >2%<br>
                        • Analysez les liens les plus cliqués
                    </p>
                </div>
                
            </div>
        </div>
        
        <?php endif; ?>
        
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
        .button-large {
            padding: 8px 16px !important;
            height: auto !important;
            line-height: 1.5 !important;
        }
    </style>
    <?php
}