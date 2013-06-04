<?php
 

add_action('init', 'rio_init');   
add_action('generate_rewrite_rules', 'rio_add_rewrite_rules'); 


register_activation_hook( __FILE__, 'rio_activate' );
register_deactivation_hook( __FILE__, 'rio_deactivate' );


add_filter('intermediate_image_sizes', 'rio_image_resizing');
add_filter('query_vars', 'rio_queryvars' );
add_filter('pre_get_posts', 'rio_filter');
add_filter('login_errors', create_function('$a', "return null;"));
    
    
include_once ('functions_admin.php');
include_once ('functions_render.php');
include_once ('functions_script.php');



function rio_init() {
    
    
    rio_set_constants();
    rio_add_post_type();
    rio_register_custom_menu();
    
    
    // enable gzip compression
    ob_start("ob_gzhandler");
}


    
function rio_set_constants(){
	define("RIO_DEFAULT_THUMBNAIL", get_stylesheet_directory_uri() . "/images/LookMessageToRioThumb.png");
}     
    
    
function rio_add_rewrite_rules( $rewrite ) {
    global $wp_rewrite;
 
    // add rewrite tokens
    $keytag = '%rio_custom%';
    $wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'rio_custom=');
 
    $keywords_structure = $wp_rewrite->root . "rio_custom/$keytag/";
    $keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure);
 
    $wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
    return $wp_rewrite->rules;
}


function rio_queryvars( $qvars ) {
    $qvars[] = 'rio_custom';
    return $qvars;
}


function rio_filter($query) {
    if( isset( $query->query_vars['rio_custom'] )) {
        
        $custom_request = $query->query_vars['rio_custom'];
        
        // reset everything else
        $query->query_vars['post_type'] = 'rio_message';
        $args = "";
        
        if($custom_request == 'most_agreet') {
            $args = array(
                'meta_key'          => 'agreement',
                'order'             => 'DESC',
                'orderby'           => 'meta_value_num',
                'posts_per_page'    => 10,
            );
            
        } else if($custom_request == 'most_disagreet') {
            $args = array(
                'meta_key'          => 'disaffirmation',
                'order'             => 'DESC',
                'orderby'           => 'meta_value_num',
                'posts_per_page'    => 10,
            );
            
        } else if($custom_request == 'diversive') {
            $args = array(
                'meta_key'          => 'controversity',
                'order'             => 'DESC',
                'orderby'           => 'meta_value_num',
                'posts_per_page'    => 10,
            );
        }


        $query->query_vars = wp_parse_args($args, $query->query_vars);
        
        
    }
    
    if ( (is_tax() || is_author() || is_home() || is_search())  && empty($query -> query_vars['suppress_filters'])) {
        $query->set('post_type', 'rio_message');
    }
    
    return $query;
}


function rio_image_resizing($size) {
    $ret = array('thumbnail');
    return $ret;
}


function rio_register_custom_menu() {
    register_nav_menu('top', __('top'));
}




function rio_add_post_type()  {
    
    $rio_type_labels = array(
        'name' =>               _x('Rio-Nachrichten', 'post type general name'),
        'singular_name' =>      _x('Rio-Nachricht', 'post type singular name'),
        'add_new' =>            _x('Hinzufügen', 'Nachricht'),
        'add_new_item' =>       __('Nachricht Hinzufügen'),
        'edit_item' =>          __('Nachricht Editieren'),
        'new_item' =>           __('Neue Nachricht'),
        'all_items' =>          __('Alle Nachrichten'),
        'view_item' =>          __('Nachricht Ansehen'),
        'search_items' =>       __('Nachrichten Suchen'),
        'not_found' =>          __('Keine Nachrichten gefunden!'),
        'not_found_in_trash' => __('Keine Nachrichten im Papierkorb'), 
        'parent_item_colon' =>  '',
        'menu_name' => 'Rio-Nachrichten'
    );
    
    $rio_type_args = array(
        'labels'        => $rio_type_labels, 
        'public'        => true,
        'description'   => 'message-to-rio custom post type',
        'show_in_menu'  => true,
        'map_meta_cap'  => true,
        'has_archive'   => false,
        'supports'      => array('title', 'editor', 'author', 'thumbnail', 'custom-fields', 'comments', 'post-formats'),
        'taxonomies'    => array('rio_topics'),
    );
//     
    
    $rio_taxonomy_args = array(
        "hierarchical"      => true,
        "label"             => "Thema",
        "singular_label"    => "Thema",
        "rewrite"           => true,
    );
        
        
    register_taxonomy("rio_topics", array("rio_message"), $rio_taxonomy_args);
    register_post_type('rio_message', $rio_type_args);
}


function getPostViews($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 Views";
    }
    return $count . ' Views';
}

function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function setControversity($postID, $agreement = -1, $disaffirmation = -1) {
    if($agreement == -1) {
        $agreement = get_post_meta($postID, 'agreement', true);
        if($agreement == '') {
            $agreement = 0;
        }
    }
    if($disaffirmation == -1) {
        $disaffirmation = get_post_meta($postID, 'disaffirmation', true);
        if($disaffirmation == '') {
            $disaffirmation = 0;
        }
    }
    
    $disaffirmation = intval($disaffirmation);
    $agreement = intval($agreement);
    
    $sum = $agreement + $disaffirmation;
    $contro = 0;
    if($sum != 0) {
        $contro = (1 - abs(($agreement/$sum) - 0.5)) * 15;
        $contro += sqrt((1+$sum/3));
    }
    
    update_post_meta($postID, 'controversity', $contro);
}




function rio_activate()  {
    rio_add_post_type();
    flush_rewrite_rules();
}

function rio_deactivate()  {
    flush_rewrite_rules();
}





?>