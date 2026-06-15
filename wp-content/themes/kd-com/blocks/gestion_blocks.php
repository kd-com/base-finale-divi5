<?php
// Inclusion dynamique et robuste des blocs ACF
// Inclusion des fichiers de déclaration des blocs actifs AVANT le hook acf/init
$blocks_dir = __DIR__ . '/blocks_declaration';
$acf_fields_dir = __DIR__ . '/acf_fields';
$blocks_actifs = get_option('acf_blocks_actifs', array());
foreach (glob($blocks_dir . '/*_block.php') as $block_file) {
    $block_name = basename($block_file, '_block.php');
    if (in_array($block_name, (array)$blocks_actifs)) {
        // Inclusion du fichier de déclaration du bloc
        include_once $block_file;
        // Inclusion du fichier de champs ACF associé
        $acf_file = $acf_fields_dir . '/acf-' . str_replace('_', '-', $block_name) . '.php';
        if (file_exists($acf_file)) {
            include_once $acf_file;
        }
    }
}
// Inclusion du groupe de champs Animation (AOS)
if (file_exists($acf_fields_dir . '/acf-animation.php')) {
    include_once $acf_fields_dir . '/acf-animation.php';
}