<?php
/**
 * Presets de boutons Divi animés
 * 
 * Enregistre des presets pour les boutons Divi avec les animations
 * Les couleurs utilisent les variables du thème par défaut mais peuvent être modifiées dans le Visual Builder
 */

if (!defined('ABSPATH')) exit;

/**
 * Ajoute les presets de boutons Divi à la bibliothèque
 */
function kd_add_divi_button_library_items() {
    // Vérifier si on est dans l'admin ou le Visual Builder
    if (!is_admin() && !et_core_is_fb_enabled()) {
        return;
    }
    
    // Ce fichier est généré automatiquement par le thème
    $couleur_titrage = '#22282d';
    $couleur_texte = '#3e464b';
    $couleur_lien = '#e84448';
    $couleur_fond = '#3db27c';
    $couleur_fond2 = '#424242';
    $couleur_blanche = '#ffffff';
    $couleur_noire = '#000000';
    $divi_accent = '#e84448';
    
    $presets = array(
        // 1. BTN-SHINE - Brillance
        array(
            'title' => 'Bouton Brillance (Shine)',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_blanche . '" button_bg_use_color_gradient="on" button_bg_color_gradient_start="' . $couleur_fond . '" button_bg_color_gradient_end="' . $couleur_lien . '" button_bg_color_gradient_direction="90deg" button_border_width="0px" button_border_radius="100px" button_use_icon="off" box_shadow_style="preset6" box_shadow_horizontal="50px" box_shadow_vertical="-23px" box_shadow_blur="32px" box_shadow_spread="-20px" box_shadow_color="rgba(255,255,255,0.54)" custom_padding="10px|20px|10px|20px|true|true" hover_transition_duration="900ms" box_shadow_horizontal__hover="-50px" box_shadow_vertical__hover="30px" box_shadow_color__hover="rgba(255,255,255,0.49)" /]',
            'categories' => array('button'),
        ),
        
        // 2. BTN-SLIDE-RIGHT - Remplissage Droite
        array(
            'title' => 'Bouton Remplissage Droite',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_lien . '" button_bg_color="transparent" button_border_width="1px" button_border_color="' . $couleur_lien . '" button_border_radius="0px" button_use_icon="off" box_shadow_style="preset7" box_shadow_horizontal="13px" box_shadow_vertical="0px" box_shadow_blur="0px" box_shadow_color="' . $couleur_lien . '" custom_padding="12px|25px|12px|25px" hover_transition_duration="500ms" hover_transition_speed_curve="ease-in" button_text_color__hover="' . $couleur_blanche . '" box_shadow_horizontal__hover="-126px" box_shadow_spread__hover="0px" /]',
            'categories' => array('button'),
        ),
        
        // 3. BTN-CIRCLE-EXPAND - Cercle Expansif
        array(
            'title' => 'Bouton Cercle Expansif',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_lien . '" button_bg_color="' . $couleur_accent . '" button_border_width="0px" button_border_radius="100px" button_icon="%%59%%" button_icon_color="' . $couleur_lien . '" button_icon_placement="left" button_on_hover="off" box_shadow_style="preset6" box_shadow_horizontal="-105px" box_shadow_blur="0px" box_shadow_color="' . $couleur_blanche . '" custom_padding="12px|20px|12px|45px" hover_transition_duration="400ms" hover_transition_speed_curve="ease-in-out" button_text_color__hover="' . $couleur_blanche . '" button_bg_color__hover="' . $couleur_lien . '" button_icon_color__hover="' . $couleur_blanche . '" box_shadow_horizontal__hover="0px" /]',
            'categories' => array('button'),
        ),
        
        // 4. BTN-SLIDE-UP - Remplissage Haut
        array(
            'title' => 'Bouton Remplissage Haut',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_blanche . '" button_bg_color="' . $couleur_accent . '" button_border_width="1px" button_border_color="' . $couleur_accent . '" button_border_radius="0px" button_icon="%%36%%" button_icon_color="' . $couleur_blanche . '" button_icon_placement="left" button_on_hover="off" box_shadow_style="preset7" box_shadow_horizontal="0px" box_shadow_vertical="0px" box_shadow_blur="0px" box_shadow_color="' . $couleur_blanche . '" custom_padding="12px|20px|12px|40px" hover_transition_duration="200ms" hover_transition_speed_curve="ease-out" button_text_color__hover="' . $couleur_accent . '" button_icon_color__hover="' . $couleur_accent . '" box_shadow_vertical__hover="-50px" box_shadow_spread__hover="0px" /]',
            'categories' => array('button'),
        ),
        
        // 5. BTN-ARROW-TAB - Onglet Flèche
        array(
            'title' => 'Bouton Onglet Flèche',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_blanche . '" button_bg_color="' . $couleur_lien . '" button_border_width="0px" button_icon="%%36%%" button_icon_color="' . $couleur_accent . '" button_icon_placement="left" button_on_hover="off" box_shadow_style="preset7" box_shadow_horizontal="32px" box_shadow_vertical="0px" box_shadow_blur="0px" box_shadow_color="' . $couleur_accent . '" custom_padding="12px|20px|12px|50px" hover_transition_duration="300ms" hover_transition_speed_curve="ease-in-out" box_shadow_horizontal__hover="22px" /]',
            'categories' => array('button'),
        ),
        
        // 6. BTN-SHADOW-LIFT - Élévation Ombre
        array(
            'title' => 'Bouton Élévation Ombre',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_blanche . '" button_bg_color="' . $couleur_fond . '" button_border_width="0px" button_border_radius="0px" button_use_icon="off" box_shadow_style="preset1" box_shadow_vertical="2px" box_shadow_blur="5px" box_shadow_color="rgba(0,0,0,0.1)" custom_padding="12px|24px|12px|24px" hover_transition_duration="300ms" box_shadow_vertical__hover="8px" box_shadow_blur__hover="20px" box_shadow_color__hover="rgba(0,0,0,0.2)" /]',
            'categories' => array('button'),
        ),
        
        // 7. BTN-BORDER-GROW - Bordures Croissantes
        array(
            'title' => 'Bouton Bordures Croissantes',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_lien . '" button_bg_color="transparent" button_border_width="0px" button_border_radius="0px" button_use_icon="off" box_shadow_style="preset6" box_shadow_horizontal="0px" box_shadow_vertical="2px" box_shadow_blur="0px" box_shadow_spread="0px" box_shadow_color="' . $couleur_lien . '" custom_padding="12px|24px|12px|24px" hover_transition_duration="400ms" button_text_color__hover="' . $couleur_blanche . '" box_shadow_horizontal__hover="200px" box_shadow_vertical__hover="2px" /]',
            'categories' => array('button'),
        ),
        
        // 8. BTN-FILL-CENTER - Remplissage Central
        array(
            'title' => 'Bouton Remplissage Central',
            'content' => '[et_pb_button button_text="Cliquez ici" _builder_version="4.0" custom_button="on" button_text_color="' . $couleur_lien . '" button_bg_color="transparent" button_border_width="2px" button_border_color="' . $couleur_lien . '" button_border_radius="0px" button_use_icon="off" box_shadow_style="preset6" box_shadow_horizontal="0px" box_shadow_vertical="0px" box_shadow_blur="0px" box_shadow_spread="0px" box_shadow_color="' . $couleur_lien . '" custom_padding="12px|24px|12px|24px" hover_transition_duration="400ms" button_text_color__hover="' . $couleur_blanche . '" box_shadow_spread__hover="200px" /]',
            'categories' => array('button'),
        ),
    );
    
    return $presets;
}

