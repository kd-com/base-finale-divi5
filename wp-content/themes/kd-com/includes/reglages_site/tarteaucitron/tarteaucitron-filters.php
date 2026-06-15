<?php
/**
 * Transformations automatiques pour rendre les embeds (YouTube, Google Maps)
 * compatibles avec Tarteaucitron (RGPD).
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Détecte et transforme le HTML d'oEmbed (YouTube, Google Maps) en wrappers TAC.
 */
function kd_tac_transform_oembed_html( $html, $url, $attr, $post_ID ) {
  if ( empty( $html ) ) {
    return $html;
  }

  // Éviter double transformation
  if (
    strpos( $html, 'youtube_player' ) !== false ||
    strpos( $html, 'googlemapsembed' ) !== false ||
    strpos( $html, 'vimeo_player' ) !== false ||
    strpos( $html, 'dailymotion_player' ) !== false ||
    strpos( $html, 'openstreetmap' ) !== false ||
    strpos( $html, 'soundcloud_player' ) !== false ||
    strpos( $html, 'spotify_player' ) !== false ||
    strpos( $html, 'slideshare-canvas' ) !== false ||
    strpos( $html, 'calameo-canvas' ) !== false ||
    strpos( $html, 'tac_genially' ) !== false ||
    strpos( $html, 'tac_playplay' ) !== false ||
    strpos( $html, 'twitch_player' ) !== false ||
    strpos( $html, 'tac_facebookpost' ) !== false ||
    strpos( $html, 'tac_iframe' ) !== false
  ) {
    return $html;
  }

  // YouTube
  if ( kd_tac_is_youtube_url( $url ) || preg_match( '#<iframe[^>]+(youtube\.com|youtu\.be)[^>]*>#i', $html ) ) {
    return kd_tac_build_youtube_wrapper_from_html( $html, $url );
  }

  // Google Maps
  if ( kd_tac_is_googlemaps_url( $url ) || preg_match( '#<iframe[^>]+(google\.com/maps|maps\.google\.)[^>]*>#i', $html ) ) {
    return kd_tac_build_googlemaps_wrapper_from_html( $html, $url );
  }

  // Vimeo
  if ( kd_tac_is_vimeo_url( $url ) || preg_match( '#<iframe[^>]+(vimeo\.com)[^>]*>#i', $html ) ) {
    return kd_tac_build_vimeo_wrapper_from_html( $html, $url );
  }

  // Dailymotion
  if ( kd_tac_is_dailymotion_url( $url ) || preg_match( '#<iframe[^>]+(dailymotion\.com|dai\.ly)[^>]*>#i', $html ) ) {
    return kd_tac_build_dailymotion_wrapper_from_html( $html, $url );
  }

  // OpenStreetMap
  if ( kd_tac_is_openstreetmap_url( $url ) || preg_match( '#<iframe[^>]+(openstreetmap\.org|osm\.org)[^>]*>#i', $html ) ) {
    return kd_tac_build_openstreetmap_wrapper_from_html( $html, $url );
  }

  // SoundCloud
  if ( kd_tac_is_soundcloud_url( $url ) || preg_match( '#<iframe[^>]+(w\.soundcloud\.com|soundcloud\.com)[^>]*>#i', $html ) ) {
    return kd_tac_build_soundcloud_wrapper_from_html( $html, $url );
  }

  // Spotify
  if ( kd_tac_is_spotify_url( $url ) || preg_match( '#<iframe[^>]+(open\.spotify\.com)[^>]*>#i', $html ) ) {
    return kd_tac_build_spotify_wrapper_from_html( $html, $url );
  }

  // SlideShare
  if ( preg_match( '#(slideshare\.net|slideshare\.com)#i', $url ) || preg_match( '#<iframe[^>]+slideshare\.net[^>]*>#i', $html ) ) {
    return kd_tac_build_slideshare_wrapper_from_html( $html, $url );
  }

  // Calameo
  if ( preg_match( '#calameo\.com|v\.calameo\.com#i', $url ) || preg_match( '#<iframe[^>]+(v\.)?calameo\.com[^>]*>#i', $html ) ) {
    return kd_tac_build_calameo_wrapper_from_html( $html, $url );
  }

  // Genially
  if ( preg_match( '#genial\.ly|genial\.ly#i', $url ) || preg_match( '#<iframe[^>]+genial\.ly[^>]*>#i', $html ) ) {
    return kd_tac_build_genially_wrapper_from_html( $html, $url );
  }

  // PlayPlay
  if ( preg_match( '#playplay\.com#i', $url ) || preg_match( '#<iframe[^>]+playplay\.com[^>]*>#i', $html ) ) {
    return kd_tac_build_playplay_wrapper_from_html( $html, $url );
  }

  // Twitch (vidéo)
  if ( preg_match( '#twitch\.tv|player\.twitch\.tv#i', $url ) || preg_match( '#<iframe[^>]+twitch\.tv[^>]*>#i', $html ) ) {
    return kd_tac_build_twitch_wrapper_from_html( $html, $url );
  }

  // Facebook post
  if ( preg_match( '#facebook\.com/.*/posts/|facebook\.com/plugins/post\.php#i', $url ) || preg_match( '#<iframe[^>]+facebook\.com/plugins/post\.php[^>]*>#i', $html ) ) {
    return kd_tac_build_facebookpost_wrapper_from_html( $html, $url );
  }

  // Twitter (tweets/cards or timelines): retirer le script widgets.js, laisser le markup oEmbed
  // Nettoyer les scripts d'intégration (Twitter/Instagram/TikTok) pour éviter le chargement avant consentement
  $content = kd_tac_strip_known_embed_scripts( $content );
  if (
    preg_match( '#twitter\.com#i', $url ) ||
    preg_match( '#twitter-tweet|twitter-timeline|platform\.twitter\.com#i', $html )
  ) {
    return kd_tac_strip_known_embed_scripts( $html );
  }

  // Instagram embeds: retirer le script embed.js, laisser le blockquote
  if (
    preg_match( '#instagram\.com#i', $url ) ||
    preg_match( '#instagram-media|instagram\.com\\/embed\.js#i', $html )
  ) {
    return kd_tac_strip_known_embed_scripts( $html );
  }

  // TikTok embeds: retirer le script embed.js, laisser le blockquote
  if (
    preg_match( '#tiktok\.com#i', $url ) ||
    preg_match( '#tiktok-embed|tiktok\.com\\/embed\.js#i', $html )
  ) {
    return kd_tac_strip_known_embed_scripts( $html );
  }

  return $html;
}
add_filter( 'embed_oembed_html', 'kd_tac_transform_oembed_html', 10, 4 );

