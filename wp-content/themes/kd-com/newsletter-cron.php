<?php
/**
 * ========================================
 * SCRIPT CRON AUTONOME - NEWSLETTER BREVO
 * Adapté pour environnement Docker
 * ========================================
 *
 * EMPLACEMENT : wp-content/themes/kd-com/newsletter-cron.php
 * CHEMIN CONTAINER : /var/www/html/wp-content/themes/kd-com/newsletter-cron.php
 *
 * OPTION 1 - Cron dans le container WordPress :
 *   docker exec -it <container> bash
 *   apt-get install -y cron
 *   crontab -e
 *   → 0 * * * * php /var/www/html/wp-content/themes/kd-com/newsletter-cron.php >> /var/log/newsletter-cron.log 2>&1
 *
 * OPTION 2 - Service cron dans docker-compose.yml (voir page admin)
 *
 * OPTION 3 - Via URL (cron-job.org) :
 *   https://votre-site.com/wp-content/themes/kd-com/newsletter-cron.php?cron_key=CLE
 */

define('NEWSLETTER_CRON_VERSION', '1.0.0');
define('NEWSLETTER_CRON_START', microtime(true));

$is_cli   = (php_sapi_name() === 'cli');
$is_debug = $is_cli ? in_array('--debug', $argv ?? []) : isset($_GET['debug']);

// ============================================
// TROUVER LA RACINE WORDPRESS
// Ce fichier est dans : wp-content/themes/kd-com/
// On remonte 3 niveaux pour atteindre la racine WP
// ============================================
$wp_root = dirname(dirname(dirname(dirname(__FILE__))));
// __FILE__  = /var/www/html/wp-content/themes/kd-com/newsletter-cron.php
// dirname 1 = /var/www/html/wp-content/themes/kd-com
// dirname 2 = /var/www/html/wp-content/themes
// dirname 3 = /var/www/html/wp-content
// dirname 4 = /var/www/html  <- racine WordPress

if (!file_exists($wp_root . '/wp-load.php')) {
    if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
        $wp_root = rtrim(ABSPATH, '/');
    } else {
        die('Erreur : wp-load.php introuvable. Chemin : ' . $wp_root . ' | __FILE__ : ' . __FILE__);
    }
}

