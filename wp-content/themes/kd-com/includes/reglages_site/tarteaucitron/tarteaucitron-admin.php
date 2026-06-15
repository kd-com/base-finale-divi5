<?php
/**
 * Administration Tarteaucitron - Gestion des cookies RGPD
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Page d'administration pour Tarteaucitron (Gestion des cookies RGPD)
 */
function kd_tarteaucitron_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Traiter la sauvegarde
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['kd_tarteaucitron_nonce'] ) ) {
    if ( ! wp_verify_nonce( $_POST['kd_tarteaucitron_nonce'], 'kd_tarteaucitron_save' ) ) {
      echo '<div class="notice notice-error"><p>Nonce invalide.</p></div>';
    } else {
      // Sauvegarder les options
      $options = array(
        'enabled' => isset( $_POST['tac_enabled'] ) ? 1 : 0,
        'hashtag' => sanitize_text_field( $_POST['tac_hashtag'] ?? '#tarteaucitron' ),
        'cookie_name' => sanitize_text_field( $_POST['tac_cookie_name'] ?? 'tarteaucitron' ),
        'orientation' => sanitize_text_field( $_POST['tac_orientation'] ?? 'middle' ),
        'group_services' => isset( $_POST['tac_group_services'] ) ? 1 : 0,
        'show_alert_small' => isset( $_POST['tac_show_alert_small'] ) ? 1 : 0,
        'cookies_list' => isset( $_POST['tac_cookies_list'] ) ? 1 : 0,
        'close_popup' => isset( $_POST['tac_close_popup'] ) ? 1 : 0,
        'show_icon' => isset( $_POST['tac_show_icon'] ) ? 1 : 0,
        'icon_position' => sanitize_text_field( $_POST['tac_icon_position'] ?? 'BottomRight' ),
        'adblocker' => isset( $_POST['tac_adblocker'] ) ? 1 : 0,
        'deny_all_cta' => isset( $_POST['tac_deny_all_cta'] ) ? 1 : 0,
        'accept_all_cta' => isset( $_POST['tac_accept_all_cta'] ) ? 1 : 0,
        'high_privacy' => isset( $_POST['tac_high_privacy'] ) ? 1 : 0,
        'handle_browser_dnt_request' => isset( $_POST['tac_handle_browser_dnt_request'] ) ? 1 : 0,
        'remove_credit' => isset( $_POST['tac_remove_credit'] ) ? 1 : 0,
        'more_info_link' => isset( $_POST['tac_more_info_link'] ) ? 1 : 0,
        'read_more_link' => esc_url_raw( $_POST['tac_read_more_link'] ?? '' ),
        'mandatory' => isset( $_POST['tac_mandatory'] ) ? 1 : 0,
        
        // Services activés
        'services' => array(),
      );
      
      // Services disponibles
      $all_services = kd_get_tarteaucitron_services();
      foreach ( $all_services as $service_id => $service_info ) {
        if ( isset( $_POST['tac_service_' . $service_id] ) ) {
          $options['services'][$service_id] = array(
            'enabled' => 1,
            'key' => sanitize_text_field( $_POST['tac_service_' . $service_id . '_key'] ?? '' ),
          );
        }
      }
      
      update_option( 'kd_tarteaucitron_settings', $options );
      echo '<div class="notice notice-success"><p>✅ Paramètres Tarteaucitron enregistrés avec succès.</p></div>';
    }
  }

  // Récupérer les options
  $options = get_option( 'kd_tarteaucitron_settings', array() );
  $defaults = array(
    'enabled' => 0,
    'hashtag' => '#tarteaucitron',
    'cookie_name' => 'tarteaucitron',
    'orientation' => 'middle',
    'group_services' => false,
    'show_alert_small' => false,
    'cookies_list' => false,
    'close_popup' => false,
    'show_icon' => true,
    'icon_position' => 'BottomRight',
    'adblocker' => false,
    'deny_all_cta' => true,
    'accept_all_cta' => true,
    'high_privacy' => true,
    'handle_browser_dnt_request' => false,
    'remove_credit' => false,
    'more_info_link' => true,
    'read_more_link' => '',
    'mandatory' => false,
    'services' => array(),
  );
  $options = wp_parse_args( $options, $defaults );

  ?>
  <div class="wrap">
    <h1>🍪 Gestion des Cookies - Tarteaucitron</h1>
    
    <div class="notice notice-info" style="margin: 20px 0;">
      <p><strong>Tarteaucitron.js</strong> est un gestionnaire de consentement aux cookies conforme au RGPD.</p>
      <p>📚 Documentation officielle : <a href="https://tarteaucitron.io/fr/" target="_blank">tarteaucitron.io</a> | 
         💻 GitHub : <a href="https://github.com/AmauriC/tarteaucitron.js" target="_blank">AmauriC/tarteaucitron.js</a></p>
    </div>

    <form method="post" action="">
      <?php wp_nonce_field( 'kd_tarteaucitron_save', 'kd_tarteaucitron_nonce' ); ?>
      
      <!-- Activation générale -->
      <h2>⚡ Activation</h2>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="tac_enabled">Activer Tarteaucitron</label>
          </th>
          <td>
            <label>
              <input type="checkbox" id="tac_enabled" name="tac_enabled" value="1" <?php checked( $options['enabled'], 1 ); ?> />
              <strong>Activer le gestionnaire de cookies sur le site</strong>
            </label>
            <p class="description">Le script Tarteaucitron sera automatiquement chargé dans le &lt;head&gt; de votre site.</p>
          </td>
        </tr>
      </table>

      <!-- Configuration générale -->
      <h2>⚙️ Configuration générale</h2>
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="tac_hashtag">Hashtag de personnalisation</label>
          </th>
          <td>
            <input type="text" id="tac_hashtag" name="tac_hashtag" value="<?php echo esc_attr( $options['hashtag'] ); ?>" class="regular-text" />
            <p class="description">Ancre pour ouvrir le panneau (défaut: #tarteaucitron)</p>
          </td>
        </tr>
        
        <tr>
          <th scope="row">
            <label for="tac_cookie_name">Nom du cookie</label>
          </th>
          <td>
            <input type="text" id="tac_cookie_name" name="tac_cookie_name" value="<?php echo esc_attr( $options['cookie_name'] ); ?>" class="regular-text" />
            <p class="description">Nom du cookie de consentement (défaut: tarteaucitron)</p>
          </td>
        </tr>
        
        <tr>
          <th scope="row">
            <label for="tac_orientation">Position de la bannière</label>
          </th>
          <td>
            <select id="tac_orientation" name="tac_orientation">
              <option value="middle" <?php selected( $options['orientation'], 'middle' ); ?>>Centre (Middle)</option>
              <option value="top" <?php selected( $options['orientation'], 'top' ); ?>>Haut (Top)</option>
              <option value="bottom" <?php selected( $options['orientation'], 'bottom' ); ?>>Bas (Bottom)</option>
            </select>
            <p class="description">Position de la bannière de consentement</p>
          </td>
        </tr>
        
        <tr>
          <th scope="row">
            <label for="tac_icon_position">Position de l'icône</label>
          </th>
          <td>
            <select id="tac_icon_position" name="tac_icon_position">
              <option value="BottomRight" <?php selected( $options['icon_position'], 'BottomRight' ); ?>>Bas à droite</option>
              <option value="BottomLeft" <?php selected( $options['icon_position'], 'BottomLeft' ); ?>>Bas à gauche</option>
              <option value="TopRight" <?php selected( $options['icon_position'], 'TopRight' ); ?>>Haut à droite</option>
              <option value="TopLeft" <?php selected( $options['icon_position'], 'TopLeft' ); ?>>Haut à gauche</option>
            </select>
            <p class="description">Position du bouton d'accès aux paramètres</p>
          </td>
        </tr>
        
        <tr>
          <th scope="row">
            <label for="tac_read_more_link">Lien "En savoir plus"</label>
          </th>
          <td>
            <input type="url" id="tac_read_more_link" name="tac_read_more_link" value="<?php echo esc_url( $options['read_more_link'] ); ?>" class="regular-text" placeholder="https://votre-site.fr/politique-de-confidentialite/" />
            <p class="description">URL vers votre page de politique de confidentialité</p>
          </td>
        </tr>
      </table>

      <!-- Options d'affichage -->
      <h2>🎨 Options d'affichage</h2>
      <table class="form-table">
        <tr>
          <th scope="row">Options de la bannière</th>
          <td>
            <fieldset>
              <label>
                <input type="checkbox" name="tac_group_services" value="1" <?php checked( $options['group_services'], 1 ); ?> />
                Grouper les services par catégorie
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_show_alert_small" value="1" <?php checked( $options['show_alert_small'], 1 ); ?> />
                Afficher la bannière en petit format
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_cookies_list" value="1" <?php checked( $options['cookies_list'], 1 ); ?> />
                Afficher la liste des cookies
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_close_popup" value="1" <?php checked( $options['close_popup'], 1 ); ?> />
                Permettre de fermer la popup
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_show_icon" value="1" <?php checked( $options['show_icon'], 1 ); ?> />
                Afficher l'icône d'accès aux paramètres
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_more_info_link" value="1" <?php checked( $options['more_info_link'], 1 ); ?> />
                Afficher le lien "En savoir plus"
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_remove_credit" value="1" <?php checked( $options['remove_credit'], 1 ); ?> />
                Retirer le crédit Tarteaucitron
              </label>
            </fieldset>
          </td>
        </tr>
      </table>

      <!-- Options de confidentialité -->
      <h2>🔒 Options de confidentialité RGPD</h2>
      <table class="form-table">
        <tr>
          <th scope="row">Paramètres RGPD</th>
          <td>
            <fieldset>
              <label>
                <input type="checkbox" name="tac_deny_all_cta" value="1" <?php checked( $options['deny_all_cta'], 1 ); ?> />
                <strong>Bouton "Tout refuser"</strong> - Afficher le bouton de refus global
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_accept_all_cta" value="1" <?php checked( $options['accept_all_cta'], 1 ); ?> />
                <strong>Bouton "Tout accepter"</strong> - Afficher le bouton d'acceptation globale
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_high_privacy" value="1" <?php checked( $options['high_privacy'], 1 ); ?> />
                <strong>Haute confidentialité</strong> - Désactiver par défaut tous les services (recommandé RGPD)
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_handle_browser_dnt_request" value="1" <?php checked( $options['handle_browser_dnt_request'], 1 ); ?> />
                <strong>Respecter Do Not Track</strong> - Respecter la préférence DNT du navigateur
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_adblocker" value="1" <?php checked( $options['adblocker'], 1 ); ?> />
                <strong>Détecter les bloqueurs de pub</strong> - Afficher un message si un adblocker est détecté
              </label><br/>
              
              <label>
                <input type="checkbox" name="tac_mandatory" value="1" <?php checked( $options['mandatory'], 1 ); ?> />
                <strong>Bannière obligatoire</strong> - L'utilisateur doit faire un choix (pas de navigation sans consentement)
              </label>
            </fieldset>
          </td>
        </tr>
      </table>

      <!-- Services disponibles -->
      <h2>🔌 Services et cookies à gérer</h2>
      <p class="description">Activez les services que vous utilisez sur votre site. Tarteaucitron gérera automatiquement leur chargement selon le consentement de l'utilisateur.</p>
      
      <?php
      $services = kd_get_tarteaucitron_services();
      $categories = array();
      foreach ( $services as $service_id => $service ) {
        $categories[$service['category']][] = array( 'id' => $service_id, 'info' => $service );
      }
      
      foreach ( $categories as $cat_name => $cat_services ) :
      ?>
        <h3><?php echo esc_html( $cat_name ); ?></h3>
        <table class="widefat" style="margin-bottom: 20px;">
          <thead>
            <tr>
              <th style="width: 60px;">Actif</th>
              <th style="width: 200px;">Service</th>
              <th>Description</th>
              <th style="width: 300px;">Configuration</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $cat_services as $service ) : 
              $service_id = $service['id'];
              $service_info = $service['info'];
              $is_enabled = isset( $options['services'][$service_id]['enabled'] );
              $service_key = $options['services'][$service_id]['key'] ?? '';
            ?>
              <tr>
                <td style="text-align: center;">
                  <input type="checkbox" name="tac_service_<?php echo esc_attr( $service_id ); ?>" value="1" <?php checked( $is_enabled, 1 ); ?> />
                </td>
                <td>
                  <strong><?php echo esc_html( $service_info['name'] ); ?></strong>
                </td>
                <td>
                  <?php echo esc_html( $service_info['description'] ); ?>
                </td>
                <td>
                  <?php if ( $service_info['needs_key'] ) : ?>
                    <input type="text" 
                           name="tac_service_<?php echo esc_attr( $service_id ); ?>_key" 
                           value="<?php echo esc_attr( $service_key ); ?>" 
                           placeholder="<?php echo esc_attr( $service_info['key_label'] ?? 'ID/Clé' ); ?>"
                           style="width: 100%;" />
                  <?php else : ?>
                    <em style="color: #666;">Aucune configuration requise</em>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>

      <p class="submit">
        <button type="submit" class="button button-primary button-large">💾 Enregistrer les paramètres</button>
      </p>
    </form>
    
    <!-- Intégration manuelle -->
    <div style="margin-top: 40px; padding: 20px; background: #f0f0f1; border-left: 4px solid #2271b1;">
      <h3>💡 Intégration dans le thème</h3>
      <p>Le script Tarteaucitron sera automatiquement chargé dans le <code>&lt;head&gt;</code> de votre site si vous l'avez activé ci-dessus.</p>
      <p><strong>Pour les services activés, utilisez les attributs HTML recommandés par Tarteaucitron :</strong></p>
      
      <h4>Exemple : YouTube</h4>
      <pre style="background: white; padding: 10px; overflow-x: auto; border: 1px solid #ddd;">&lt;div class="youtube_player" 
     videoID="dQw4w9WgXcQ" 
     width="560" 
     height="315"&gt;
