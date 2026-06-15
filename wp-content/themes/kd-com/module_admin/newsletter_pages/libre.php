<?php
/**
 * ========================================
 * PAGE NEWSLETTER LIBRE - VERSION AMÉLIORÉE
 * ========================================
 */

// Enqueue media uploader et scripts
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'newsletter_page_brevo-newsletter-libre') {
        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script('jquery-ui-sortable');
    }
});

function brevo_newsletter_libre_page() {
    global $wpdb;
    
    // Action : Sauvegarder ou envoyer
    if (isset($_POST['save_newsletter_draft']) && check_admin_referer('brevo_newsletter_libre')) {
        $newsletter_id = isset($_POST['newsletter_id']) ? intval($_POST['newsletter_id']) : 0;
        
        $newsletter_data = array(
            'subject' => sanitize_text_field($_POST['newsletter_subject']),
            'preview_text' => sanitize_text_field($_POST['newsletter_preview']),
            'blocks' => isset($_POST['newsletter_blocks']) ? $_POST['newsletter_blocks'] : array(),
            'status' => 'draft'
        );
        
        if ($newsletter_id > 0) {
            // Mise à jour
            update_option('brevo_custom_newsletter_' . $newsletter_id, $newsletter_data);
            echo '<div class="notice notice-success"><p>✅ Brouillon sauvegardé avec succès !</p></div>';
        } else {
            // Création
            $newsletter_id = brevo_create_newsletter_draft($newsletter_data);
            echo '<div class="notice notice-success"><p>✅ Brouillon créé avec succès ! <a href="?page=brevo-newsletter-libre&edit=' . $newsletter_id . '">Modifier</a></p></div>';
        }
    }
    
    // Action : Envoyer
    if (isset($_POST['send_custom_newsletter']) && check_admin_referer('brevo_newsletter_libre')) {
        $newsletter_id = isset($_POST['newsletter_id']) ? intval($_POST['newsletter_id']) : 0;
        $subject = sanitize_text_field($_POST['newsletter_subject']);
        $preview_text = sanitize_text_field($_POST['newsletter_preview']);
        $blocks = isset($_POST['newsletter_blocks']) ? $_POST['newsletter_blocks'] : array();
        
        $send_mode = sanitize_text_field($_POST['send_mode']);
        $test_email = sanitize_email($_POST['test_email']);
        
        // Sauvegarder avant envoi
        $newsletter_data = array(
            'subject' => $subject,
            'preview_text' => $preview_text,
            'blocks' => $blocks,
            'status' => 'draft'
        );
        
        if ($newsletter_id > 0) {
            update_option('brevo_custom_newsletter_' . $newsletter_id, $newsletter_data);
        } else {
            $newsletter_id = brevo_create_newsletter_draft($newsletter_data);
        }
        
        // Générer le HTML
        $html = brevo_generate_custom_newsletter_html($subject, $preview_text, $blocks);
        
        // Envoyer
        $result = brevo_send_custom_newsletter($html, $subject, $send_mode === 'test', $test_email, $newsletter_id);
        
        if ($result['success']) {
            if ($send_mode !== 'test') {
                // Marquer comme envoyée
                $newsletter_data['status'] = 'sent';
                $newsletter_data['sent_date'] = current_time('mysql');
                update_option('brevo_custom_newsletter_' . $newsletter_id, $newsletter_data);
            }
            echo '<div class="notice notice-success"><p>✅ ' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ ' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    // Action : Supprimer
    if (isset($_GET['delete']) && check_admin_referer('delete_newsletter_' . $_GET['delete'])) {
        $newsletter_id = intval($_GET['delete']);
        delete_option('brevo_custom_newsletter_' . $newsletter_id);
        echo '<div class="notice notice-success"><p>✅ Newsletter supprimée !</p></div>';
    }
    
    $config = get_brevo_config();
    $api_configured = is_brevo_configured();
    
    // Mode édition
    $edit_mode = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
    $newsletter_data = null;
    
    if ($edit_mode > 0) {
        $newsletter_data = get_option('brevo_custom_newsletter_' . $edit_mode, null);
    }
    
    // Afficher la liste ou le formulaire
    $show_list = isset($_GET['action']) && $_GET['action'] === 'list';
    
    ?>
    <div class="wrap">
        <h1>
            ✉️ Newsletters personnalisées
            <?php if (!$show_list) : ?>
                <a href="?page=brevo-newsletter-libre&action=list" class="page-title-action">📋 Voir la liste</a>
            <?php else : ?>
                <a href="?page=brevo-newsletter-libre" class="page-title-action">➕ Créer une newsletter</a>
            <?php endif; ?>
        </h1>
        
        <?php if (!$api_configured) : ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Configuration requise</strong></p>
                <p>Veuillez d'abord <a href="<?php echo admin_url('admin.php?page=brevo-newsletter-config'); ?>">configurer votre API Brevo</a></p>
            </div>
            <?php return; ?>
        <?php endif; ?>
        
        <?php if ($show_list) : ?>
            <!-- LISTE DES NEWSLETTERS -->
            <?php brevo_display_newsletters_list(); ?>
        <?php else : ?>
            <!-- FORMULAIRE DE CRÉATION/ÉDITION -->
            <?php brevo_display_newsletter_form($newsletter_data, $edit_mode); ?>
        <?php endif; ?>
        
    </div>
    <?php
}

/**
 * Afficher la liste des newsletters
 */
function brevo_display_newsletters_list() {
    global $wpdb;
    
    // Récupérer toutes les newsletters personnalisées
    $custom_newsletters = brevo_get_all_custom_newsletters();
    
    ?>
    <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
        <h2>📋 Toutes les newsletters</h2>
        
        <?php if (!empty($custom_newsletters)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 40%;">Sujet</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($custom_newsletters as $newsletter) : ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($newsletter['subject']); ?></strong>
                        <?php if (!empty($newsletter['preview_text'])) : ?>
                        <div style="font-size: 12px; color: #666;">
                            <?php echo esc_html(substr($newsletter['preview_text'], 0, 80)); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>Newsletter libre</td>
                    <td>
                        <?php if ($newsletter['status'] === 'sent') : ?>
                            <span style="color: #28a745;">✅ Envoyée</span>
                        <?php else : ?>
                            <span style="color: #6c757d;">📝 Brouillon</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($newsletter['sent_date'])) {
                            echo date_i18n('j M Y à H:i', strtotime($newsletter['sent_date']));
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="?page=brevo-newsletter-libre&edit=<?php echo $newsletter['id']; ?>" 
                           class="button button-small">
                            ✏️ Modifier
                        </a>
                        <?php if ($newsletter['status'] === 'sent') : ?>
                        <a href="?page=brevo-newsletter-libre&duplicate=<?php echo $newsletter['id']; ?>" 
                           class="button button-small">
                            📋 Dupliquer
                        </a>
                        <?php endif; ?>
                        <a href="?page=brevo-newsletter-libre&action=list&delete=<?php echo $newsletter['id']; ?>&_wpnonce=<?php echo wp_create_nonce('delete_newsletter_' . $newsletter['id']); ?>" 
                           class="button button-small button-link-delete"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette newsletter ?');">
                            🗑️ Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p style="text-align: center; color: #666; padding: 40px;">
            Aucune newsletter créée pour le moment.<br>
            <a href="?page=brevo-newsletter-libre" class="button button-primary" style="margin-top: 15px;">
                ➕ Créer votre première newsletter
            </a>
        </p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Afficher le formulaire de création/édition
 */
function brevo_display_newsletter_form($newsletter_data, $edit_mode) {
    ?>
    <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
        
        <form method="post" id="custom-newsletter-form">
            <?php wp_nonce_field('brevo_newsletter_libre'); ?>
            <input type="hidden" name="newsletter_id" value="<?php echo $edit_mode; ?>">
            
            <!-- En-tête -->
            <div style="margin-bottom: 30px;">
                <h2 style="margin-top: 0;">📝 Informations générales</h2>
                
                <table class="form-table">
                    <tr>
                        <th><label for="newsletter_subject">Objet de l'email *</label></th>
                        <td>
                            <input type="text" 
                                   id="newsletter_subject" 
                                   name="newsletter_subject" 
                                   class="widefat" 
                                   required
                                   value="<?php echo $newsletter_data ? esc_attr($newsletter_data['subject']) : ''; ?>"
                                   placeholder="Ex: Offre spéciale - 20% de réduction"
                                   style="max-width: 600px;">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="newsletter_preview">Texte de prévisualisation</label></th>
                        <td>
                            <input type="text" 
                                   id="newsletter_preview" 
                                   name="newsletter_preview" 
                                   class="widefat"
                                   value="<?php echo $newsletter_data ? esc_attr($newsletter_data['preview_text']) : ''; ?>"
                                   placeholder="Texte affiché dans la boîte de réception (optionnel)"
                                   style="max-width: 600px;">
                            <p class="description">Ce texte apparaît après l'objet dans les clients email</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Constructeur de blocs -->
            <div style="margin-bottom: 30px;">
                <h2>🎨 Contenu de la newsletter</h2>
                
                <div id="newsletter-blocks-container" style="margin: 20px 0;">
                    <!-- Les blocs seront ajoutés ici dynamiquement -->
                </div>
                
                <div style="margin: 20px 0;">
                    <button type="button" class="button button-secondary" onclick="addBlock('text')">
                        ➕ Ajouter un bloc texte
                    </button>
                    <button type="button" class="button button-secondary" onclick="addBlock('image')">
                        🖼️ Ajouter une image
                    </button>
                    <button type="button" class="button button-secondary" onclick="addBlock('button')">
                        🔘 Ajouter un bouton
                    </button>
                    <button type="button" class="button button-secondary" onclick="addBlock('spacer')">
                        ↕️ Ajouter un espace
                    </button>
                </div>
            </div>
            
            <!-- Sauvegarde et envoi -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button type="submit" name="save_newsletter_draft" class="button button-secondary button-large">
                        💾 Sauvegarder le brouillon
                    </button>
                    <button type="button" class="button button-secondary" onclick="showPreview()">
                        🔍 Prévisualiser
                    </button>
                </div>
                
                <h2>📤 Envoi de la newsletter</h2>
                
                <table class="form-table">
                    <tr>
                        <th><label>Mode d'envoi :</label></th>
                        <td>
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="send_mode" value="test" checked>
                                <strong>Envoi test</strong> - Envoyer à une adresse spécifique
                            </label>
                            <label style="display: block;">
                                <input type="radio" name="send_mode" value="production">
                                <strong>Envoi réel</strong> - Envoyer à tous les abonnés
                            </label>
                        </td>
                    </tr>
                    <tr class="test-email-row">
                        <th><label for="test_email">Email de test :</label></th>
                        <td>
                            <input type="email" 
                                   id="test_email" 
                                   name="test_email" 
                                   value="<?php echo esc_attr(get_option('admin_email')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="send_custom_newsletter" class="button button-primary button-large">
                        📨 Envoyer la newsletter
                    </button>
                </p>
            </div>
            
        </form>
        
        <!-- Aperçu -->
        <div id="newsletter-preview" style="display: none; margin-top: 20px; padding: 20px; background: #f5f5f5; border-radius: 4px;">
            <h3>👁️ Aperçu de la newsletter</h3>
            <div id="preview-content" style="background: white; padding: 20px;"></div>
        </div>
        
    </div>
    
    <!-- Templates de blocs -->
    <script type="text/template" id="block-template-text">
        <div class="newsletter-block" data-type="text" style="margin-bottom: 20px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong>📝 Bloc Texte</strong>
                <div>
                    <button type="button" class="button button-small" onclick="moveBlockUp(this)">↑</button>
                    <button type="button" class="button button-small" onclick="moveBlockDown(this)">↓</button>
                    <button type="button" class="button button-small button-link-delete" onclick="removeBlock(this)">✕</button>
                </div>
            </div>
            <input type="hidden" name="newsletter_blocks[{INDEX}][type]" value="text">
            <textarea name="newsletter_blocks[{INDEX}][content]" class="widefat block-textarea" rows="10" style="font-family: monospace;"></textarea>
            <p class="description">Vous pouvez utiliser du HTML basique : &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a&gt;, &lt;br&gt;</p>
        </div>
    </script>
    
    <script type="text/template" id="block-template-image">
        <div class="newsletter-block" data-type="image" style="margin-bottom: 20px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong>🖼️ Bloc Image</strong>
                <div>
                    <button type="button" class="button button-small" onclick="moveBlockUp(this)">↑</button>
                    <button type="button" class="button button-small" onclick="moveBlockDown(this)">↓</button>
                    <button type="button" class="button button-small button-link-delete" onclick="removeBlock(this)">✕</button>
                </div>
            </div>
            <div class="image-preview" style="margin-bottom: 10px;">
                <img src="" style="max-width: 100%; display: none;">
            </div>
            <input type="hidden" name="newsletter_blocks[{INDEX}][type]" value="image">
            <input type="hidden" name="newsletter_blocks[{INDEX}][image_url]" class="image-url">
            <button type="button" class="button upload-image-button">Choisir une image</button>
            <button type="button" class="button remove-image-button" style="display: none;">Supprimer l'image</button>
            <p style="margin-top: 10px;">
                <label>
                    Largeur : 
                    <select name="newsletter_blocks[{INDEX}][image_width]">
                        <option value="100">100% (pleine largeur)</option>
                        <option value="75">75%</option>
                        <option value="50">50%</option>
                        <option value="33">33%</option>
                    </select>
                </label>
            </p>
            <p>
                <label>
                    Lien (optionnel) : 
                    <input type="url" name="newsletter_blocks[{INDEX}][image_link]" class="regular-text" placeholder="https://">
                </label>
            </p>
        </div>
    </script>
    
    <script type="text/template" id="block-template-button">
        <div class="newsletter-block" data-type="button" style="margin-bottom: 20px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong>🔘 Bloc Bouton</strong>
                <div>
                    <button type="button" class="button button-small" onclick="moveBlockUp(this)">↑</button>
                    <button type="button" class="button button-small" onclick="moveBlockDown(this)">↓</button>
                    <button type="button" class="button button-small button-link-delete" onclick="removeBlock(this)">✕</button>
                </div>
            </div>
            <input type="hidden" name="newsletter_blocks[{INDEX}][type]" value="button">
            <p>
                <label>
                    Texte du bouton * : 
                    <input type="text" name="newsletter_blocks[{INDEX}][button_text]" class="regular-text" required placeholder="Ex: Voir l'offre">
                </label>
            </p>
            <p>
                <label>
                    URL * : 
                    <input type="url" name="newsletter_blocks[{INDEX}][button_url]" class="regular-text" required placeholder="https://">
                </label>
            </p>
            <p>
                <label>
                    Alignement : 
                    <select name="newsletter_blocks[{INDEX}][button_align]">
                        <option value="left">Gauche</option>
                        <option value="center" selected>Centre</option>
                        <option value="right">Droite</option>
                    </select>
                </label>
            </p>
        </div>
    </script>
    
    <script type="text/template" id="block-template-spacer">
        <div class="newsletter-block" data-type="spacer" style="margin-bottom: 20px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong>↕️ Espace</strong>
                <div>
                    <button type="button" class="button button-small" onclick="moveBlockUp(this)">↑</button>
                    <button type="button" class="button button-small" onclick="moveBlockDown(this)">↓</button>
                    <button type="button" class="button button-small button-link-delete" onclick="removeBlock(this)">✕</button>
                </div>
            </div>
            <input type="hidden" name="newsletter_blocks[{INDEX}][type]" value="spacer">
            <p>
                <label>
                    Hauteur : 
                    <select name="newsletter_blocks[{INDEX}][spacer_height]">
                        <option value="10">Petit (10px)</option>
                        <option value="20" selected>Moyen (20px)</option>
                        <option value="40">Grand (40px)</option>
                        <option value="60">Très grand (60px)</option>
                    </select>
                </label>
            </p>
        </div>
    </script>
    
    <script>
    jQuery(document).ready(function($) {
        let blockIndex = 0;
        
        // Gestion de l'affichage du champ email test
        $('input[name="send_mode"]').on('change', function() {
            if ($(this).val() === 'test') {
                $('.test-email-row').show();
            } else {
                $('.test-email-row').hide();
            }
        });
        
        // Fonction pour ajouter un bloc
        window.addBlock = function(type) {
            const template = $('#block-template-' + type).html();
            const html = template.replace(/{INDEX}/g, blockIndex);
            $('#newsletter-blocks-container').append(html);
            blockIndex++;
        };
        
        // Fonction pour supprimer un bloc
        window.removeBlock = function(btn) {
            if (confirm('Supprimer ce bloc ?')) {
                $(btn).closest('.newsletter-block').remove();
            }
        };
        
        // Fonction pour déplacer un bloc vers le haut
        window.moveBlockUp = function(btn) {
            const block = $(btn).closest('.newsletter-block');
            const prev = block.prev('.newsletter-block');
            if (prev.length) {
                block.insertBefore(prev);
            }
        };
        
        // Fonction pour déplacer un bloc vers le bas
        window.moveBlockDown = function(btn) {
            const block = $(btn).closest('.newsletter-block');
            const next = block.next('.newsletter-block');
            if (next.length) {
                block.insertAfter(next);
            }
        };
        
        // Gestion de l'upload d'image
        $(document).on('click', '.upload-image-button', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const block = button.closest('.newsletter-block');
            const frame = wp.media({
                title: 'Choisir une image',
                button: { text: 'Utiliser cette image' },
                multiple: false
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                block.find('.image-url').val(attachment.url);
                block.find('.image-preview img').attr('src', attachment.url).show();
                block.find('.remove-image-button').show();
            });
            
            frame.open();
        });
        
        // Supprimer une image
        $(document).on('click', '.remove-image-button', function(e) {
            e.preventDefault();
            const block = $(this).closest('.newsletter-block');
            block.find('.image-url').val('');
            block.find('.image-preview img').attr('src', '').hide();
            $(this).hide();
        });
        
        // Prévisualisation
        window.showPreview = function() {
            const subject = $('#newsletter_subject').val();
            const preview_text = $('#newsletter_preview').val();
            const blocks = [];
            
            $('.newsletter-block').each(function() {
                const type = $(this).data('type');
                const block = { type: type };
                
                if (type === 'text') {
                    block.content = $(this).find('textarea').val();
                } else if (type === 'image') {
                    block.image_url = $(this).find('.image-url').val();
                    block.image_width = $(this).find('select[name*="image_width"]').val();
                    block.image_link = $(this).find('input[name*="image_link"]').val();
                } else if (type === 'button') {
                    block.button_text = $(this).find('input[name*="button_text"]').val();
                    block.button_url = $(this).find('input[name*="button_url"]').val();
                    block.button_align = $(this).find('select[name*="button_align"]').val();
                } else if (type === 'spacer') {
                    block.spacer_height = $(this).find('select[name*="spacer_height"]').val();
                }
                
                blocks.push(block);
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'brevo_preview_newsletter',
                    subject: subject,
                    preview_text: preview_text,
                    blocks: blocks,
                    _wpnonce: '<?php echo wp_create_nonce('brevo_preview_newsletter'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#preview-content').html(response.data.html);
                        $('#newsletter-preview').slideDown();
                        $('html, body').animate({
                            scrollTop: $('#newsletter-preview').offset().top - 100
                        }, 500);
                    }
                }
            });
        };
        
        // Charger les blocs existants si mode édition
        <?php if ($newsletter_data && !empty($newsletter_data['blocks'])) : ?>
        const existingBlocks = <?php echo json_encode($newsletter_data['blocks']); ?>;
        
        $.each(existingBlocks, function(index, block) {
            addBlock(block.type);
            
            const lastBlock = $('.newsletter-block').last();
            
            if (block.type === 'text') {
                lastBlock.find('textarea').val(block.content || '');
            } else if (block.type === 'image') {
                if (block.image_url) {
                    lastBlock.find('.image-url').val(block.image_url);
                    lastBlock.find('.image-preview img').attr('src', block.image_url).show();
                    lastBlock.find('.remove-image-button').show();
                }
                if (block.image_width) {
                    lastBlock.find('select[name*="image_width"]').val(block.image_width);
                }
                if (block.image_link) {
                    lastBlock.find('input[name*="image_link"]').val(block.image_link);
                }
            } else if (block.type === 'button') {
                lastBlock.find('input[name*="button_text"]').val(block.button_text || '');
                lastBlock.find('input[name*="button_url"]').val(block.button_url || '');
                lastBlock.find('select[name*="button_align"]').val(block.button_align || 'center');
            } else if (block.type === 'spacer') {
                lastBlock.find('select[name*="spacer_height"]').val(block.spacer_height || '20');
            }
        });
        <?php endif; ?>
    });
    </script>
    
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            border-radius: 4px;
        }
        .newsletter-block {
            position: relative;
        }
        .block-textarea {
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
    <?php
}