// ============================================
// VÉRIFICATION CLÉ SECRÈTE (mode HTTP uniquement)
// ============================================
if (!$is_cli) {
    $provided_key = $_GET['cron_key'] ?? '';
    if (empty($provided_key) || strlen($provided_key) < 32) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

// ============================================
// CHARGEMENT DE WORDPRESS
// ============================================
if ($is_cli) {
    $_SERVER['HTTP_HOST']      = 'localhost';
    $_SERVER['REQUEST_URI']    = '/';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['SERVER_NAME']    = 'localhost';
    $_SERVER['SERVER_PORT']    = '80';
    error_reporting(E_ERROR | E_PARSE);
}

require_once($wp_root . '/wp-load.php');

// ============================================
// VÉRIFICATION CLÉ APRÈS CHARGEMENT WP
// ============================================
if (!$is_cli) {
    $stored_key   = get_option('brevo_newsletter_cron_key', '');
    $provided_key = sanitize_text_field($_GET['cron_key'] ?? '');
    if (empty($stored_key) || !hash_equals($stored_key, $provided_key)) {
        http_response_code(403);
        die('Clé invalide. Configurez-la dans WordPress > Newsletter > Cron autonome.');
    }
}

// ============================================
// VERROU ANTI-DOUBLONS
// ============================================
$lock_file    = $wp_root . '/wp-content/newsletter-cron.lock';
$lock_timeout = 3600;

if (file_exists($lock_file)) {
    $lock_time = (int) file_get_contents($lock_file);
    if ((time() - $lock_time) < $lock_timeout) {
        cron_log('INFO: Processus déjà en cours. Abandon.');
        exit(0);
    }
    cron_log('AVERTISSEMENT: Verrou expiré, suppression.');
    unlink($lock_file);
}

file_put_contents($lock_file, time());

register_shutdown_function(function () use ($lock_file) {
    if (file_exists($lock_file)) unlink($lock_file);
});

// ============================================
// LOGIQUE PRINCIPALE
// ============================================
cron_log('=== Newsletter Cron v' . NEWSLETTER_CRON_VERSION . ' ===');
cron_log('Mode        : ' . ($is_cli ? 'CLI' : 'HTTP'));
cron_log('Heure WP    : ' . current_time('Y-m-d H:i:s'));
cron_log('Chemin WP   : ' . $wp_root);

if (!function_exists('is_brevo_configured') || !is_brevo_configured()) {
    cron_log('ERREUR: Brevo non configuré.');
    cron_history('error', 'Brevo non configuré');
    exit(1);
}
cron_log('Brevo       : OK');

$auto_send = get_option('brevo_newsletter_auto_send', '0');
if ($auto_send !== '1') {
    cron_log('INFO: Envoi automatique désactivé. Arrêt.');
    cron_history('skipped', 'Envoi automatique désactivé');
    exit(0);
}

$send_day    = (int) get_option('brevo_newsletter_send_day', 1);
$send_hour   = (int) get_option('brevo_newsletter_send_hour', 9);
$send_minute = (int) get_option('brevo_newsletter_send_minute', 0);
$tolerance   = (int) get_option('brevo_newsletter_cron_tolerance', 30);

$now            = current_time('timestamp');
$current_day    = (int) date('j', $now);
$current_hour   = (int) date('G', $now);
$current_minute = (int) date('i', $now);

cron_log(sprintf('Envoi prévu : le %d à %02d:%02d (tolérance ±%d min)', $send_day, $send_hour, $send_minute, $tolerance));
cron_log(sprintf('Maintenant  : jour=%d, %02d:%02d', $current_day, $current_hour, $current_minute));

if ($current_day !== $send_day) {
    cron_log("INFO: Pas le bon jour (aujourd'hui=$current_day, prévu=$send_day). Arrêt.");
    cron_history('skipped', "Mauvais jour (aujourd'hui=$current_day, prévu=$send_day)");
    exit(0);
}

$send_total    = $send_hour * 60 + $send_minute;
$current_total = $current_hour * 60 + $current_minute;
$diff          = abs($current_total - $send_total);

if ($diff > $tolerance) {
    cron_log(sprintf('INFO: Heure incorrecte (écart=%d min > tolérance=%d min). Arrêt.', $diff, $tolerance));
    cron_history('skipped', "Mauvaise heure (écart=$diff min)");
    exit(0);
}
cron_log("Heure       : OK (écart=$diff min)");

$last_sent = get_option('brevo_newsletter_last_sent', '');
if (!empty($last_sent)) {
    $last_month    = date('Y-m', strtotime($last_sent));
    $current_month = date('Y-m', $now);
    if ($last_month === $current_month) {
        cron_log("INFO: Déjà envoyée ce mois ($last_sent). Arrêt.");
        cron_history('skipped', "Déjà envoyée ce mois ($last_sent)");
        exit(0);
    }
}

if (!function_exists('has_new_posts_to_send') || !has_new_posts_to_send()) {
    cron_log('INFO: Aucun nouveau contenu. Arrêt.');
    cron_history('skipped', 'Aucun nouveau contenu');
    exit(0);
}
cron_log('Contenu     : OK');

// ============================================
// ENVOI
// ============================================
cron_log('>>> Envoi en cours...');

try {
    if (!function_exists('send_newsletter_via_brevo')) {
        throw new Exception('Fonction send_newsletter_via_brevo() introuvable.');
    }

    $result   = send_newsletter_via_brevo(false);
    $duration = round(microtime(true) - NEWSLETTER_CRON_START, 2);

    if ($result['success']) {
        cron_log('SUCCÈS: ' . $result['message']);
        cron_log("Durée: {$duration}s");
        cron_history('success', $result['message']);

        wp_mail(
            get_option('admin_email'),
            '[' . get_bloginfo('name') . '] ✅ Newsletter envoyée automatiquement',
            "Newsletter envoyée avec succès.\n\nDate : " . current_time('d/m/Y H:i') .
            "\nMessage : " . $result['message'] . "\nDurée : {$duration}s"
        );
        exit(0);

    } else {
        $err = $result['message'] ?? 'Erreur inconnue';
        cron_log('ÉCHEC: ' . $err);
        cron_history('error', $err);
        wp_mail(
            get_option('admin_email'),
            '[' . get_bloginfo('name') . '] ❌ Échec newsletter automatique',
            "L'envoi automatique a échoué.\n\nDate : " . current_time('d/m/Y H:i') . "\nErreur : " . $err
        );
        exit(1);
    }

} catch (Exception $e) {
    cron_log('ERREUR FATALE: ' . $e->getMessage());
    cron_history('error', 'Exception: ' . $e->getMessage());
    exit(1);
}

// ============================================
// FONCTIONS UTILITAIRES
// ============================================

function cron_log($message) {
    global $is_cli, $is_debug;

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;

    error_log('Newsletter Cron: ' . $message);

    if ($is_cli) {
        echo $line . PHP_EOL;
    }

    if (!$is_cli && $is_debug) {
        echo htmlspecialchars($line) . "<br>\n";
        if (ob_get_level()) ob_flush();
    }

    if (function_exists('wp_upload_dir')) {
        $upload = wp_upload_dir();
        $log    = $upload['basedir'] . '/newsletter-cron.log';
        if (file_exists($log) && filesize($log) > 1048576) {
            rename($log, $log . '.old');
        }
        file_put_contents($log, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function cron_history($status, $message) {
    if (!function_exists('get_option')) return;

    $history = get_option('brevo_newsletter_cron_history', []);
    if (count($history) >= 50) {
        $history = array_slice($history, -49);
    }

    $history[] = [
        'date'    => current_time('Y-m-d H:i:s'),
        'status'  => $status,
        'message' => $message,
    ];

    update_option('brevo_newsletter_cron_history', $history);
    update_option('brevo_newsletter_cron_last_run', current_time('Y-m-d H:i:s'));
}