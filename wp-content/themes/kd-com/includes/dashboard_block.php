<?php
/**
 * Widget Dashboard personnalisé KD-COM
 * 
 * Affiche un tableau de bord personnalisé avec :
 * - En-tête graphique avec logo KD-COM
 * - Cartes Documentation, Contact, Support/Maintenance
 * - Statistiques de la dernière newsletter (si module activé)
 * - Formulaire de support (si maintenance activée)
 * - Guides d'utilisation Pages et Articles
 */

if (!defined('ABSPATH')) {
  exit; // Sécurité : empêche l'accès direct
}

// Register our custom dashboard widget.
function kd_add_my_dashboard_widget() {
  global $wp_meta_boxes;
  
  // Supprimer tous les widgets existants du tableau de bord
  $wp_meta_boxes['dashboard']['normal']['core'] = array();
  $wp_meta_boxes['dashboard']['side']['core'] = array();
  $wp_meta_boxes['dashboard']['column3']['core'] = array();
  $wp_meta_boxes['dashboard']['column4']['core'] = array();
  
  // Ajouter notre widget personnalisé
  wp_add_dashboard_widget(
    'kd-theme-presentation',
    'Présentation du thème KD-COM',
    'kd_render_my_dashboard_widget',
    null,
    null,
    'normal',
    'high'
  );
}
add_action('wp_dashboard_setup', 'kd_add_my_dashboard_widget', 10);