/**
 * Créer un brouillon de newsletter
 */
function brevo_create_newsletter_draft($data) {
    global $wpdb;
    
    // Trouver le prochain ID disponible
    $max_id = 0;
    for ($i = 1; $i <= 1000; $i++) {
        if (!get_option('brevo_custom_newsletter_' . $i)) {
            $max_id = $i;
            break;
        }
    }
    
    if ($max_id > 0) {
        add_option('brevo_custom_newsletter_' . $max_id, $data);
        return $max_id;
    }
    
    return 0;
}

/**
 * Récupérer toutes les newsletters personnalisées
 */
function brevo_get_all_custom_newsletters() {
    global $wpdb;
    
    $newsletters = array();
    
    // Récupérer toutes les options qui commencent par brevo_custom_newsletter_
    $results = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} 
         WHERE option_name LIKE 'brevo_custom_newsletter_%' 
         ORDER BY option_name DESC"
    );
    
    foreach ($results as $result) {
        $id = str_replace('brevo_custom_newsletter_', '', $result->option_name);
        $data = maybe_unserialize($result->option_value);
        
        if (is_array($data)) {
            $data['id'] = $id;
            $newsletters[] = $data;
        }
    }
    
    return $newsletters;
}

include get_stylesheet_directory() . '/newsletter/template-email-free.php';

