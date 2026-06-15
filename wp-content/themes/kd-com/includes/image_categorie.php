<?php
/**
 * ============================
 *  FEATURED IMAGE POUR CATÉGORIES
 *  (Upload + Suppression + Fallback)
 * ============================
 */

// 1. Charger les scripts pour la médiathèque
 function cat_featured_image_admin_scripts($hook) {
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'category') {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }
}
add_action('admin_enqueue_scripts', 'cat_featured_image_admin_scripts');



// 2. Champ "image mise en avant" pour l’ajout d’une catégorie
function cat_featured_image_add_field() {
    ?>
    <div class="form-field">
        <label for="category_image">Image mise en avant</label>
        <input type="text" name="category_image" id="category_image" value="" />
        <button type="button" class="button upload_image_button">Choisir</button>
        <button type="button" class="button remove_image_button">Supprimer</button>

        <div class="preview-wrapper" style="margin-top:10px;"></div>
    </div>

    <script>
    jQuery(document).ready(function($){
        let mediaUploader;

        $('.upload_image_button').click(function(e){
            e.preventDefault();
            if (mediaUploader) return mediaUploader.open();

            mediaUploader = wp.media({
                title: 'Choisir une image',
                button: { text: 'Utiliser cette image' },
                multiple: false
            });

            mediaUploader.on('select', function(){
                let attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#category_image').val(attachment.url);
                $('.preview-wrapper').html('<img src="'+attachment.url+'" style="max-width:150px;">');
            });

            mediaUploader.open();
        });

        $('.remove_image_button').click(function(){
            $('#category_image').val('');
            $('.preview-wrapper').html('');
        });
    });
    </script>
    <?php
}
add_action('category_add_form_fields', 'cat_featured_image_add_field');


// 3. Champ image pour l’édition d’une catégorie
function cat_featured_image_edit_field($term) {
    $value = get_term_meta($term->term_id, 'category_image', true);
    ?>
    <tr class="form-field">
        <th><label for="category_image">Image mise en avant</label></th>
        <td>
            <input type="text" name="category_image" id="category_image" value="<?php echo esc_attr($value); ?>" />
            <button type="button" class="button upload_image_button">Choisir</button>
            <button type="button" class="button remove_image_button">Supprimer</button>

            <div class="preview-wrapper" style="margin-top:10px;">
                <?php if ($value) : ?>
                    <img src="<?php echo esc_url($value); ?>" style="max-width:150px;">
                <?php endif; ?>
            </div>
        </td>
    </tr>

    <script>
    jQuery(document).ready(function($){
        let mediaUploader;

        $('.upload_image_button').click(function(e){
            e.preventDefault();
            if (mediaUploader) return mediaUploader.open();

            mediaUploader = wp.media({
                title: 'Choisir une image',
                button: { text: 'Utiliser cette image' },
                multiple: false
            });

            mediaUploader.on('select', function(){
                let attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#category_image').val(attachment.url);
                $('.preview-wrapper').html('<img src="'+attachment.url+'" style="max-width:150px;">');
            });

            mediaUploader.open();
        });

        $('.remove_image_button').click(function(){
            $('#category_image').val('');
            $('.preview-wrapper').html('');
        });
    });
    </script>

    <?php
}
add_action('category_edit_form_fields', 'cat_featured_image_edit_field');


// 4. Sauvegarde des données
function cat_featured_image_save($term_id) {
    if (isset($_POST['category_image'])) {
        update_term_meta($term_id, 'category_image', sanitize_text_field($_POST['category_image']));
    }
}
add_action('created_category', 'cat_featured_image_save');
add_action('edited_category', 'cat_featured_image_save');


// 5. Fonction pour récupérer l’image avec fallback
function get_category_featured_image($term_id = null) {
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }

    $img = get_term_meta($term_id, 'category_image', true);

    // FALLBACK (mets ici l’URL de ton image par défaut)
    if (!$img) {
        $default_image = get_option('image_par_defaut');
        if ( $default_image ) {
            $img = wp_get_attachment_url($default_image);
        }
    }

    return $img;
}

// shortcode pour afficher l’image d’une catégorie
function category_featured_image_shortcode($atts) {
    // Récupérer l'ID de la catégorie actuelle
    $term_id = get_queried_object_id();

    // Récupérer l'URL de l'image
    $image_url = get_category_featured_image($term_id);

    // Vérifier si l'URL est valide
    if (!empty($image_url)) {
        return '<img src="' . esc_url($image_url) . '" alt="Image de la catégorie" class="category-featured-image" />';
    }

    return '';
}
add_shortcode('category_featured_image', 'category_featured_image_shortcode');
