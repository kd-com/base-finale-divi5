<?php
function afficher_reglages_couleurs() {
    ?>
    <div class="wrap">
        <h1>Gestion des couleurs</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('reglages_site_couleurs_group');
            do_settings_sections('reglages-site-couleurs');
            submit_button();
            ?>
        </form>
        <?php
        // Bouton de réinitialisation de la palette Divi
        if (current_user_can('manage_options')) {
            if (isset($_GET['force_divi_palette_reset'])) {
                if (function_exists('kd_sync_divi_palette')) {
                    kd_sync_divi_palette();
                }
                global $wpdb;
                $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'et_core_options_cache%' OR option_name LIKE '_transient_et_core_options_cache%'");
                echo '<div class="notice notice-success"><strong>Palette Divi et cache réinitialisés.</strong></div>';
            }
            echo '<a href="?page=reglages-site-couleurs&force_divi_palette_reset=1" class="button" style="margin-top:20px;">Forcer la réinitialisation de la palette Divi</a>';
        }
        ?>
    </div>
    <?php
}
add_action('admin_init', function() {
    $couleurs = [
        'couleur_titrage' => ['label' => 'Couleur titrage', 'default' => '#22282d'],
        'couleur_texte' => ['label' => 'Couleur texte', 'default' => '#3e464b'],
        'couleur_lien' => ['label' => 'Couleur lien', 'default' => '#e84448'],
        'couleur_fond' => ['label' => 'Couleur fond', 'default' => '#3db27c'],
        'couleur_fond2' => ['label' => 'Couleur fond 2', 'default' => '#424242'],
        'couleur_blanche' => ['label' => 'Blanc', 'default' => '#ffffff'],
        'couleur_noire' => ['label' => 'Noir', 'default' => '#000000'],
    ];
    add_settings_section('section_couleurs', '', null, 'reglages-site-couleurs');
    foreach ($couleurs as $slug => $data) {
        register_setting('reglages_site_couleurs_group', $slug);
        add_settings_field($slug, $data['label'], function() use ($slug, $data) {
            $val = get_option($slug, $data['default']);
            echo '<input type="text" name="' . esc_attr($slug) . '" value="' . esc_attr($val) . '" class="regular-text" style="width:120px;">';
            echo '<span style="display:inline-block;width:24px;height:24px;margin-left:8px;vertical-align:middle;background:' . esc_attr($val) . ';border:1px solid #ccc;"></span>';
        }, 'reglages-site-couleurs', 'section_couleurs');
    }
});