/**
 * Transforme les iframes inline dans le contenu (copiés/collés) pour TAC.
 */
function kd_tac_transform_iframes_in_content( $content ) {
  if ( empty( $content ) || is_admin() ) {
    return $content;
  }

  // Déjà transformé ?
  if (
    strpos( $content, 'youtube_player' ) !== false ||
    strpos( $content, 'googlemapsembed' ) !== false ||
    strpos( $content, 'vimeo_player' ) !== false ||
    strpos( $content, 'dailymotion_player' ) !== false ||
    strpos( $content, 'openstreetmap' ) !== false ||
    strpos( $content, 'soundcloud_player' ) !== false ||
    strpos( $content, 'spotify_player' ) !== false ||
    strpos( $content, 'slideshare-canvas' ) !== false ||
    strpos( $content, 'calameo-canvas' ) !== false ||
    strpos( $content, 'tac_genially' ) !== false ||
    strpos( $content, 'tac_playplay' ) !== false ||
    strpos( $content, 'twitch_player' ) !== false ||
    strpos( $content, 'tac_facebookpost' ) !== false
  ) {
    return $content;
  }

  // YouTube iframes -> <div class="youtube_player" ...></div>
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']+)(youtube\.com|youtu\.be)[^\"\\\']*[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_youtube_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Google Maps iframes -> <div class="googlemapsembed" data-url="..." style="width:...;height:...;"></div>
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*google\.com\/maps[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_googlemaps_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Vimeo iframes -> <div class="vimeo_player" videoID="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*(player\.)?vimeo\.com[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_vimeo_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Dailymotion iframes -> <div class="dailymotion_player" videoID="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*(dailymotion\.com|dai\.ly)[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_dailymotion_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // OpenStreetMap iframes -> <div class="openstreetmap" data-url="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*(openstreetmap\.org|osm\.org)[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_openstreetmap_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // SoundCloud iframes -> <div class="soundcloud_player" data-playable-url="..." data-height="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*w\.soundcloud\.com\/player[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_soundcloud_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Spotify iframes -> <div class="spotify_player" spotifyID="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*open\.spotify\.com\/embed[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_spotify_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // SlideShare iframes -> <div class="slideshare-canvas" data-id="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*slideshare\.net[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_slideshare_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Calameo iframes -> <div class="calameo-canvas" data-id="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*(?:v\.)?calameo\.com[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_calameo_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Genially iframes -> <div class="tac_genially" geniallyid="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*genial\.ly[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_genially_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // PlayPlay iframes -> <div class="tac_playplay" data-id="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*playplay\.com[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_playplay_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Twitch iframes -> <div class="twitch_player" videoID="..." parent="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*(?:player\.)?twitch\.tv[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_twitch_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  // Facebook post iframes -> <div class="tac_facebookpost" data-url="...">
  $content = preg_replace_callback( '#<iframe[^>]+src=[\"\\\']([^\"\\\']*facebook\.com\/plugins\/post\.php[^\"\\\']*)[\"\"][^>]*>(?:</iframe>)?#i', function( $m ) {
    $iframe = $m[0];
    return kd_tac_build_facebookpost_wrapper_from_html( $iframe, $m[1] );
  }, $content );

  return $content;
}
add_filter( 'the_content', 'kd_tac_transform_iframes_in_content', 12 );

/**
 * Transformer le reCAPTCHA de Forminator pour Tarteaucitron
 */
function kd_tac_transform_forminator_recaptcha( $content ) {
  if ( empty( $content ) || is_admin() ) {
    return $content;
  }

  // Déjà transformé ?
  if ( strpos( $content, 'tarteaucitronrecaptcha' ) !== false ) {
    return $content;
  }

  // Détecter les divs reCAPTCHA de Forminator
  // Pattern: <div class="forminator-g-recaptcha" data-sitekey="..." data-theme="..." data-size="...">
  $content = preg_replace_callback(
    '#<div[^>]+class=["\']([^"\']*forminator-g-recaptcha[^"\']*)["\']\s+data-theme=["\'](light|dark)["\']\s+data-sitekey=["\']([^"\']+)["\']\s+data-size=["\'](normal|compact|invisible)["\'][^>]*>.*?</div>#is',
    function( $matches ) {
      $full_match = $matches[0];
      $theme = $matches[2]; // light ou dark
      $sitekey = $matches[3];
      $size = $matches[4]; // normal, compact ou invisible

      // Créer le wrapper Tarteaucitron
      // Tarteaucitron attend un div avec la classe 'tarteaucitronrecaptcha'
      return '<div class="tarteaucitronrecaptcha" data-sitekey="' . esc_attr( $sitekey ) . '" data-theme="' . esc_attr( $theme ) . '" data-size="' . esc_attr( $size ) . '"></div>';
    },
    $content
  );

  return $content;
}
add_filter( 'the_content', 'kd_tac_transform_forminator_recaptcha', 11 );

/**
 * Bloquer le script reCAPTCHA de Forminator pour laisser Tarteaucitron le gérer
 */
function kd_tac_block_forminator_recaptcha_script() {
  $options = get_option( 'kd_tarteaucitron_settings', array() );
  
  // Ne bloquer que si Tarteaucitron est activé ET reCAPTCHA est configuré
  if ( empty( $options['enabled'] ) || empty( $options['services']['recaptcha']['enabled'] ) ) {
    return;
  }

  // Bloquer le script reCAPTCHA chargé par Forminator
  wp_dequeue_script( 'forminator-google-recaptcha' );
  wp_deregister_script( 'forminator-google-recaptcha' );
}
add_action( 'wp_enqueue_scripts', 'kd_tac_block_forminator_recaptcha_script', 99 );

/**
 * Script JavaScript pour transformer les reCAPTCHA Forminator en temps réel
 */
function kd_tac_forminator_recaptcha_js() {
  $options = get_option( 'kd_tarteaucitron_settings', array() );
  
  if ( empty( $options['enabled'] ) || empty( $options['services']['recaptcha']['enabled'] ) ) {
    return;
  }
  ?>
  <script type="text/javascript">
  (function() {
    // Observer pour détecter l'ajout de reCAPTCHA par Forminator
    function transformForminatorRecaptcha() {
      const recaptchaElements = document.querySelectorAll('.forminator-g-recaptcha:not(.tac-processed)');
      
      recaptchaElements.forEach(function(element) {
        // Marquer comme traité
        element.classList.add('tac-processed');
        
        // Récupérer les attributs
        const sitekey = element.getAttribute('data-sitekey');
        const theme = element.getAttribute('data-theme') || 'light';
        const size = element.getAttribute('data-size') || 'normal';
        
        // Vider le contenu
        element.innerHTML = '';
        
        // Créer le wrapper Tarteaucitron (simple, le fallback sera géré par le service personnalisé)
        const tacWrapper = document.createElement('div');
        tacWrapper.className = 'tarteaucitronrecaptcha';
        tacWrapper.setAttribute('data-sitekey', sitekey);
        tacWrapper.setAttribute('data-theme', theme);
        tacWrapper.setAttribute('data-size', size);
        
        // Remplacer le contenu
        element.appendChild(tacWrapper);
        
        // Re-lancer Tarteaucitron pour ce captcha
        if (typeof tarteaucitron !== 'undefined' && tarteaucitron.job) {
          if (!tarteaucitron.job.includes('recaptcha')) {
            tarteaucitron.job.push('recaptcha');
          }
        }
      });
    }
    
    // Exécuter au chargement du DOM
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', transformForminatorRecaptcha);
    } else {
      transformForminatorRecaptcha();
    }
    
    // Observer les changements dans le DOM (pour les formulaires chargés dynamiquement)
    if (window.MutationObserver) {
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.addedNodes.length) {
            transformForminatorRecaptcha();
          }
        });
      });
      
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  })();
  </script>
  <?php
}
add_action( 'wp_footer', 'kd_tac_forminator_recaptcha_js', 5 );

