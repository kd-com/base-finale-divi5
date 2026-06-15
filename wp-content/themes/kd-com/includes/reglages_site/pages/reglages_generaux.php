<?php
// Enqueue le script media uploader WordPress sur la page des réglages généraux
add_action('admin_enqueue_scripts', function($hook) {
    if (isset($_GET['page']) && $_GET['page'] === 'reglages-site-generaux') {
        wp_enqueue_media();
    }
});
function afficher_reglages_generaux() {
    ?>
    <div class="wrap">
        <h1>Réglages généraux</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('reglages_site_generaux_group');
            do_settings_sections('reglages-site-generaux');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
add_action('admin_init', function() {
    register_setting('reglages_site_generaux_group', 'adresse_du_site_w');
    register_setting('reglages_site_generaux_group', 'telephone');
    register_setting('reglages_site_generaux_group', 'image_par_defaut');
    register_setting('reglages_site_generaux_group', 'kd_com_aos_enabled', [
        'type' => 'string',
        'sanitize_callback' => function($v) { return $v === '1' ? '1' : '0'; }
    ]);
    add_settings_section('section_generaux', '', null, 'reglages-site-generaux');
    add_settings_field('adresse_du_site_w', 'Adresse', function() {
        $contentw = get_option('adresse_du_site_w');
        wp_editor($contentw, 'adresse_du_site_w', [
            'textarea_name' => 'adresse_du_site_w',
            'media_buttons' => false,
            'textarea_rows' => 8,
            'teeny' => true
        ]);
    }, 'reglages-site-generaux', 'section_generaux');
    add_settings_field('telephone', 'Téléphone', function() {
        echo '<input type="text" name="telephone" value="' . esc_attr(get_option('telephone')) . '" class="regular-text">';
    }, 'reglages-site-generaux', 'section_generaux');
    // Ajout du champ horaire d'ouverture avec éditeur WYSIWYG
        register_setting('reglages_site_generaux_group', 'horaire_ouverture');
        add_settings_field('horaire_ouverture', "Horaire d'ouverture", function() {
            $content = get_option('horaire_ouverture');
            wp_editor($content, 'horaire_ouverture', [
                'textarea_name' => 'horaire_ouverture',
                'media_buttons' => false,
                'textarea_rows' => 8,
                'teeny' => true
            ]);
        }, 'reglages-site-generaux', 'section_generaux');
    add_settings_field('image_par_defaut', 'Image par défaut', function() {
    $image_id = get_option('image_par_defaut');
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        echo '<input type="hidden" name="image_par_defaut" id="image_par_defaut" value="' . esc_attr($image_id) . '" />';
        echo '<img id="image_par_defaut_preview" src="' . esc_url($image_url) . '" style="max-width:150px;display:block;margin-bottom:10px;" />';
        echo '<button type="button" class="button" id="upload_image_par_defaut">Choisir une image</button>';
        ?>
        <script>
        jQuery(document).ready(function($){
            var frame;
            $('#upload_image_par_defaut').on('click', function(e){
                e.preventDefault();
                if(frame){ frame.open(); return; }
                frame = wp.media({ title: 'Sélectionner une image', button: { text: 'Utiliser cette image' }, multiple: false });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#image_par_defaut').val(attachment.id);
                    $('#image_par_defaut_preview').attr('src', attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }, 'reglages-site-generaux', 'section_generaux');

    add_settings_field('kd_com_aos_enabled', 'Activer AOS', function() {
        $value = get_option('kd_com_aos_enabled', '1');
        echo '<input type="checkbox" name="kd_com_aos_enabled" value="1"' . checked($value, '1', false) . ' />';
    }, 'reglages-site-generaux', 'section_generaux');

        // Ajout du réglage pour la maintenance KD-COM
        register_setting('reglages_site_generaux_group', 'maintenance_kd_com', [
            'type' => 'string',
            'sanitize_callback' => function($v) { return $v === '1' ? '1' : '0'; }
        ]);
        add_settings_field('maintenance_kd_com', 'Activer la maintenance KD-COM', function() {
            $value = get_option('maintenance_kd_com', '0');
            echo '<input type="checkbox" name="maintenance_kd_com" value="1"' . checked($value, '1', false) . ' />';
        }, 'reglages-site-generaux', 'section_generaux');
});
