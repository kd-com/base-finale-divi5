<?php
/**
 * Newsletter CSS Inliner avec lecture automatique des couleurs du thème
 * 
 * Ce script lit automatiquement les couleurs depuis _theme-colors.scss
 * et les convertit en styles inline pour les emails
 * 
 * Usage dans vos templates:
 * require_once(BREVO_NEWSLETTER_DIR . '/newsletter-styles-inline.php');
 * echo style('classe-css');
 */

if (!defined('ABSPATH')) exit;

/**
 * Parse le fichier _theme-colors.scss et extrait les variables de couleur
 * 
 * @return array Tableau associatif des couleurs
 */
function brevo_parse_theme_colors() {
    static $colors_cache = null;
    
    // Cache pour éviter de parser à chaque appel
    if ($colors_cache !== null) {
        return $colors_cache;
    }
    
    // Chemin vers le fichier SCSS
    $scss_file = get_stylesheet_directory() . '/sass/_theme-colors.scss';
    
    // Valeurs par défaut si le fichier n'existe pas
    $default_colors = array(
        'couleur-titrage' => '#22282d',
        'couleur-texte' => '#3e464b',
        'couleur-lien' => '#e84448',
        'couleur-fond' => '#3db27c',
        'couleur-fond2' => '#424242',
        'couleur-blanche' => '#ffffff',
        'couleur-noire' => '#000000',
        'divi-accent' => '#e84448',
    );
    
    if (!file_exists($scss_file)) {
        $colors_cache = $default_colors;
        return $colors_cache;
    }
    
    $colors = array();
    $content = file_get_contents($scss_file);
    
    // Parse les variables SCSS : $nom-variable: #couleur;
    preg_match_all('/\$([a-z0-9\-]+)\s*:\s*(#[0-9a-fA-F]{3,6}|rgba?\([^)]+\));/i', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $index => $var_name) {
            $colors[$var_name] = $matches[2][$index];
        }
    }
    
    // Merge avec les valeurs par défaut pour les variables manquantes
    $colors_cache = array_merge($default_colors, $colors);
    
    return $colors_cache;
}

/**
 * Définition de tous les styles de newsletter
 * Utilise automatiquement les couleurs du thème
 */