// --------------------------
// Helpers
// --------------------------

function kd_tac_is_youtube_url( $url ) {
  return is_string( $url ) && preg_match( '#(youtube\.com|youtu\.be)#i', $url );
}

function kd_tac_is_googlemaps_url( $url ) {
  return is_string( $url ) && preg_match( '#google\.(com|fr)/maps#i', $url );
}

function kd_tac_is_vimeo_url( $url ) {
  return is_string( $url ) && preg_match( '#vimeo\.com#i', $url );
}

function kd_tac_is_dailymotion_url( $url ) {
  return is_string( $url ) && preg_match( '#(dailymotion\.com|dai\.ly)#i', $url );
}

function kd_tac_is_openstreetmap_url( $url ) {
  return is_string( $url ) && preg_match( '#(openstreetmap\.org|osm\.org)#i', $url );
}

function kd_tac_is_soundcloud_url( $url ) {
  return is_string( $url ) && preg_match( '#(soundcloud\.com|w\.soundcloud\.com)#i', $url );
}

function kd_tac_is_spotify_url( $url ) {
  return is_string( $url ) && preg_match( '#open\.spotify\.com#i', $url );
}

function kd_tac_extract_width_height_from_iframe_html( $html ) {
  $width = '';
  $height = '';

  if ( preg_match( '#\swidth=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $mw ) ) {
    $width = trim( $mw[1] );
  }
  if ( preg_match( '#\sheight=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $mh ) ) {
    $height = trim( $mh[1] );
  }
  // Valeurs par défaut raisonnables
  if ( $width === '' ) { $width = '100%'; }
  if ( $height === '' ) { $height = '315'; }
  return array( $width, $height );
}

function kd_tac_extract_youtube_id_from_url( $url ) {
  if ( empty( $url ) ) return '';
  $id = '';
  // patterns possibles
  // https://www.youtube.com/watch?v=VIDEOID
  if ( preg_match( '#[?&]v=([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
    $id = $m[1];
  }
  // https://youtu.be/VIDEOID
  if ( ! $id && preg_match( '#youtu\.be/([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
    $id = $m[1];
  }
  // https://www.youtube.com/embed/VIDEOID
  if ( ! $id && preg_match( '#/embed/([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
    $id = $m[1];
  }
  return $id;
}

function kd_tac_build_youtube_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );

  // Trouver le src de l'iframe dans le HTML si possible
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) {
    $src = $url_hint;
  }

  // Extraire l'ID vidéo
  $video_id = kd_tac_extract_youtube_id_from_url( $src );
  if ( ! $video_id ) {
    // Si on ne parvient pas à extraire, fallback en iframe générique sous TAC
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }

  // Paramètres utiles depuis la query
  $params = array();
  $parsed = wp_parse_url( $src );
  if ( isset( $parsed['query'] ) ) {
    parse_str( $parsed['query'], $params );
  }

  $attrs = array(
    'class' => 'youtube_player',
    'videoID' => $video_id,
    'width' => $width,
    'height' => $height,
  );
  // Mapper quelques paramètres courants
  foreach ( array( 'autoplay', 'controls', 'mute', 'start', 'end', 'loop' ) as $k ) {
    if ( isset( $params[ $k ] ) ) {
      $attrs[ $k ] = $params[ $k ];
    }
  }

  // Construire la div
  $parts = array();
  foreach ( $attrs as $k => $v ) {
    $parts[] = $k . '="' . esc_attr( $v ) . '"';
  }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

function kd_tac_build_googlemaps_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );

  // Récupérer l'URL de l'iframe
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) {
    $src = $url_hint;
  }
  $safe_url = esc_url( $src );

  // Wrapper recommandé par TAC pour les iframes Google Maps embeds
  // Utilise le service "googlemapsembed" qui lit data-url et la taille de l'élément
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="googlemapsembed" data-url="' . $safe_url . '" style="' . $style . '"></div>';
}

