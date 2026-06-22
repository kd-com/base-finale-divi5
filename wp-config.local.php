<?php
/**
 * Configuration WordPress générique et réutilisable
 * Compatible avec tous les containers WordPress
 * 
 * Ce fichier utilise les variables d'environnement Docker
 * définies dans docker-compose.yml
 * 
 * Les valeurs par défaut seront remplacées par le script 03_init-wp.sh
 */

// ============================================
// 🔒 HTTPS depuis le proxy Traefik
// ============================================
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
}

if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// Force le protocole HTTPS pour toutes les requêtes
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['REQUEST_SCHEME'] = 'https';
}

// ============================================
// 🌐 Configuration URL WordPress
// ============================================
$wp_domain = getenv('WP_DOMAIN') ?: 'localhost';
$wp_protocol = 'https';

define('WP_HOME', $wp_protocol . '://' . $wp_domain);
define('WP_SITEURL', $wp_protocol . '://' . $wp_domain);
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);
define('WP_CONTENT_URL', $wp_protocol . '://' . $wp_domain . '/wp-content');
define('WP_PLUGIN_URL', $wp_protocol . '://' . $wp_domain . '/wp-content/plugins');

// ============================================
// 🗄️ Configuration Base de Données
// ============================================
define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress_user');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'password');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Préfixe de table
$table_prefix = getenv('WORDPRESS_TABLE_PREFIX') ?: 'wp_';

// ============================================
// 🔐 Clés d'authentification uniques
// ============================================
// Ces clés seront remplacées par le script 03_init-wp.sh

// ============================================
// 🛠️ Configuration Développement
// ============================================
define('WP_DEBUG', getenv('WP_DEBUG') !== false ? filter_var(getenv('WP_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true);
define('WP_DEBUG_LOG', getenv('WP_DEBUG_LOG') !== false ? filter_var(getenv('WP_DEBUG_LOG'), FILTER_VALIDATE_BOOLEAN) : true);
define('WP_DEBUG_DISPLAY', getenv('WP_DEBUG_DISPLAY') !== false ? filter_var(getenv('WP_DEBUG_DISPLAY'), FILTER_VALIDATE_BOOLEAN) : false);
define('SCRIPT_DEBUG', getenv('SCRIPT_DEBUG') !== false ? filter_var(getenv('SCRIPT_DEBUG'), FILTER_VALIDATE_BOOLEAN) : false);

// Log WordPress dans wp-content/debug.log
if (WP_DEBUG_LOG) {
    @ini_set('log_errors', 1);
    @ini_set('error_log', '/var/www/html/wp-content/debug.log');
}

// ============================================
// ⚡ Optimisations Performance
// ============================================
define('CONCATENATE_SCRIPTS', false); // Important pour DIVI Builder
define('COMPRESS_SCRIPTS', false);    // Désactivé pour le debug
define('COMPRESS_CSS', false);        // Désactivé pour le debug
define('ENFORCE_GZIP', true);
define('WP_CACHE', false);            // Désactivé en dev

// ============================================
// 🎨 Configuration spécifique DIVI
// ============================================
// Augmente les limites pour le Visual Builder
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_vars', '3000');
@ini_set('post_max_size', '64M');
@ini_set('upload_max_filesize', '64M');

// ============================================
// 🔐 Sécurité
// ============================================
define('DISALLOW_FILE_EDIT', false);  // Permettre l'édition en dev
define('DISALLOW_FILE_MODS', false);  // Permettre les installations en dev
define('ALLOW_UNFILTERED_UPLOADS', false);

// Protection contre les attaques
define('WP_POST_REVISIONS', 5);
define('AUTOSAVE_INTERVAL', 300);

// ============================================
// 🌍 Localisation
// ============================================
define('WPLANG', 'fr_FR');
define('WP_LANG_DIR', ABSPATH . 'wp-content/languages');

// ============================================
// 📊 API et Services externes
// ============================================
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);

// ============================================
// 🔧 Corrections spécifiques DIVI
// ============================================
// Headers CORS pour DIVI Builder
if (!headers_sent()) {
    header('Access-Control-Allow-Origin: ' . $wp_protocol . '://' . $wp_domain);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Fix pour l'historique du navigateur (erreur replaceState)
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['SCRIPT_URI'] = $wp_protocol . '://' . $wp_domain . $_SERVER['REQUEST_URI'];
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';