/**
 * Ajoute les CSS classes pour les boutons Divi
 * Permet d'ajouter automatiquement les classes CSS nécessaires
 */
add_filter('et_builder_module_classes', 'kd_add_button_animation_classes', 10, 2);

function kd_add_button_animation_classes($classes, $module) {
    if ($module->slug !== 'et_pb_button') {
        return $classes;
    }
    
    // Détecter le type de preset basé sur les propriétés du bouton
    $props = $module->props;
    
    // Shine - gradient + box-shadow avec décalage important
    if (isset($props['button_bg_use_color_gradient']) && $props['button_bg_use_color_gradient'] === 'on' 
        && isset($props['box_shadow_horizontal']) && abs(intval($props['box_shadow_horizontal'])) > 40) {
        $classes[] = 'btn-shine';
    }
    
    // Slide Right - box-shadow horizontal qui change beaucoup au hover
    else if (isset($props['box_shadow_style']) && $props['box_shadow_style'] === 'preset7'
        && isset($props['box_shadow_horizontal__hover']) && intval($props['box_shadow_horizontal__hover']) < -100) {
        $classes[] = 'btn-slide-right';
    }
    
    // Circle Expand - avec icône et box-shadow négatif important
    else if (isset($props['button_icon']) && isset($props['box_shadow_horizontal']) 
        && intval($props['box_shadow_horizontal']) < -90) {
        $classes[] = 'btn-circle-expand';
    }
    
    // Slide Up - box-shadow vertical qui change au hover
    else if (isset($props['box_shadow_vertical__hover']) && intval($props['box_shadow_vertical__hover']) < -40) {
        $classes[] = 'btn-slide-up';
    }
    
    // Arrow Tab - box-shadow horizontal entre 20 et 40
    else if (isset($props['box_shadow_horizontal']) && intval($props['box_shadow_horizontal']) >= 20 
        && intval($props['box_shadow_horizontal']) <= 40) {
        $classes[] = 'btn-arrow-tab';
    }
    
    // Shadow Lift - box-shadow qui augmente au hover (vertical)
    else if (isset($props['box_shadow_vertical__hover']) && intval($props['box_shadow_vertical__hover']) > 5
        && isset($props['box_shadow_blur__hover']) && intval($props['box_shadow_blur__hover']) > 10) {
        $classes[] = 'btn-shadow-lift';
    }
    
    // Border Grow - box-shadow horizontal important au hover + inset
    else if (isset($props['box_shadow_horizontal__hover']) && intval($props['box_shadow_horizontal__hover']) > 100
        && isset($props['box_shadow_vertical']) && intval($props['box_shadow_vertical']) > 0) {
        $classes[] = 'btn-border-grow';
    }
    
    // Fill Center - box-shadow spread important au hover
    else if (isset($props['box_shadow_spread__hover']) && intval($props['box_shadow_spread__hover']) > 100) {
        $classes[] = 'btn-fill-center';
    }
    
    return $classes;
}

