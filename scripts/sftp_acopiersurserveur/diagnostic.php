<?php
/**
 * Script de diagnostic WordPress
 * Uploadez ce fichier à la racine de WordPress et accédez-y via navigateur
 */

// Chargement de WordPress
require_once('wp-config.php');
require_once('wp-load.php');

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Diagnostic WordPress</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0073aa; color: white; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border-left: 3px solid #0073aa; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic WordPress</h1>";

// ============================================================
// 1. CONFIGURATION
// ============================================================
echo "<div class='section'>
    <h2>1️⃣ Configuration</h2>
    <table>
        <tr><th>Paramètre</th><th>Valeur</th></tr>
        <tr><td>Site URL</td><td>" . get_option('siteurl') . "</td></tr>
        <tr><td>Home URL</td><td>" . get_option('home') . "</td></tr>
        <tr><td>WordPress Version</td><td>" . get_bloginfo('version') . "</td></tr>
        <tr><td>Préfixe tables</td><td>" . $GLOBALS['wpdb']->prefix . "</td></tr>
        <tr><td>Nom base de données</td><td>" . DB_NAME . "</td></tr>
    </table>
</div>";

// ============================================================
// 2. POSTS
// ============================================================
global $wpdb;

$post_counts = $wpdb->get_results("
    SELECT post_type, post_status, COUNT(*) as count 
    FROM {$wpdb->posts} 
    GROUP BY post_type, post_status
    ORDER BY post_type, post_status
");

echo "<div class='section'>
    <h2>2️⃣ Comptage des Posts</h2>
    <table>
        <tr><th>Type</th><th>Statut</th><th>Nombre</th></tr>";

foreach ($post_counts as $row) {
    $class = ($row->post_status === 'publish') ? 'success' : '';
    echo "<tr class='$class'>
        <td>{$row->post_type}</td>
        <td>{$row->post_status}</td>
        <td><strong>{$row->count}</strong></td>
    </tr>";
}

echo "</table></div>";

// ============================================================
// 3. DERNIERS POSTS PUBLIÉS
// ============================================================
$recent_posts = $wpdb->get_results("
    SELECT ID, post_title, post_name, post_status, post_date, post_type
    FROM {$wpdb->posts}
    WHERE post_type = 'post' AND post_status = 'publish'
    ORDER BY post_date DESC
    LIMIT 10
");

echo "<div class='section'>
    <h2>3️⃣ Derniers Posts Publiés</h2>";

if (empty($recent_posts)) {
    echo "<p class='error'>❌ Aucun post publié trouvé !</p>";
} else {
    echo "<table>
        <tr><th>ID</th><th>Titre</th><th>Slug</th><th>Date</th><th>Lien</th></tr>";
    
    foreach ($recent_posts as $post) {
        $permalink = get_permalink($post->ID);
        echo "<tr>
            <td>{$post->ID}</td>
            <td>{$post->post_title}</td>
            <td>{$post->post_name}</td>
            <td>{$post->post_date}</td>
            <td><a href='{$permalink}' target='_blank'>Voir</a></td>
        </tr>";
    }
    
    echo "</table>";
}

echo "</div>";

// ============================================================
// 4. VÉRIFICATION URLs LOCALES
// ============================================================
$local_urls = $wpdb->get_results("
    SELECT ID, post_title, post_content
    FROM {$wpdb->posts}
    WHERE post_content LIKE '%localhost%' OR post_content LIKE '%127.0.0.1%'
    LIMIT 5
");

echo "<div class='section'>
    <h2>4️⃣ URLs Locales Détectées</h2>";

if (empty($local_urls)) {
    echo "<p class='success'>✅ Aucune URL locale trouvée dans les posts</p>";
} else {
    echo "<p class='warning'>⚠️ Des URLs locales ont été trouvées :</p>
    <table>
        <tr><th>ID</th><th>Titre</th><th>Extrait</th></tr>";
    
    foreach ($local_urls as $post) {
        $excerpt = substr($post->post_content, 0, 200) . '...';
        echo "<tr>
            <td>{$post->ID}</td>
            <td>{$post->post_title}</td>
            <td><pre>" . htmlspecialchars($excerpt) . "</pre></td>
        </tr>";
    }
    
    echo "</table>";
}

echo "</div>";

// ============================================================
// 5. PERMALIENS
// ============================================================
$permalink_structure = get_option('permalink_structure');

echo "<div class='section'>
    <h2>5️⃣ Structure des Permaliens</h2>
    <p><strong>Structure actuelle :</strong> " . 
    ($permalink_structure ? $permalink_structure : "<span class='warning'>Par défaut (pas bien !)</span>") . 
    "</p>";

if (empty($permalink_structure)) {
    echo "<p class='warning'>⚠️ Les permaliens utilisent la structure par défaut (?p=123). 
    Allez dans <strong>Réglages → Permaliens</strong> pour changer.</p>";
}

echo "</div>";

// ============================================================
// 6. THÈME ACTIF
// ============================================================
$theme = wp_get_theme();

echo "<div class='section'>
    <h2>6️⃣ Thème Actif</h2>
    <table>
        <tr><th>Paramètre</th><th>Valeur</th></tr>
        <tr><td>Nom</td><td>{$theme->get('Name')}</td></tr>
        <tr><td>Version</td><td>{$theme->get('Version')}</td></tr>
        <tr><td>Dossier</td><td>{$theme->get_stylesheet()}</td></tr>
    </table>
</div>";

// ============================================================
// 7. PLUGINS ACTIFS
// ============================================================
$active_plugins = get_option('active_plugins');

echo "<div class='section'>
    <h2>7️⃣ Plugins Actifs</h2>
    <ul>";

if (empty($active_plugins)) {
    echo "<li class='warning'>Aucun plugin actif</li>";
} else {
    foreach ($active_plugins as $plugin) {
        echo "<li>{$plugin}</li>";
    }
}

echo "</ul></div>";

// ============================================================
// 8. REQUÊTE WP_QUERY TEST
// ============================================================
$test_query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 5
]);

echo "<div class='section'>
    <h2>8️⃣ Test WP_Query</h2>
    <p><strong>Posts trouvés :</strong> " . $test_query->found_posts . "</p>";

if ($test_query->have_posts()) {
    echo "<ul>";
    while ($test_query->have_posts()) {
        $test_query->the_post();
        echo "<li><strong>" . get_the_title() . "</strong> - " . get_permalink() . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='error'>❌ WP_Query ne retourne aucun post !</p>";
}

wp_reset_postdata();

echo "</div>";

echo "<div class='section'>
    <h2>✅ Diagnostic terminé</h2>
    <p><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier après utilisation pour des raisons de sécurité !</p>
</div>

</body>
</html>";