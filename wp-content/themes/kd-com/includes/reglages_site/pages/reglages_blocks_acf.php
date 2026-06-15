<?php
// Sous-page pour activer/désactiver les blocks ACF du dossier blocks/
function afficher_reglages_blocks_acf() {
    // Fonction pour récupérer l'instruction du block depuis le fichier acf_fields
    function get_block_instruction($block) {
    $acf_file = dirname(__FILE__, 4) . '/blocks/acf_fields/acf-' . str_replace('_', '-', $block) . '.php';
        if (file_exists($acf_file)) {
            $content = file_get_contents($acf_file);
            if (preg_match('/["\']instruction["\']\s*=>\s*["\']([^"\']+)["\']/', $content, $matches)) {
                return '<div style="background:#f8fafc;border-left:4px solid #e64449;padding:10px 15px;margin:8px 0 18px 0;border-radius:6px;color:#444;font-size:14px;line-height:1.6;">' . esc_html($matches[1]) . '</div>';
            } else {
                return '<div style="color:red;">Instruction non trouvée dans :<br>' . esc_html($acf_file) . '</div>';
            }
        } else {
            return '<div style="color:red;">Fichier ACF non trouvé :<br>' . esc_html($acf_file) . '</div>';
        }
    }
    $blocks_declaration_dir = dirname(__FILE__, 4) . '/blocks/blocks_declaration/';
    $blocks = array();
    if (is_dir($blocks_declaration_dir)) {
        foreach (glob($blocks_declaration_dir . '*_block.php') as $file) {
            $block_name = basename($file, '_block.php');
            $blocks[] = $block_name;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['acf_blocks'])) {
            update_option('acf_blocks_actifs', $_POST['acf_blocks']);
        } else {
            update_option('acf_blocks_actifs', array());
        }
        echo '<div class="updated"><p>Réglages enregistrés.</p></div>';
    }
    $actifs = get_option('acf_blocks_actifs', array());
    echo '<div class="wrap">';
    echo '<h1 style="margin-bottom:24px;">Activation des blocks ACF</h1>';
    echo '<form method="post" style="max-width:600px;">';
    // Champ caché pour forcer l'envoi même si aucune case n'est cochée
    echo '<input type="hidden" name="acf_blocks[]" value="__none__" style="display:none;">';
    echo '<h2 style="margin-top:32px;margin-bottom:16px;color:#e64449;font-size:20px;">Blocks disponibles</h2>';
    foreach ($blocks as $block) {
        $checked = in_array($block, (array)$actifs) ? 'checked' : '';
        echo '<div style="margin-bottom:32px;">';
        echo '<label style="font-weight:600;font-size:16px;">';
        echo '<input type="checkbox" name="acf_blocks[]" value="' . esc_attr($block) . '" ' . $checked . '> ';
        echo 'Activer le block <span>' . esc_html($block) . '</span>';
        echo '</label>';
        echo get_block_instruction($block);
        echo '</div>';
    }
    echo '<p><input type="submit" class="button-primary" value="Enregistrer"></p>';
    echo '</form></div>';
}

// Enregistrement du réglage
add_action('admin_init', function() {
    register_setting('reglages_site_blocks_group', 'acf_blocks_actifs');
    add_settings_section('section_blocks', '', null, 'reglages-site-blocks');
});