&lt;/div&gt;</pre>

      <h4>Exemple : Google Analytics (automatique)</h4>
      <pre style="background: white; padding: 10px; overflow-x: auto; border: 1px solid #ddd;">// Le script sera automatiquement initialisé
// avec votre ID GA configuré ci-dessus</pre>

      <h4>Exemple : Google Maps</h4>
      <pre style="background: white; padding: 10px; overflow-x: auto; border: 1px solid #ddd;">&lt;div class="googlemaps-canvas" 
     data-address="1 rue de la Paix, Paris"&gt;
&lt;/div&gt;</pre>

      <p style="margin-top: 15px;">
        <a href="https://tarteaucitron.io/fr/install/" target="_blank" class="button">📖 Consulter la documentation complète</a>
      </p>
    </div>
  </div>
  <?php
}

/**
 * Liste des services Tarteaucitron disponibles
 */
function kd_get_tarteaucitron_services() {
  return array(
    // Analytics
    'googleanalytics' => array(
      'name' => 'Google Analytics (GA4)',
      'description' => 'Analyse d\'audience avec Google Analytics 4',
      'category' => '📊 Analytics',
      'needs_key' => true,
      'key_label' => 'ID de mesure (G-XXXXXXXXXX)',
    ),
    'googletagmanager' => array(
      'name' => 'Google Tag Manager',
      'description' => 'Gestionnaire de balises Google',
      'category' => '📊 Analytics',
      'needs_key' => true,
      'key_label' => 'ID GTM (GTM-XXXXXXX)',
    ),
    'matomo' => array(
      'name' => 'Matomo (Piwik)',
      'description' => 'Solution analytics open source respectueuse de la vie privée',
      'category' => '📊 Analytics',
      'needs_key' => true,
      'key_label' => 'URL de votre instance Matomo',
    ),
    
    // Publicité
    'googleads' => array(
      'name' => 'Google Ads',
      'description' => 'Publicités et conversions Google Ads',
      'category' => '💰 Publicité',
      'needs_key' => true,
      'key_label' => 'ID de conversion (AW-XXXXXXXXX)',
    ),
    'facebookpixel' => array(
      'name' => 'Facebook Pixel',
      'description' => 'Pixel de suivi Facebook pour le retargeting',
      'category' => '💰 Publicité',
      'needs_key' => true,
      'key_label' => 'ID Pixel Facebook',
    ),
    
    // Vidéo
    'youtube' => array(
      'name' => 'YouTube',
      'description' => 'Vidéos YouTube intégrées',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'vimeo' => array(
      'name' => 'Vimeo',
      'description' => 'Vidéos Vimeo intégrées',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'dailymotion' => array(
      'name' => 'Dailymotion',
      'description' => 'Vidéos Dailymotion intégrées',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    
    // Réseaux sociaux
    'twitter' => array(
      'name' => 'Twitter / X',
      'description' => 'Boutons de partage et widgets Twitter',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    'facebook' => array(
      'name' => 'Facebook',
      'description' => 'Boutons de partage et widgets Facebook',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    'linkedin' => array(
      'name' => 'LinkedIn',
      'description' => 'Boutons de partage LinkedIn',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    'instagram' => array(
      'name' => 'Instagram',
      'description' => 'Widgets Instagram intégrés',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    
    // Audio & Podcasts
    'soundcloud' => array(
      'name' => 'SoundCloud',
      'description' => 'Lecteur SoundCloud intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),
    'spotify' => array(
      'name' => 'Spotify',
      'description' => 'Lecteur Spotify intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),
    'deezer' => array(
      'name' => 'Deezer',
      'description' => 'Lecteur Deezer intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),
    'mixcloud' => array(
      'name' => 'Mixcloud',
      'description' => 'Lecteur Mixcloud intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),
    'ausha' => array(
      'name' => 'Ausha',
      'description' => 'Lecteur Ausha intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),
    'bandcamp' => array(
      'name' => 'Bandcamp',
      'description' => 'Lecteur Bandcamp intégré',
      'category' => '🎵 Audio & Podcast',
      'needs_key' => false,
    ),

    // Vidéos (compléments)
    'twitch' => array(
      'name' => 'Twitch',
      'description' => 'Lecteur Twitch intégré',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'tiktokvideo' => array(
      'name' => 'TikTok (vidéo)',
      'description' => 'Lecteur TikTok intégré',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'slideshare' => array(
      'name' => 'SlideShare',
      'description' => 'SlideShare intégré',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'calameo' => array(
      'name' => 'Calameo',
      'description' => 'Lecteur Calameo intégré',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'genially' => array(
      'name' => 'Genially',
      'description' => 'Intégration Genially',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),
    'playplay' => array(
      'name' => 'PlayPlay',
      'description' => 'Lecteur PlayPlay intégré',
      'category' => '🎥 Vidéo & Média',
      'needs_key' => false,
    ),

    // Réseaux sociaux (embeds)
    'facebookpost' => array(
      'name' => 'Facebook (post)',
      'description' => 'Post Facebook intégré',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    'twitterembed' => array(
      'name' => 'Twitter / X (card)',
      'description' => 'Carte Twitter/X intégrée',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),
    'twittertimeline' => array(
      'name' => 'Twitter / X (timeline)',
      'description' => 'Timeline Twitter/X intégrée',
      'category' => '🌐 Réseaux sociaux',
      'needs_key' => false,
    ),

    // Cartes
    'googlemaps' => array(
      'name' => 'Google Maps',
      'description' => 'Cartes Google Maps intégrées',
      'category' => '🗺️ Cartes',
      'needs_key' => false,
    ),
    'openstreetmap' => array(
      'name' => 'OpenStreetMap',
      'description' => 'Cartes OpenStreetMap (alternative libre)',
      'category' => '🗺️ Cartes',
      'needs_key' => false,
    ),
    
    // Fonctionnalités
    'googlefonts' => array(
      'name' => 'Google Fonts',
      'description' => 'Polices de caractères Google Fonts',
      'category' => '🎨 Fonctionnalités',
      'needs_key' => false,
    ),
    
    // Support & Chat
    'recaptcha' => array(
      'name' => 'reCAPTCHA',
      'description' => 'Protection anti-spam Google reCAPTCHA',
      'category' => '🔒 Support & Sécurité',
      'needs_key' => true,
      'key_label' => 'Clé du site reCAPTCHA',
    ),
    'intercom' => array(
      'name' => 'Intercom',
      'description' => 'Chat en direct Intercom',
      'category' => '🔒 Support & Sécurité',
      'needs_key' => true,
      'key_label' => 'ID App Intercom',
    ),
    'crisp' => array(
      'name' => 'Crisp',
      'description' => 'Chat en direct Crisp',
      'category' => '🔒 Support & Sécurité',
      'needs_key' => true,
      'key_label' => 'ID Website Crisp',
    ),
    
    // E-commerce
    'stripe' => array(
      'name' => 'Stripe',
      'description' => 'Paiement en ligne Stripe',
      'category' => '🛒 E-commerce',
      'needs_key' => false,
    ),
  );
}

/**
 * Charger le script Tarteaucitron dans le head
 */
function kd_tarteaucitron_load_script() {
  $options = get_option( 'kd_tarteaucitron_settings', array() );
  // Ne pas charger le script pour tous les utilisateurs connectés au back-office sauf les abonnés
  if ( is_user_logged_in() && is_admin() && !current_user_can('read') ) {
    return;
  }
  if ( empty( $options['enabled'] ) ) {
    return;
  }
  ?>
  <!-- Tarteaucitron.js -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tarteaucitronjs@latest/css/tarteaucitron.min.css">
  <script src="https://cdn.jsdelivr.net/npm/tarteaucitronjs@latest/tarteaucitron.min.js"></script>
  <script type="text/javascript">
    tarteaucitron.init({
      "privacyUrl": "<?php echo esc_js( $options['read_more_link'] ?? '' ); ?>",
      "hashtag": "<?php echo esc_js( $options['hashtag'] ?? '#tarteaucitron' ); ?>",
      "cookieName": "<?php echo esc_js( $options['cookie_name'] ?? 'tarteaucitron' ); ?>",
      "orientation": "<?php echo esc_js( $options['orientation'] ?? 'middle' ); ?>",
      "groupServices": <?php echo $options['group_services'] ? 'true' : 'false'; ?>,
      "showAlertSmall": <?php echo $options['show_alert_small'] ? 'true' : 'false'; ?>,
      "cookieslist": <?php echo $options['cookies_list'] ? 'true' : 'false'; ?>,
      "closePopup": <?php echo $options['close_popup'] ? 'true' : 'false'; ?>,
      "showIcon": <?php echo $options['show_icon'] ? 'true' : 'false'; ?>,
      "iconPosition": "<?php echo esc_js( $options['icon_position'] ?? 'BottomRight' ); ?>",
      "adblocker": <?php echo $options['adblocker'] ? 'true' : 'false'; ?>,
      "DenyAllCta": <?php echo $options['deny_all_cta'] ? 'true' : 'false'; ?>,
      "AcceptAllCta": <?php echo $options['accept_all_cta'] ? 'true' : 'false'; ?>,
      "highPrivacy": <?php echo $options['high_privacy'] ? 'true' : 'false'; ?>,
      "handleBrowserDNTRequest": <?php echo $options['handle_browser_dnt_request'] ? 'true' : 'false'; ?>,
      "removeCredit": <?php echo $options['remove_credit'] ? 'true' : 'false'; ?>,
      "moreInfoLink": <?php echo $options['more_info_link'] ? 'true' : 'false'; ?>,
      "useExternalCss": false,
      "readmoreLink": "<?php echo esc_js( $options['read_more_link'] ?? '' ); ?>",
      "mandatory": <?php echo $options['mandatory'] ? 'true' : 'false'; ?>
    });

    <?php
    // Charger les services activés
    if ( ! empty( $options['services'] ) ) {
      foreach ( $options['services'] as $service_id => $service_config ) {
        if ( empty( $service_config['enabled'] ) ) {
          continue;
        }
        
        $key = $service_config['key'] ?? '';
        
        switch ( $service_id ) {
          case 'googleanalytics':
            if ( $key ) {
              echo "tarteaucitron.user.gtagUa = '" . esc_js( $key ) . "';\n";
              echo "tarteaucitron.user.gtagMore = function () { /* add here your optionnal gtag() */ };\n";
              echo "(tarteaucitron.job = tarteaucitron.job || []).push('gtag');\n";
            }
            break;
            
          case 'googletagmanager':
            if ( $key ) {
              echo "tarteaucitron.user.googletagmanagerId = '" . esc_js( $key ) . "';\n";
              echo "(tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager');\n";
            }
            break;
            
          case 'facebookpixel':
            if ( $key ) {
              echo "tarteaucitron.user.facebookpixelId = '" . esc_js( $key ) . "';\n";
              echo "(tarteaucitron.job = tarteaucitron.job || []).push('facebookpixel');\n";
            }
            break;
            
          case 'recaptcha':
            if ( $key ) {
              // Configuration pour reCAPTCHA v2
              echo "tarteaucitron.user.recaptchaSitekey = '" . esc_js( $key ) . "';\n";
              echo "(tarteaucitron.job = tarteaucitron.job || []).push('recaptcha');\n";
              
              // Debug
              echo "console.log('reCAPTCHA configuré avec la clé:', tarteaucitron.user.recaptchaSitekey);\n";
            }
            break;

          case 'googlemaps':
            // Pour les iframes d'embed, on utilise le service 'googlemapsembed'
            echo "(tarteaucitron.job = tarteaucitron.job || []).push('googlemapsembed');\n";
            break;
          case 'googlefonts':
            // Google Fonts - pas de configuration particulière nécessaire
            echo "(tarteaucitron.job = tarteaucitron.job || []).push('googlefonts');\n";
            break;
          case 'tiktokvideo':
            // Mapper vers le job officiel 'tiktok'
            echo "(tarteaucitron.job = tarteaucitron.job || []).push('tiktok');\n";
            break;
          case 'twitterembed':
          case 'twittertimeline':
            // Les cartes et timelines utilisent le même widget Twitter
            echo "(tarteaucitron.job = tarteaucitron.job || []).push('twitter');\n";
            break;
            
          default:
            // Services sans clé
            echo "(tarteaucitron.job = tarteaucitron.job || []).push('" . esc_js( $service_id ) . "');\n";
            break;
        }
      }
    }
    // Sécurité : si des wrappers TAC sont présents dans le DOM, pousser les jobs nécessaires automatiquement
    ?>
    document.addEventListener('DOMContentLoaded', function() {
      if (document.querySelector('.youtube_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('youtube');
      }
      if (document.querySelector('.googlemapsembed')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('googlemapsembed');
      }
      if (document.querySelector('.tac_iframe')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('iframe');
      }
      if (document.querySelector('.vimeo_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('vimeo');
      }
      if (document.querySelector('.dailymotion_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('dailymotion');
      }
      if (document.querySelector('.openstreetmap')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('openstreetmap');
      }
      if (document.querySelector('.soundcloud_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('soundcloud');
      }
      if (document.querySelector('.spotify_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('spotify');
      }
      if (document.querySelector('.slideshare-canvas')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('slideshare');
      }
      if (document.querySelector('.calameo-canvas')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('calameo');
      }
      if (document.querySelector('.tac_genially')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('genially');
      }
      if (document.querySelector('.tac_playplay')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('playplay');
      }
      if (document.querySelector('.twitch_player')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('twitch');
      }
      if (document.querySelector('.tac_facebookpost')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('facebookpost');
      }
      // Détections pour widgets scriptés
      if (document.querySelector('blockquote.twitter-tweet, a.twitter-timeline')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('twitter');
      }
      if (document.querySelector('blockquote.instagram-media, iframe[src*="instagram.com/embed"]')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('instagram');
      }
      if (document.querySelector('blockquote.tiktok-embed, iframe[src*="tiktok.com"]')) {
        (tarteaucitron.job = tarteaucitron.job || []).push('tiktok');
      }
    });

        <?php
    // Fallback personnalisé pour reCAPTCHA
    if ( ! empty( $options['services']['recaptcha']['enabled'] ) ) {
    ?>
    (function() {
      var tentatives = 0;
      var maxTentatives = 50;
      
      var initFallback = function() {
        tentatives++;
        
        if (typeof tarteaucitron !== 'undefined' && tarteaucitron.services && tarteaucitron.services.recaptcha) {
          var originalFallback = tarteaucitron.services.recaptcha.fallback;
          
          tarteaucitron.services.recaptcha.fallback = function () {
            // Vérifier si le consentement est déjà donné
            if (tarteaucitron.state && tarteaucitron.state.recaptcha === true) {
              if (tarteaucitron.services.recaptcha && tarteaucitron.services.recaptcha.js) {
                tarteaucitron.services.recaptcha.js();
                
                setTimeout(function() {
                  if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function') {
                    var elements = document.querySelectorAll('.tarteaucitronrecaptcha');
                    elements.forEach(function(el) {
                      if (!el.querySelector('.g-recaptcha') && !el.hasAttribute('data-rendered')) {
                        var sitekey = el.getAttribute('data-sitekey');
                        var theme = el.getAttribute('data-theme') || 'light';
                        var size = el.getAttribute('data-size') || 'normal';
                        
                        try {
                          grecaptcha.render(el, {
                            'sitekey': sitekey,
                            'theme': theme,
                            'size': size
                          });
                          el.setAttribute('data-rendered', 'true');
                        } catch(e) {}
                      }
                    });
                  }
                }, 1000);
              }
              return;
            }
            
            // Vérifier aussi dans le cookie
            var cookieValue = document.cookie.match('(^|;)\\s*tarteaucitron\\s*=\\s*([^;]+)');
            if (cookieValue && cookieValue[2].indexOf('recaptcha=true') !== -1) {
              if (tarteaucitron.services.recaptcha && tarteaucitron.services.recaptcha.js) {
                tarteaucitron.services.recaptcha.js();
                
                setTimeout(function() {
                  if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.render === 'function') {
                    var elements = document.querySelectorAll('.tarteaucitronrecaptcha');
                    elements.forEach(function(el) {
                      if (!el.querySelector('.g-recaptcha') && !el.hasAttribute('data-rendered')) {
                        var sitekey = el.getAttribute('data-sitekey');
                        var theme = el.getAttribute('data-theme') || 'light';
                        var size = el.getAttribute('data-size') || 'normal';
                        
                        try {
                          grecaptcha.render(el, {
                            'sitekey': sitekey,
                            'theme': theme,
                            'size': size
                          });
                          el.setAttribute('data-rendered', 'true');
                        } catch(e) {}
                      }
                    });
                  }
                }, 1000);
              }
              return;
            }
            
            // Afficher le message de fallback
            var captchaElements = document.querySelectorAll('.tarteaucitronrecaptcha');
            captchaElements.forEach(function(element) {
              if (element.querySelector('.tac-recaptcha-fallback')) {
                return;
              }
              
              var fallbackMsg = document.createElement('div');
              fallbackMsg.className = 'tac-recaptcha-fallback';
              fallbackMsg.innerHTML = '<p>🍪 Protection anti-spam (reCAPTCHA)</p><p style="margin: 0 0 15px 0; font-size: 14px; opacity: 0.95; color: #ffffff;">Pour soumettre ce formulaire, veuillez accepter les cookies Google reCAPTCHA.</p><button onclick="tarteaucitron.userInterface.openPanel();">Gérer mes cookies</button>';
              element.appendChild(fallbackMsg);
            });
            
            if (originalFallback) {
              originalFallback();
            }
          };
          
          // Surveiller l'acceptation des cookies
          var checkInterval = setInterval(function() {
            var isAccepted = false;
            
            if (tarteaucitron.state && tarteaucitron.state.recaptcha === true) {
              isAccepted = true;
            }
            
            var cookieValue = document.cookie.match('(^|;)\\s*tarteaucitron\\s*=\\s*([^;]+)');
            if (cookieValue && cookieValue[2].indexOf('recaptcha=true') !== -1) {
              isAccepted = true;
            }
            
            if (isAccepted) {
              clearInterval(checkInterval);
              var fallbacks = document.querySelectorAll('.tac-recaptcha-fallback');
              if (fallbacks.length > 0) {
                fallbacks.forEach(function(fb) { fb.remove(); });
                setTimeout(function() { location.reload(); }, 500);
              }
            }
          }, 500);
          
          setTimeout(function() { clearInterval(checkInterval); }, 30000);
          setTimeout(function() {
            if (tarteaucitron.services.recaptcha.fallback) {
              tarteaucitron.services.recaptcha.fallback();
            }
          }, 500);
          
        } else if (tentatives < maxTentatives) {
          setTimeout(initFallback, 100);
        }
      };
      
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFallback);
      } else {
        initFallback();
      }
    })();
    <?php
    }
    ?>
  </script>
  <?php
}
add_action( 'wp_head', 'kd_tarteaucitron_load_script', 1 );