/**
 * Documentation pour l'utilisateur dans le Visual Builder
 */
add_action('admin_notices', 'kd_divi_buttons_info');

function kd_divi_buttons_info() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_et_divi_options') {
        ?>
        <div class="notice notice-info">
            <h3>🎨 Boutons Animés Divi - KD Theme</h3>
            <p><strong>8 styles de boutons animés sont disponibles :</strong></p>
            <ol>
                <li><strong>Brillance (Shine)</strong> : Gradient avec effet de lumière qui se déplace</li>
                <li><strong>Remplissage Droite</strong> : Remplissage depuis la droite au hover</li>
                <li><strong>Cercle Expansif</strong> : Cercle qui s'agrandit depuis la gauche</li>
                <li><strong>Remplissage Haut</strong> : Remplissage depuis le bas vers le haut</li>
                <li><strong>Onglet Flèche</strong> : Onglet avec triangle qui se rétracte</li>
                <li><strong>Élévation Ombre</strong> : Le bouton s'élève avec une ombre</li>
                <li><strong>Bordures Croissantes</strong> : Bordures qui grandissent pour remplir</li>
                <li><strong>Remplissage Central</strong> : Remplissage depuis le centre</li>
            </ol>
            <p><strong>Comment utiliser :</strong></p>
            <ul>
                <li>Dans le Visual Builder, ajoutez un module Bouton</li>
                <li>Configurez les couleurs, texte, bordures et box-shadow comme indiqué dans la documentation</li>
                <li>Les classes CSS seront automatiquement ajoutées selon vos paramètres</li>
                <li>Toutes les couleurs sont modifiables directement dans le Visual Builder</li>
                <li><strong>Les styles par défaut de Divi sont automatiquement neutralisés</strong></li>
            </ul>
            <p>📚 <strong>Documentation complète :</strong> <code>/docs/DIVI-BUTTONS.md</code></p>
        </div>
        <?php
    }
}