function kd_tac_normalize_css_size( $val ) {
  // Ajoute px si valeur numérique pure
  if ( is_numeric( $val ) ) {
    return $val . 'px';
  }
  return $val;
}

// SlideShare
function kd_tac_build_slideshare_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $id = '';
  if ( preg_match( '#/slideshow/embed_code/key/([^/?\"\']+)#', $src, $m ) ) {
    $id = $m[1];
  }
  if ( ! $id ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="slideshare-canvas" data-id="' . esc_attr( $id ) . '" style="' . $style . '"></div>';
}

// Calameo
function kd_tac_build_calameo_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $id = '';
  if ( preg_match( '#[?&]bkcode=([^&\"\']+)#', $src, $m ) ) {
    $id = $m[1];
  }
  if ( ! $id && preg_match( '#/(?:embed\.html)?(?:\?|#).*bkcode=([^&\"\']+)#', $src, $m ) ) {
    $id = $m[1];
  }
  if ( ! $id ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="calameo-canvas" data-id="' . esc_attr( $id ) . '" style="' . $style . '"></div>';
}

// Genially
function kd_tac_build_genially_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $id = '';
  if ( preg_match( '#genial\.ly/([^/?\"\']+)#', $src, $m ) ) {
    $id = $m[1];
  }
  if ( ! $id ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="tac_genially" geniallyid="' . esc_attr( $id ) . '" style="' . $style . '"></div>';
}

