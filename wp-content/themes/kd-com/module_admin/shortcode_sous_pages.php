<?php
// affichage des sous pages si pas de chapeau
function show_childpages_shortcode() {

    // a shortcode should just return the content not echo html
    // so we start to create an object, and on the end we return it
    // if we dont do this the shortcode will be displayed in the top of the content
  ob_start();

    // only start if we are on a single page
  if ( is_page() ) {
        // get the ID of the current (parent) page
    $current_page_id = get_the_ID();
     // si chapeau on affiche pas ce shortcode
     if(get_field('chapeau_de_la_page')) {
     } else {

        // get all the children of the current page
    $child_pages = array(
      'post_type'      => 'page',
      'post_parent' => $current_page_id,

            // Only show one level of hierarchy
      'posts_per_page' => -1,
      'order' => 'ASC'

    );

        // start only if we have some childpages
    if ($child_pages) {

            // if we have some children, display a list wrapper
      echo '<div class="flex-category '.get_the_ID().'">';



      $args = array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'post_parent'    => $current_page_id,
        'order'          => 'ASC',
        'orderby'        => 'menu_order'
      );
      $parent = new WP_Query( $args );
      if ( $parent->have_posts() ) :
        $i = 100;
        while ( $parent->have_posts() ) : $parent->the_post();
                $page_id      = $parent->ID; // get the ID of the childpage
                $page_link    = get_permalink( $page_id ); // returns the link to childpage
                $page_img     = get_the_post_thumbnail( $page_id); // returns the featured image <img> element
                $page_title   = get_the_title( $current_page_id ); // returns the title of the child page
                $page_excerpt = get_the_excerpt( $page_id);
                ?>
                <div class="info-box" data-aos="fade-up" data-aos-delay="<?=$i;?>">
                  <a href="<?php the_permalink();?>" class="">
                    <div class="lazyloaded info-box__img">
                      <div class="entry-featured-image-url">
                        <?php echo $page_img; //display featured image ?>
                      </div>
                    </div>
                    <div class="info-box__category">

                      <?php echo $page_title;?>

                    </div>
                    <div class="info-box__description">
                      <div class="info-box__inner">
                        <h4 class="info-box__title">
                          <?php echo the_title(); ?>
                        </h4>
                        <div class="info-box__hidden" style="display:none;">
                          <p></p>
                          <a class="info-box__link with--line" href="<?php the_permalink(); ?>">en savoir + </a>
                        </div>
                      </div>
                    </div>
                  </a>
                </div>
                <?php $i += 100; ?>
                <!-- .ma_sous_page -->
              <?php endwhile;
            endif; wp_reset_postdata();

            echo '</div>';


        }//END if ($child_pages)  
      }//end chapeau  

    }//END if (is_page())

    // return the object
    return ob_get_clean();



  }
  add_shortcode( 'show_childpages', 'show_childpages_shortcode' );
// affichage des sous pages si pas de chapeau
function show_childpages_chapeau_shortcode() {

    // a shortcode should just return the content not echo html
    // so we start to create an object, and on the end we return it
    // if we dont do this the shortcode will be displayed in the top of the content
  ob_start();

    // only start if we are on a single page
  if ( is_page() ) {
        // get the ID of the current (parent) page
    $current_page_id = get_the_ID();
     // si chapeau on affiche pas ce shortcode
     if(get_field('chapeau_de_la_page')) {?>
     <p class="has-blanc-color has-couleur-theme-segondaire-background-color has-text-color has-background has-link-color"><?= the_field('chapeau_de_la_page', false, false);?></p>
     <?php
     

        // get all the children of the current page
    $child_pages = array(
      'post_type'      => 'page',
      'post_parent' => $current_page_id,

            // Only show one level of hierarchy
      'posts_per_page' => -1,
      'order' => 'ASC'

    );

        // start only if we have some childpages
    if ($child_pages) {

            // if we have some children, display a list wrapper
      echo '<div class="flex-category '.get_the_ID().'">';



      $args = array(
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'post_parent'    => $current_page_id,
        'order'          => 'ASC',
        'orderby'        => 'menu_order'
      );
      $parent = new WP_Query( $args );
      if ( $parent->have_posts() ) :
        $i = 100;
        while ( $parent->have_posts() ) : $parent->the_post();
                $page_id      = $parent->ID; // get the ID of the childpage
                $page_link    = get_permalink( $page_id ); // returns the link to childpage
                $page_img     = get_the_post_thumbnail( $page_id); // returns the featured image <img> element
                $page_title   = get_the_title( $current_page_id ); // returns the title of the child page
                $page_excerpt = get_the_excerpt( $page_id);
                ?>
                <div class="info-box" data-aos="fade-up" data-aos-delay="<?= $i;?>">
                  <a href="<?php the_permalink();?>" class="">
                    <div class="lazyloaded info-box__img">
                      <div class="entry-featured-image-url">
                        <?php echo $page_img; //display featured image ?>
                      </div>
                    </div>
                    
                    <div class="info-box__description">
                      <div class="info-box__inner">
                        <h4 class="info-box__title">
                          <?php echo the_title(); ?>
                        </h4>
                        <div class="info-box__hidden" style="display:none;">
                          <p></p>
                          <a class="info-box__link with--line" href="<?php the_permalink(); ?>">en savoir + </a>
                        </div>
                      </div>
                    </div>
                  </a>
                </div>
                <?php $i +=100;?>
                <!-- .ma_sous_page -->
              <?php endwhile;
            endif; wp_reset_postdata();

            echo '</div>';


        }//END if ($child_pages)  
      }//end chapeau  

    }//END if (is_page())

    // return the object
    return ob_get_clean();



  }
  add_shortcode( 'show_childpages_chapeau', 'show_childpages_chapeau_shortcode' );
?>