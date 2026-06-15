<?php
// Inclusion des champs ACF pour la sous-page modules

// Variable globale pour stocker les champs ACF modules
$acf_module_fields = array();

// Chargement dynamique des modules présents dans le dossier acf_fields
$acf_fields_dir = __DIR__ . '/../acf_fields/';
$acf_files = glob($acf_fields_dir . '*.php');
foreach ($acf_files as $acf_file) {
    require_once $acf_file;
}

function get_acf_field_description_by_slug($field_name) {
    global $acf_module_fields;
    
    // Chercher dans le tableau global
    foreach ($acf_module_fields as $group) {
        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $field) {
                if (isset($field['name']) && $field['name'] === $field_name && !empty($field['instructions'])) {
                    return '<div style="background:#f8fafc;border-left:4px solid #e64449;padding:10px 15px;margin:8px 0 18px 0;border-radius:6px;color:#444;font-size:14px;line-height:1.6;">' . esc_html($field['instructions']) . '</div>';
                }
            }
        }
    }
    return '';
}

// Enregistrement dynamique des options modules
add_action('admin_init', function() {
    $acf_fields_dir = __DIR__ . '/../acf_fields/';
    $acf_files = glob($acf_fields_dir . '*.php');
    
    foreach ($acf_files as $acf_file) {
        $module_slug = basename($acf_file, '.php');
        $option_key = 'module_' . $module_slug;
        
        // Créer l'option si elle n'existe pas
        if (!get_option($option_key)) {
            add_option($option_key, '0', '', 'yes');
        }
        
        // Enregistrer le setting
        register_setting('reglages_site_modules_group', $option_key, [
            'type' => 'string',
            'sanitize_callback' => function($v) { return $v === '1' ? '1' : '0'; }
        ]);
    }
});

function afficher_reglages_modules() {
    ?>
    <div class="wrap">
        <h1 style="margin-bottom:24px;">Gestion des modules</h1>
        <form method="post" action="options.php" style="max-width:600px;">
            <?php
            settings_fields('reglages_site_modules_group');
            do_settings_sections('reglages-site-modules');

            $acf_fields_dir = __DIR__ . '/../acf_fields/';
            $acf_files = glob($acf_fields_dir . '*.php');

            // Regroupement par catégorie selon le préfixe
            $categories = [
                'slider_' => 'Slider',
                'cpt_' => 'Custom Post Type',
                'shortcode_' => 'Shortcode',
                // Ajoutez d'autres préfixes ici si besoin
            ];
            $modules_by_cat = [];
            foreach ($acf_files as $acf_file) {
                $module_slug = basename($acf_file, '.php');
                $found = false;
                foreach ($categories as $prefix => $cat_label) {
                    if (strpos($module_slug, $prefix) === 0) {
                        $modules_by_cat[$cat_label][] = $module_slug;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $modules_by_cat['Autres'][] = $module_slug;
                }
            }

            // Affichage trié par catégorie et par ordre alphabétique
            foreach ($modules_by_cat as $cat_label => $modules) {
                echo '<h2 style="margin-top:32px;margin-bottom:16px;color:#e64449;font-size:20px;">' . esc_html($cat_label) . '</h2>';
                sort($modules);
                foreach ($modules as $module_slug) {
                    $option_key = 'module_' . $module_slug;
                    $checked = checked(1, get_option($option_key), false);
                    $label = 'Activer le module ' . str_replace('_', ' ', $module_slug);
                    echo '<div style="margin-bottom:32px;"><label style="font-weight:600;font-size:16px;"><input type="checkbox" name="' . esc_attr($option_key) . '" value="1" ' . $checked . ' /> ' . esc_html($label) . '</label>';
                    // Correspondance stricte du champ ACF - cherche par slug du module
                    echo get_acf_field_description_by_slug($module_slug);
                    echo '</div>';
                }
            }

            submit_button();
            ?>
        </form>
    </div>
    <?php
}