// PlayPlay
function kd_tac_build_playplay_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $id = '';
  if ( preg_match( '#playplay\.com/app/embed-video/([^/?\"\']+)#', $src, $m ) ) {
    $id = $m[1];
  }
  if ( ! $id ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="tac_playplay" data-id="' . esc_attr( $id ) . '" style="' . $style . '"></div>';
}

// Twitch (vidéo)
function kd_tac_build_twitch_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $video = '';
  $parsed = wp_parse_url( $src );
  if ( isset( $parsed['query'] ) ) {
    parse_str( $parsed['query'], $q );
    if ( ! empty( $q['video'] ) ) { $video = $q['video']; }
  }
  if ( ! $video ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $host = parse_url( home_url(), PHP_URL_HOST );
  $attrs = array(
    'class' => 'twitch_player',
    'videoID' => $video,
    'parent' => $host,
    'width' => $width,
    'height' => $height,
  );
  $parts = array();
  foreach ( $attrs as $k => $v ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

// Facebook post
function kd_tac_build_facebookpost_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $post_url = '';
  $parsed = wp_parse_url( $src );
  if ( isset( $parsed['query'] ) ) {
    parse_str( $parsed['query'], $q );
    if ( ! empty( $q['href'] ) ) { $post_url = $q['href']; }
  }
  if ( ! $post_url ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="tac_facebookpost" data-url="' . esc_url( $post_url ) . '" style="' . $style . '"></div>';
}

/**
 * Supprime les balises <script> d'intégration connues (Twitter, Instagram, TikTok)
 * afin que Tarteaucitron gère leur chargement après consentement.
 */
function kd_tac_strip_known_embed_scripts( $html ) {
  if ( empty( $html ) ) return $html;
  // Retirer les scripts de widgets/embeds (Twitter/Instagram/TikTok)
  $patterns = array(
    '#<script[^>]+src=["\']https?://platform\.twitter\.com/widgets\.js[^>]*>\s*</script>#i',
    '#<script[^>]+src=["\']https?://www\.instagram\.com/embed\.js[^>]*>\s*</script>#i',
    '#<script[^>]+src=["\']https?://www\.tiktok\.com/embed\.js[^>]*>\s*</script>#i',
  );
  $html = preg_replace( $patterns, '', $html );
  return $html;
}
// Vimeo
function kd_tac_build_vimeo_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $video_id = '';
  if ( preg_match( '#vimeo\.com/(?:video/)?([0-9]+)#', $src, $m ) ) {
    $video_id = $m[1];
  }
  if ( ! $video_id ) {
    // fallback générique
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $attrs = array(
    'class' => 'vimeo_player',
    'videoID' => $video_id,
    'width' => $width,
    'height' => $height,
  );
  $parts = array();
  foreach ( $attrs as $k => $v ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

// Dailymotion
function kd_tac_build_dailymotion_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $video_id = '';
  if ( preg_match( '#dailymotion\.com/(?:embed/)?(?:video|playlist)/([A-Za-z0-9]+)#', $src, $m ) ) {
    $video_id = $m[1];
  }
  if ( ! $video_id && preg_match( '#dai\.ly/([A-Za-z0-9]+)#', $src, $m ) ) {
    $video_id = $m[1];
  }
  if ( ! $video_id ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $attrs = array(
    'class' => 'dailymotion_player',
    'videoID' => $video_id,
    'width' => $width,
    'height' => $height,
    'embedType' => 'video',
  );
  $parts = array();
  foreach ( $attrs as $k => $v ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

// OpenStreetMap
function kd_tac_build_openstreetmap_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $safe_url = esc_url( $src );
  $style = 'width:' . esc_attr( kd_tac_normalize_css_size( $width ) ) . ';height:' . esc_attr( kd_tac_normalize_css_size( $height ) ) . ';';
  return '<div class="openstreetmap" data-url="' . $safe_url . '" style="' . $style . '"></div>';
}

// SoundCloud
function kd_tac_build_soundcloud_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  // Extraire le paramètre ?url= (décodé)
  $playable_url = '';
  $parsed = wp_parse_url( $src );
  if ( isset( $parsed['query'] ) ) {
    parse_str( $parsed['query'], $q );
    if ( ! empty( $q['url'] ) ) {
      $playable_url = urldecode( $q['url'] );
    }
  }
  if ( empty( $playable_url ) ) {
    // fallback générique
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $attrs = array(
    'class' => 'soundcloud_player',
    'data-playable-url' => $playable_url,
    'data-height' => kd_tac_normalize_css_size( $height ),
  );
  $parts = array();
  foreach ( $attrs as $k => $v ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

// Spotify
function kd_tac_build_spotify_wrapper_from_html( $html, $url_hint = '' ) {
  list( $width, $height ) = kd_tac_extract_width_height_from_iframe_html( $html );
  $src = '';
  if ( preg_match( '#src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $ms ) ) {
    $src = $ms[1];
  }
  if ( empty( $src ) ) { $src = $url_hint; }
  $spotify_id = '';
  // src ex: https://open.spotify.com/embed/album/ID ... or /embed/track/ID ... or /embed/playlist/ID
  if ( preg_match( '#open\.spotify\.com\/embed\/([^\"\'/]+\/[^\"\'?]+)#', $src, $m ) ) {
    $spotify_id = $m[1];
  }
  if ( empty( $spotify_id ) ) {
    $safe_url = esc_url( $src );
    return '<div class="tac_iframe" url="' . $safe_url . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"></div>';
  }
  $attrs = array(
    'class' => 'spotify_player',
    'spotifyID' => $spotify_id,
    'width' => $width,
    'height' => $height,
  );
  $parts = array();
  foreach ( $attrs as $k => $v ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; }
  return '<div ' . implode( ' ', $parts ) . '></div>';
}

?>