// Fonction de rendu du widget
function kd_render_my_dashboard_widget() {
  $maintenance_kd = get_option('maintenance_kd_com', '0') === '1';
  $logo_url = get_bloginfo('stylesheet_directory') . '/img/logo_admin.png';
  
  // Vérifier si le module newsletter est activé
  $newsletter_module_enabled = get_option('module_newsletter', '0') === '1';
  
  // Récupérer les statistiques de la dernière newsletter si le module est activé
  $newsletter_stats = null;
  if ($newsletter_module_enabled && function_exists('brevo_get_recent_campaigns')) {
    $recent_campaigns = brevo_get_recent_campaigns(1);
    if (!empty($recent_campaigns)) {
      $newsletter_stats = $recent_campaigns[0];
    }
  }
  
  // Récupérer les informations sur la newsletter automatique programmée
  $auto_newsletter_enabled = false;
  $next_send_date = null;
  $has_new_content = false;
  $new_posts_count = 0;
  $new_events_count = 0;
  
  if ($newsletter_module_enabled) {
    $auto_send = get_option('brevo_newsletter_auto_send', '0');
    if ($auto_send === '1') {
      $auto_newsletter_enabled = true;
      $send_day = get_option('brevo_newsletter_send_day', 1);
      $send_hour = get_option('brevo_newsletter_send_hour', 9);
      $send_minute = get_option('brevo_newsletter_send_minute', 0);
      
      // Calculer la date du prochain envoi
      if (function_exists('brevo_get_next_send_date')) {
        $next_send_date = brevo_get_next_send_date($send_day, $send_hour, $send_minute);
      }
      
      // Vérifier s'il y a du nouveau contenu
      if (function_exists('has_new_posts_to_send')) {
        $has_new_content = has_new_posts_to_send();
      }
      
      // Compter les nouveaux posts
      if (function_exists('get_sent_posts')) {
        $sent_posts = get_sent_posts();
        
        // Compter les nouveaux articles
        $news_query = new WP_Query(array(
          'post_type' => 'post',
          'posts_per_page' => -1,
          'post_status' => 'publish',
          'post__not_in' => $sent_posts,
          'fields' => 'ids'
        ));
        $new_posts_count = $news_query->found_posts;
        wp_reset_postdata();
        
        // Compter les nouveaux événements si le module est activé
        $events_module_enabled = get_option('module_cpt_evenements', '0') === '1';
        if ($events_module_enabled) {
          $today = date('Y-m-d');
          $events_query = new WP_Query(array(
            'post_type' => 'evenements',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'meta_value',
            'meta_key' => 'event_date',
            'order' => 'ASC',
            'meta_type' => 'DATE',
            'meta_query' => array(
              array(
                'key' => 'event_date',
                'value' => $today,
                'compare' => '>=',
                'type' => 'DATE',
              ),
            ),
            //'post__not_in' => $sent_posts,
            'fields' => 'ids'
          ));
          $new_events_count = $events_query->found_posts;
          wp_reset_postdata();
        }
      }
    }
  }
  ?>
  <div class="kd-dashboard-widget" style="padding: 0; max-width: 100%;">
    <!-- En-tête graphique avec logo -->
    <div style="background: linear-gradient(135deg, #1a2332 0%, #2d3a4d 100%); color: white; padding: 0; border-radius: 8px 8px 0 0; margin-bottom: 20px; position: relative; overflow: hidden; min-height: 180px;">
      <!-- Motif géométrique de fond -->
      <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,107,53,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
      <div style="position: absolute; bottom: -30px; left: -30px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,107,53,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
      
      <!-- Barre décorative orange -->
      <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #e64449 0%, #e64449 100%);"></div>
      
      <!-- Contenu principal -->
      <div style="position: relative; z-index: 2; padding: 40px 30px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 30px;">
          <!-- Logo et texte -->
          <div style="display: flex; align-items: center; gap: 25px; flex: 1;">
            <div style="background: white; padding: 15px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); border: 3px solid #e64449;">
              <img src="<?php echo esc_url($logo_url); ?>" alt="KD-COM Logo" style="height: 70px; display: block;">
            </div>
            <div>
              <h2 style="margin: 0 0 8px 0; color: white; font-size: 32px; font-weight: 700; text-shadow: 0 2px 8px rgba(0,0,0,0.3); letter-spacing: -0.5px;">Thème KD-COM</h2>
              <p style="margin: 0; color: #e64449; font-size: 16px; font-weight: 500;">Votre thème WordPress professionnel optimisé</p>
            </div>
          </div>
          
          <!-- Badge version ou info -->
          <div style="background: rgba(255,107,53,0.2); backdrop-filter: blur(10px); padding: 12px 20px; border-radius: 8px; border: 1px solid rgba(255,107,53,0.3);">
            <div style="font-size: 11px; color: #e64449; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Powered by</div>
            <div style="font-size: 14px; color: white; font-weight: 600;">KD-COM<br><small><strong>20 rue de Bretagne 53240 Andouillé</strong></small></div>
          </div>
        </div>
      </div>
    </div>
    
    <div style="padding: 0 15px 15px 15px;">
      <div style="background: #f8fafc; padding: 25px; border-radius: 8px; border-left: 4px solid #e64449; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; color: #1a2332; font-size: 20px; font-weight: 600;">👋 Bienvenue sur votre site internet !</h3>
        <p style="line-height: 1.6; color: #64748b; font-size: 15px; margin: 0;">
          Vous disposez d'un thème WordPress conçu par KD-COM avec toutes les fonctionnalités modernes 
          pour gérer facilement votre contenu : blocs Gutenberg personnalisés, gestion des couleurs, 
          animations, et bien plus encore.
        </p>
      </div>

      <?php if ($newsletter_module_enabled && $newsletter_stats) : ?>
      <!-- Statistiques de la dernière newsletter -->
      <div style="background: linear-gradient(135deg, #3c434a 0%, #263143 100%); padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
          <h3 style="margin: 0; color: white; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            📧 Dernière newsletter envoyée
          </h3>
          <a href="<?php echo admin_url('admin.php?page=brevo-newsletter'); ?>" 
             style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.3s ease;">
            Voir tout →
          </a>
        </div>
        
        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 15px;">
          <div style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">
            <?php echo esc_html($newsletter_stats['name']); ?>
          </div>
          <?php if (!empty($newsletter_stats['subject'])) : ?>
          <div style="font-size: 13px; opacity: 0.9;">
            <?php echo esc_html($newsletter_stats['subject']); ?>
          </div>
          <?php endif; ?>
          <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">
            📅 <?php echo date_i18n('j F Y à H:i', strtotime($newsletter_stats['sent_date'])); ?>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
          <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px; text-align: center;">
            <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">Destinataires</div>
            <div style="font-size: 24px; font-weight: 700;">
              <?php echo number_format($newsletter_stats['recipients'], 0, ',', ' '); ?>
            </div>
          </div>
          
          <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px; text-align: center;">
            <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">Taux d'ouverture</div>
            <div style="font-size: 24px; font-weight: 700;">
              <?php echo $newsletter_stats['open_rate']; ?>%
            </div>
            <div style="font-size: 11px; opacity: 0.8; margin-top: 3px;">
              <?php echo number_format($newsletter_stats['unique_opens'], 0, ',', ' '); ?> ouvertures
            </div>
          </div>
          
          <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px; text-align: center;">
            <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">Taux de clic</div>
            <div style="font-size: 24px; font-weight: 700;">
              <?php echo $newsletter_stats['click_rate']; ?>%
            </div>
            <div style="font-size: 11px; opacity: 0.8; margin-top: 3px;">
              <?php echo number_format($newsletter_stats['unique_clicks'], 0, ',', ' '); ?> clics
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($auto_newsletter_enabled && $next_send_date) : ?>
      <!-- Newsletter automatique programmée -->
      <div style="background: linear-gradient(135deg, #3c434a 0%, #263143 100%); padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
          <h3 style="margin: 0; color: white; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            🗓️ Prochaine newsletter automatique
          </h3>
          <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
             style="background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.3s ease;">
            Configurer →
          </a>
        </div>
        
        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 6px; margin-bottom: 15px;">
          <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            <div style="background: rgba(255,255,255,0.2); padding: 15px 20px; border-radius: 8px; text-align: center; min-width: 140px;">
              <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">📅 Date</div>
              <div style="font-size: 18px; font-weight: 700;">
                <?php echo date_i18n('j F Y', $next_send_date); ?>
              </div>
            </div>
            
            <div style="background: rgba(255,255,255,0.2); padding: 15px 20px; border-radius: 8px; text-align: center; min-width: 120px;">
              <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">🕐 Heure</div>
              <div style="font-size: 18px; font-weight: 700;">
                <?php echo date_i18n('H:i', $next_send_date); ?>
              </div>
            </div>
            
            <div style="flex: 1; background: rgba(255,255,255,0.2); padding: 15px 20px; border-radius: 8px;">
              <div style="font-size: 11px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">⏱️ Temps restant</div>
              <div style="font-size: 16px; font-weight: 700;">
                <?php
                $now = current_time('timestamp');
                $diff = $next_send_date - $now;
                
                if ($diff > 0) {
                  $days = floor($diff / (60 * 60 * 24));
                  $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                  
                  if ($days > 0) {
                    echo $days . ' jour' . ($days > 1 ? 's' : '');
                    if ($hours > 0) {
                      echo ' et ' . $hours . 'h';
                    }
                  } else if ($hours > 0) {
                    echo $hours . ' heure' . ($hours > 1 ? 's' : '');
                  } else {
                    echo 'Moins d\'une heure';
                  }
                } else {
                  echo 'En cours...';
                }
                ?>
              </div>
            </div>
          </div>
          
          <?php if ($has_new_content) : ?>
          <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 6px; border-left: 3px solid rgba(255,255,255,0.5);">
            <div style="font-size: 13px; font-weight: 600; margin-bottom: 10px;">
              ✅ Contenu prêt à être envoyé :
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
              <?php if ($new_posts_count > 0) : ?>
              <div style="background: rgba(255,255,255,0.15); padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">📰</span>
                <div>
                  <div style="font-size: 18px; font-weight: 700;"><?php echo $new_posts_count; ?></div>
                  <div style="font-size: 11px; opacity: 0.9;">Article<?php echo $new_posts_count > 1 ? 's' : ''; ?></div>
                </div>
              </div>
              <?php endif; ?>
              
              <?php if ($new_events_count > 0) : ?>
              <div style="background: rgba(255,255,255,0.15); padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🎉</span>
                <div>
                  <div style="font-size: 18px; font-weight: 700;"><?php echo $new_events_count; ?></div>
                  <div style="font-size: 11px; opacity: 0.9;">Événement<?php echo $new_events_count > 1 ? 's' : ''; ?></div>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php else : ?>
          <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px; border-left: 3px solid rgba(255,200,0,0.7); display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">⚠️</span>
            <div style="flex: 1;">
              <div style="font-size: 13px; font-weight: 600; margin-bottom: 3px;">
                Aucun nouveau contenu
              </div>
              <div style="font-size: 12px; opacity: 0.9;">
                L'envoi sera automatiquement annulé s'il n'y a pas de nouveaux articles ou événements
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
             class="button" 
             style="background: rgba(255,255,255,0.9); color: #f5576c; border: none; padding: 10px 20px; text-decoration: none; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
            <span>⚙️</span> Modifier la programmation
          </a>
          <?php if ($has_new_content) : ?>
          <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-auto'); ?>" 
             class="button" 
             style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 20px; text-decoration: none; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
            <span>👁️</span> Prévisualiser le contenu
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
          <h4 style="margin: 0 0 10px 0; color: #1a2332; font-size: 18px; font-weight: 600;">📚 Documentation</h4>
          <p style="font-size: 14px; color: #64748b; margin-bottom: 15px;">Consultez le guide d'utilisation complet</p>
         
          <p style="font-size: 12px; color: #555; line-height: 1.5; margin-bottom: 15px;">
            Ce guide vous aidera à maîtriser toutes les fonctionnalités de votre site.
            <?php if ($newsletter_module_enabled) : ?>
            Vous avez également accès au guide pour la gestion de vos newsletters.
            <?php endif; ?>
          </p>
          <a href="https://acrobat.adobe.com/link/spaces/urn%3Aaaid%3Asc%3AEU%3A64d5236a-7552-4a05-b586-fa9bb3035f05/assets/urn%3Aaaid%3Asc%3AEU%3A9c370504-1b55-55e2-a590-26250d82defe" target="_blank" class="button button-primary" style="text-decoration: none; background: #e64449; border-color: #e64449;">
            Télécharger le guide
          </a>
          <?php if ($newsletter_module_enabled) : ?>
          <a href="https://acrobat.adobe.com/link/spaces/urn:aaid:sc:EU:64d5236a-7552-4a05-b586-fa9bb3035f05/assets/urn:aaid:sc:EU:82514d16-6315-5bc3-adac-dfd34fdd8e41" target="_blank" class="button" style="text-decoration: none; background: rgba(255,100,69,0.1); border: 1px solid rgba(255,100,69,0.3); color: #e64449; margin-left: 10px;">
            Guide Newsletter
          </a>
          <?php endif; ?>
        </div>
      
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
          <h4 style="margin: 0 0 10px 0; color: #1a2332; font-size: 18px; font-weight: 600;">📞 Contact</h4>
          <p style="font-size: 14px; color: #64748b; margin-bottom: 15px;">Besoin d'aide ? Contactez-nous</p>
          <a href="tel:0626656741" class="button button-secondary" style="text-decoration: none; background: #1a2332; border-color: #1a2332; color: white;">
            📱 06 26 65 67 41
          </a>
        </div>

        <?php if ($maintenance_kd) : ?>
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
          <h4 style="margin: 0 0 10px 0; color: #1a2332; font-size: 18px; font-weight: 600;">✉️ Demande de support</h4>
          <div style="background: #f0f9ff; padding: 12px; border-radius: 6px; border-left: 3px solid #0ea5e9; margin-bottom: 12px;">
            <p style="font-size: 12px; color: #0c4a6e; margin: 0; line-height: 1.5;">
              <strong>🛡️ Site sous contrat de maintenance</strong><br>
              Votre site est maintenu par <strong>KD-COM</strong>. Profitez d'un support technique réactif pour toute question ou problème.
            </p>
          </div>
          <p style="font-size: 14px; color: #64748b; margin-bottom: 15px;">Envoyez-nous votre demande par email</p>
          <button onclick="document.getElementById('kd-support-form-modal').style.display='block'" class="button button-primary" style="text-decoration: none; border: none; cursor: pointer; background: #FF6B35; border-color: #FF6B35;">
            Formulaire de contact
          </button>
        </div>
        <?php else : ?>
        <div style="background: #f0fff4; padding: 20px; border-radius: 8px; border: 1px solid #86efac; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
          <h4 style="margin: 0 0 10px 0; color: #16a34a; font-size: 18px; font-weight: 600;">🛡️ Maintenance KD-COM</h4>
          <p style="font-size: 13px; color: #555; line-height: 1.6; margin: 0 0 10px 0;">
            Profitez d'une maintenance professionnelle de votre site WordPress avec <strong>KD-COM</strong> : 
            sauvegardes automatiques, mises à jour sécurisées, surveillance 24/7 et support technique réactif.
          </p>
          <p style="font-size: 12px; color: #16a34a; font-weight: bold; margin: 0; padding: 8px; background: rgba(22, 163, 74, 0.1); border-radius: 4px;">
            📅 Engagement d'un an de janvier à décembre<br>
            💰 Facturation annuelle renouvelable
          </p>
        </div>
        <?php endif; ?>
      </div>

    <?php if ($maintenance_kd) : ?>
    <!-- Modal du formulaire de support -->
    <div id="kd-support-form-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
      <div style="background-color: white; margin: 5% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; position: relative;">
        <span onclick="document.getElementById('kd-support-form-modal').style.display='none'" style="position: absolute; right: 20px; top: 15px; font-size: 28px; font-weight: bold; cursor: pointer; color: #999;">&times;</span>
        <h3 style="margin-top: 0; color: #1a2332;">Demande de support</h3>
        <iframe src="https://ubiquitous-wallaby-823.notion.site/ebd//8b656f12f65e4f028630067a133a550e" width="100%" height="600" frameborder="0" allowfullscreen></iframe>
      </div>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
      <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="margin: 0 0 10px 0; color: #1a2332; font-size: 18px; font-weight: 600;">📄 Utilisation des Pages</h4>
        <ul style="font-size: 13px; color: #64748b; line-height: 1.8; margin: 0; padding-left: 20px;">
          <li>Les pages sont utilisées pour le contenu statique (À propos, Contact, Services...)</li>
          <li>Elles peuvent être organisées de manière hiérarchique (pages parentes/enfants)</li>
          <li>Idéales pour créer la structure principale de votre site</li>
          <li>Ajoutez une page via <strong style="color: #1a2332;">Pages > Ajouter</strong></li>
        </ul>
      </div>
      
      <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="margin: 0 0 10px 0; color: #1a2332; font-size: 18px; font-weight: 600;">📰 Utilisation des Articles</h4>
        <ul style="font-size: 13px; color: #64748b; line-height: 1.8; margin: 0; padding-left: 20px;">
          <li>Les articles sont parfaits pour le contenu régulier (actualités, blog...)</li>
          <li>Ils sont organisés par date de publication</li>
          <li>Possibilité de les classer par catégories et étiquettes</li>
          <li>Créez un article via <strong style="color: #1a2332;">Articles > Ajouter</strong></li>
        </ul>
      </div>
    </div>
  </div>
  <style>
    /* Force le widget à prendre toute la largeur */
    #dashboard-widgets .postbox-container {
      width: 100% !important;
    }
    #kd-theme-presentation {
      width: 100% !important;
      max-width: 100% !important;
    }
    #kd-theme-presentation .inside {
      margin: 0 !important;
      padding: 0 !important;
    }
    #normal-sortables {
      min-height: 0 !important;
    }
    .kd-dashboard-widget .button {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 4px;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    .kd-dashboard-widget .button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
  </style>
  <?php
}

