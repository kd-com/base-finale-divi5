<?php
/**
 * Template Newsletter Personnalisée - VERSION AVEC INLINE STYLES
 * Les styles sont générés dynamiquement en PHP pour être inline dans le HTML
 * Compatible avec tous les clients email
 */
function brevo_generate_custom_newsletter_html($subject, $preview_text, $blocks) {
    // Charger les styles inline
    require_once(BREVO_NEWSLETTER_DIR . '/newsletter-styles-inline.php');
    
    $logo_url = ( $user_logo = et_get_option('divi_logo')) && ! empty($user_logo) ? $user_logo : get_bloginfo('stylesheet_directory') .'/img/logo_admin.png';
    
    // Convertir en URL absolue si nécessaire
    if (strpos($logo_url, 'http') !== 0) {
        $logo_url = site_url($logo_url);
    }
    
    $site_name = get_bloginfo('name');
    $current_year = date('Y');
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo esc_html($subject); ?></title>
        <!--[if mso]>
        <style type="text/css">
            body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
        </style>
        <![endif]-->
    </head>
    <body<?php echo style('body'); ?>>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('email-wrapper'); ?>>
            <tr>
                <td<?php echo style('email-wrapper-td'); ?>>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="600"<?php echo style('email-container'); ?>>
                        
                        <!-- Lien aperçu navigateur -->
                        <tr>
                            <td<?php echo style('browser-preview-link'); ?>>
                                <span<?php echo style('browser-preview-span'); ?>>
                                    Problème d'affichage ? 
                                    <a href="{{ mirror }}"<?php echo style('browser-preview-link-a'); ?>>
                                        Voir cette newsletter dans votre navigateur
                                    </a>
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Header avec logo -->
                        <tr>
                            <td<?php echo style('newsletter-header-blanc'); ?>>
                                <img src="<?php echo esc_attr($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>"<?php echo style('newsletter-header-blanc-img'); ?> />
                            </td>
                        </tr>
                        
                        <!-- Contenu -->
                        <tr>
                            <td<?php echo style('newsletter-content'); ?>>
                                <?php
                                if (!empty($blocks)) {
                                    foreach ($blocks as $block) {
                                        switch ($block['type']) {
                                            case 'text':
                                                $content = isset($block['content']) ? $block['content'] : '';
                                                $allowed_tags = '<p><br><strong><b><em><i><a><ul><ol><li><h1><h2><h3><h4>';
                                                $content = strip_tags($content, $allowed_tags);
                                                echo '<div' . style('custom-text-block') . '>' . $content . '</div>';
                                                break;
                                            
                                            case 'image':
                                                if (!empty($block['image_url'])) {
                                                    $width = isset($block['image_width']) ? intval($block['image_width']) : 100;
                                                    $image_url = $block['image_url'];
                                                    
                                                    // Convertir en URL absolue si nécessaire
                                                    if (strpos($image_url, 'http') !== 0) {
                                                        $image_url = site_url($image_url);
                                                    }
                                                    
                                                    echo '<div' . style('custom-image-block') . '>';
                                                    if (!empty($block['image_link'])) {
                                                        echo '<a href="' . esc_url($block['image_link']) . '">';
                                                    }
                                                    $img_style = get_inline_style('custom-image-block-img') . ' max-width: ' . $width . '%;';
                                                    echo '<img src="' . esc_attr($image_url) . '" style="' . esc_attr($img_style) . '">';
                                                    if (!empty($block['image_link'])) {
                                                        echo '</a>';
                                                    }
                                                    echo '</div>';
                                                }
                                                break;
                                            
                                            case 'button':
                                                if (!empty($block['button_text']) && !empty($block['button_url'])) {
                                                    $align = isset($block['button_align']) ? $block['button_align'] : 'center';
                                                    echo '<div' . style('custom-button-block-' . $align) . '>';
                                                    echo '<a href="' . esc_url($block['button_url']) . '"' . style('custom-button-block-a') . '>';
                                                    echo esc_html($block['button_text']);
                                                    echo '</a>';
                                                    echo '</div>';
                                                }
                                                break;
                                            
                                            case 'spacer':
                                                $height = isset($block['spacer_height']) ? intval($block['spacer_height']) : 20;
                                                echo '<div style="height: ' . $height . 'px;"></div>';
                                                break;
                                        }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td<?php echo style('newsletter-footer'); ?>>
                                <p<?php echo style('newsletter-footer-p'); ?>>
                                    Vous recevez cet email car vous êtes abonné à notre newsletter.
                                </p>
                                <p<?php echo style('newsletter-footer-p'); ?>>
                                    <a href="<?php echo home_url('/?brevo_unsubscribe=1&email={{contact.EMAIL}}'); ?>"<?php echo style('newsletter-footer-a'); ?>>Se désabonner</a> | 
                                    <a href="<?php echo home_url('/'); ?>"<?php echo style('newsletter-footer-a'); ?>>Voir le site web</a>
                                </p>
                                <p<?php echo style('newsletter-footer-p'); ?>>
                                    &copy; <?php echo esc_html($current_year); ?> <?php echo esc_html($site_name); ?> - Tous droits réservés
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    
    return ob_get_clean();
}