<?php
/**
 * Sous-page de réglages pour Tarteaucitron (RGPD)
 */
if (!defined('ABSPATH')) {
    exit;
}

function kd_reglages_tarteaucitron_page() {
    settings_fields('reglages_site_tarteaucitron_group');
    do_settings_sections('reglages_site_tarteaucitron_group');
    // Inclure le formulaire d'admin Tarteaucitron
    if (function_exists('kd_tarteaucitron_page')) {
        kd_tarteaucitron_page();
    } else {
        echo '<div class="notice notice-error"><p>La fonction kd_tarteaucitron_page n\'est pas disponible.</p></div>';
    }
}


// Enregistrement des réglages
add_action('admin_init', function() {
    register_setting('reglages_site_tarteaucitron_group', 'kd_tarteaucitron_settings');
});
