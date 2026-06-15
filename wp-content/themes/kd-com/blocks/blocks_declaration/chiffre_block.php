<?php

// Déclarer un bloc Gutenberg chiffre cle
function kd_chiffre_acf_block_types() {
  acf_register_block_type( array(
    'name'              => 'chiffre',
    'title'             => 'Chiffre clé',
    'description'       => "Chiffres clés",
    'render_template'   => get_stylesheet_directory() . '/blocks/my_block/chiffre.php',
    'category'          => 'formatting', 
    'icon'              => 'admin-plugins', 
    'supports'        => [
      'align'           => [ 'full' ],
      'jsx'             => true,
      'color'           => [
        'background' => false,
        'gradients'  => false,
        'text'       => false,
      ],
    ],
    'keywords'          => array( 'chiffres', 'extension', 'add-on' ),
    'enqueue_assets'    => function() {
      wp_enqueue_style( 
        'capitaine-blocks', 
        get_bloginfo( 'stylesheet_directory' ) . '/css/blocks.css' 
      );
    }
  ) );
}
add_action( 'acf/init', 'kd_chiffre_acf_block_types' );
// Un seul enregistrement du bloc chiffre
// error_log('chiffre_block.php chargé');

// Ajout du script pour charger dynamiquement la liste des icônes Font Awesome
add_action('acf/input/admin_footer', function() {
    ?>
    <script>
    let observer;
    let tries = 0;
    const maxTries = 5;
    let iconsLoaded = false;
    function fillFontAwesomeSelects() {
      const selects = Array.from(document.querySelectorAll('select')).filter(s => s.name && s.name.match(/field_6729f935f47e1/));
      if (!selects.length) {
        // Attendre que le champ apparaisse dans le DOM
        return;
      }
      // Si déjà rempli, ne rien faire
      if (iconsLoaded || selects.every(select => select.options.length > 1)) {
        if (observer) observer.disconnect();
        return;
      }
      // Charger les icônes une seule fois
      const url = '<?php echo get_stylesheet_directory_uri(); ?>/assets/icons_custom.json';
      console.log('FontAwesome fetch URL:', url);
      fetch(url)
        .then(response => {
          if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          tries = 0; // reset tries on success
          selects.forEach(select => {
            if (!select) return;
            if (select.options.length > 1) return; // déjà rempli
            // Ajout d'un nom personnalisé au select pour faciliter la sélection JS
            // Ajout du champ de recherche
            let search = select.previousElementSibling;
            if (!search || !(search.classList && search.classList.contains('fa-search'))) {
              search = document.createElement('input');
              search.type = 'text';
              search.placeholder = 'Rechercher une icône...';
              search.className = 'fa-search';
              search.style.marginBottom = '8px';
              search.style.width = '100%';
              select.parentNode.insertBefore(search, select);
            }
            // Fonction pour remplir le select selon le filtre
            function fillSelect(filter = '') {
              select.innerHTML = '';
              const emptyOption = document.createElement('option');
              emptyOption.value = '';
              emptyOption.textContent = '-- Choisir une icône --';
              select.appendChild(emptyOption);
              let count = 0;
              Object.keys(data).forEach(icon => {
                if (icon.toLowerCase().includes(filter.toLowerCase())) {
                  if (count < 100) {
                    const option = document.createElement('option');
                    option.value = 'fa-solid fa-' + icon;
                    option.textContent = icon;
                    select.appendChild(option);
                    count++;
                  }
                }
              });
            }
            fillSelect();
            if (search) {
              search.addEventListener('input', function() {
                fillSelect(search.value);
              });
            }
            // Ajout de l'aperçu visuel
            let preview = select.nextElementSibling;
            if (!preview || !(preview.classList && preview.classList.contains('fa-preview'))) {
              preview = document.createElement('span');
              preview.className = 'fa-preview';
              preview.style.marginLeft = '10px';
              preview.style.fontSize = '2em';
              select.parentNode.insertBefore(preview, select.nextSibling);
            }
            select.addEventListener('change', function() {
              if (preview && typeof preview.classList !== 'undefined') {
                preview.className = 'fa-preview';
                if (select.value) {
                  preview.classList.add(...select.value.split(' '));
                }
              }
            });
            // Affichage initial si déjà sélectionné
            if (preview && typeof preview.classList !== 'undefined' && select.value) {
              preview.className = 'fa-preview';
              preview.classList.add(...select.value.split(' '));
            }
          });
          iconsLoaded = true;
          if (observer) observer.disconnect(); // Stopper l'observation après remplissage
        })
        .catch((err) => {
          tries++;
          if (tries < maxTries) {
            console.warn('Erreur lors du chargement des icônes Font Awesome, tentative', tries, '/', maxTries);
            setTimeout(fillFontAwesomeSelects, 1000 * tries); // délai progressif
          } else {
            alert('Erreur lors du chargement de la liste des icônes Font Awesome après plusieurs tentatives.');
          }
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
      observer = new MutationObserver(fillFontAwesomeSelects);
      observer.observe(document.body, { childList: true, subtree: true });
      fillFontAwesomeSelects();
    });
    </script>
    <?php
});
