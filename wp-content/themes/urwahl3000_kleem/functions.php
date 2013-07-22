<?php

add_action( 'after_setup_theme', 'kr8_childtheme',9999 );

function kr8_childtheme() {
  if (!is_admin()) {

    wp_register_style( 'kr8-childtheme', get_stylesheet_directory_uri() . '/style.css', array('kr8-stylesheet'), '', 'all' );
    wp_enqueue_style( 'kr8-childtheme' );

  }
}

?>