function brevo_get_newsletter_styles() {
    // Récupérer les couleurs du thème automatiquement
    $theme_colors = brevo_parse_theme_colors();
    
    $couleur_blanche = $theme_colors['couleur-blanche'];
    $couleur_fond = $theme_colors['couleur-fond'];
    $couleur_fond2 = $theme_colors['couleur-fond2'];
    $couleur_titrage = $theme_colors['couleur-titrage'];
    $couleur_lien = $theme_colors['couleur-lien'];
    $couleur_texte = $theme_colors['couleur-texte'];

    
    return array(
        // Structure
        'body' => 'margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;',
        
        'email-wrapper' => 'width: 100%; margin: 0; padding: 0;',
        'email-wrapper-td' => 'padding: 20px 0;',
        
        'email-container' => 'max-width: 600px; margin: 0 auto; background-color: ' . $couleur_blanche . '; font-family: Arial, sans-serif;',
        
        // Lien aperçu navigateur
        'browser-preview-link' => 'background-color: #f4f4f4; padding: 10px 20px; text-align: center; font-size: 12px; font-family: Arial, sans-serif; border-bottom: 1px solid #e0e0e0;',
        'browser-preview-span' => 'color: #666666;',
        'browser-preview-link-a' => 'color: ' . $couleur_titrage . '; text-decoration: underline; font-weight: 600;',
        
        // Header
        'newsletter-header' => 'background-color: ' . $couleur_fond . '; padding: 30px 20px; text-align: center; color: ' . $couleur_blanche . ';',
        'newsletter-header-h1' => 'margin: 0; font-size: 28px; color: ' . $couleur_blanche . '; font-family: Arial, sans-serif;',
        
        'newsletter-header-blanc' => 'background-color: ' . $couleur_blanche . '; padding: 20px; text-align: center;',
        'newsletter-header-blanc-img' => 'max-width: 200px; height: auto; display: block; margin: 0 auto;',
        
        // Contenu
        'newsletter-content' => 'padding: 20px;',
        
        // Sections
        'section-title' => 'font-size: 22px; color: ' . $couleur_titrage . '; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid ' . $couleur_titrage . '; font-weight: 700; font-family: Arial, sans-serif;',
        'section-title-multi-day' => 'font-size: 22px; color: ' . $couleur_titrage . '; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid ' . $couleur_titrage . '; font-weight: 700; font-family: Arial, sans-serif;',
        'section-title-recurring' => 'font-size: 22px; color: ' . $couleur_titrage . '; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid ' . $couleur_titrage . '; font-weight: 700; font-family: Arial, sans-serif;',
        
        // Items
        'post-item' => 'margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0;',
        
        // Meta
        'post-meta' => 'font-size: 12px; color: #999999; margin-bottom: 10px; font-weight: 600; font-family: Arial, sans-serif;',
        'event-meta' => 'font-size: 13px; color: ' . $couleur_texte . '; margin-bottom: 8px; font-weight: 500; font-family: Arial, sans-serif;',
        
        // Badges
        'event-type-badge-multi-day' => 'display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; margin-left: 8px; background-color: #fcc8d1; color: ' . $couleur_titrage . ';',
        'event-type-badge-recurring' => 'display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; margin-left: 8px; background-color: #dce1fc; color: ' . $couleur_titrage . ';',
        
        // Titres
        'post-title' => 'font-size: 18px; font-weight: bold; margin-bottom: 10px; font-family: Arial, sans-serif;',
        'post-title-a' => 'color: ' . $couleur_titrage . '; text-decoration: none;',
        'post-title-a-multi-day' => 'color: ' . $couleur_titrage . '; text-decoration: none;',
        'post-title-a-recurring' => 'color: ' . $couleur_titrage . '; text-decoration: none;',
        
        // Images
        'post-image' => 'margin-bottom: 15px;',
        'post-image-img' => 'max-width: 100%; height: auto; border-radius: 3px; display: block;',
        
        // Extraits
        'post-excerpt' => 'color: ' . $couleur_texte . '; font-size: 14px; line-height: 1.6; margin-bottom: 10px; font-family: Arial, sans-serif;',
        
        // Boutons
        'button-wrapper' => 'border-radius: 3px; background-color: ' . $couleur_lien . ';',
        'button-wrapper-multi-day' => 'border-radius: 3px; background-color: ' . $couleur_titrage . ';',
        'button-wrapper-recurring' => 'border-radius: 3px; background-color: ' . $couleur_titrage . ';',
        
        'read-more' => 'display: inline-block; padding: 8px 16px; background-color: ' . $couleur_lien . '; color: ' . $couleur_blanche . '; text-decoration: none; border-radius: 3px; font-size: 14px; font-weight: 600; font-family: Arial, sans-serif;',
        'read-more-multi-day' => 'display: inline-block; padding: 8px 16px; background-color: ' . $couleur_titrage . '; color: ' . $couleur_blanche . '; text-decoration: none; border-radius: 3px; font-size: 14px; font-weight: 600; font-family: Arial, sans-serif;',
        'read-more-recurring' => 'display: inline-block; padding: 8px 16px; background-color: ' . $couleur_titrage . '; color: ' . $couleur_blanche . '; text-decoration: none; border-radius: 3px; font-size: 14px; font-weight: 600; font-family: Arial, sans-serif;',
        
        // Blocs personnalisés
        'custom-text-block' => 'margin-bottom: 20px; line-height: 1.6; font-family: Arial, sans-serif; color: ' . $couleur_texte . ';',
        
        'custom-image-block' => 'margin-bottom: 20px; text-align: center;',
        'custom-image-block-img' => 'height: auto; display: inline-block; border-radius: 3px;',
        
        'custom-button-block' => 'margin-bottom: 20px;',
        'custom-button-block-left' => 'margin-bottom: 20px; text-align: left;',
        'custom-button-block-center' => 'margin-bottom: 20px; text-align: center;',
        'custom-button-block-right' => 'margin-bottom: 20px; text-align: right;',
        'custom-button-block-a' => 'display: inline-block; padding: 12px 24px; background-color: ' . $couleur_lien . '; color: ' . $couleur_blanche . '; text-decoration: none; border-radius: 4px; font-weight: 600; font-family: Arial, sans-serif;',
        
        // Footer
        'newsletter-footer' => 'background-color: ' . $couleur_fond2 . '; color: ' . $couleur_blanche . '; padding: 20px; text-align: center; font-size: 12px; font-family: Arial, sans-serif;',
        'newsletter-footer-p' => 'margin: 10px 0; color: ' . $couleur_blanche . ';',
        'newsletter-footer-a' => 'color: ' . $couleur_lien . '; text-decoration: none;',
        
        // Divers
        'no-content' => 'color: #999999; font-style: italic; padding: 20px 0; text-align: center; font-family: Arial, sans-serif;',
    );
}

/**
 * Récupère le style inline pour une classe donnée
 */
function get_inline_style($class_name) {
    static $styles = null;
    
    if ($styles === null) {
        $styles = brevo_get_newsletter_styles();
    }
    
    return isset($styles[$class_name]) ? $styles[$class_name] : '';
}

/**
 * Shortcut pour générer un attribut style
 */
function style($class_name) {
    $inline = get_inline_style($class_name);
    return $inline ? ' style="' . esc_attr($inline) . '"' : '';
}

/**
 * Fonction de debug pour afficher les couleurs détectées
 * Utile pour vérifier que le parsing fonctionne correctement
 */
function brevo_debug_theme_colors() {
    $colors = brevo_parse_theme_colors();
    echo '<pre style="background: #f5f5f5; padding: 20px; margin: 20px; border: 1px solid #ddd;">';
    echo '<strong>Couleurs détectées depuis _theme-colors.scss :</strong>' . "\n\n";
    foreach ($colors as $name => $value) {
        echo sprintf(
            '<span style="background: %s; display: inline-block; width: 30px; height: 20px; border: 1px solid #333; margin-right: 10px; vertical-align: middle;"></span> $%s: %s' . "\n",
            esc_attr($value),
            esc_html($name),
            esc_html($value)
        );
    }
    echo '</pre>';
}

// Pour débugger, décommentez cette ligne dans votre admin WordPress
// add_action('admin_notices', 'brevo_debug_theme_colors');