/**
 * Envoyer une newsletter personnalisée
 */
function brevo_send_custom_newsletter($html, $subject, $is_test = false, $test_email = '', $newsletter_id = 0) {
    $config = get_brevo_config();
    
    if ($is_test && empty($test_email)) {
        return array('success' => false, 'message' => 'Email de test requis');
    }
    
    $url = $is_test ? 'https://api.brevo.com/v3/smtp/email' : 'https://api.brevo.com/v3/emailCampaigns';
    
    $campaign_data = array(
        'name' => $subject . ' - ' . date('Y-m-d H:i'),
        'subject' => ($is_test ? '[TEST] ' : '') . $subject,
        'sender' => array(
            'name' => $config['sender_name'],
            'email' => $config['sender_email']
        ),
        'htmlContent' => $html,
    );
    
    if ($is_test) {
        $campaign_data['to'] = array(array('email' => $test_email));
    } else {
        $campaign_data['recipients'] = array('listIds' => array(intval($config['list_id'])));
        $campaign_data['scheduledAt'] = date('c', strtotime('+2 minutes'));
    }
    
    $response = wp_remote_post($url, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key'],
            'content-type' => 'application/json'
        ),
        'body' => json_encode($campaign_data),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return array('success' => false, 'message' => 'Erreur : ' . $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code >= 200 && $status_code < 300) {
        return array(
            'success' => true,
            'message' => $is_test ? 'Email de test envoyé !' : 'Newsletter programmée avec succès !'
        );
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return array(
            'success' => false,
            'message' => 'Erreur API : ' . ($body['message'] ?? 'Erreur inconnue')
        );
    }
}

// AJAX pour la prévisualisation
add_action('wp_ajax_brevo_preview_newsletter', function() {
    check_ajax_referer('brevo_preview_newsletter');
    
    $subject = sanitize_text_field($_POST['subject']);
    $preview_text = sanitize_text_field($_POST['preview_text']);
    $blocks = isset($_POST['blocks']) ? $_POST['blocks'] : array();
    
    $html = brevo_generate_custom_newsletter_html($subject, $preview_text, $blocks);
    
    wp_send_json_success(array('html' => $html));
});