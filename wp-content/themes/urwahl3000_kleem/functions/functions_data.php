<?php
 
 
/**
 * Registeres the post type 'opinion' along with its taxonomy 'opintion_topic'
 */
function kleem_register_pt_opinion()  {
    
    $post_type_labels = array(
        'name' =>               _x('Argumente', 'post type general name'),
        'singular_name' =>      _x('Argument', 'post type singular name'),
        'add_new' =>            _x('Argument hinzufügen', ''),
        'add_new_item' =>       __('Argument Hinzufügen'),
        'edit_item' =>          __('Argument Editieren'),
        'new_item' =>           __('Neue Argument'),
        'all_items' =>          __('Alle Argumente'),
        'view_item' =>          __('Argument Ansehen'),
        'search_items' =>       __('Argumente Suchen'),
        'not_found' =>          __('Keine Argument gefunden!'),
        'not_found_in_trash' => __('Keine Argumente im Papierkorb'), 
        'parent_item_colon' =>  '',
        'menu_name' => 			'Alle Argumente'
    );
    
    $post_type_args = array(
        'labels'        => $post_type_labels, 
        'public'        => true,
        'description'   => 'kleem custom post type',
        'show_in_menu'  => true,
        'has_archive'   => true,
        'supports'      => array('title', 'editor', 'author', 'thumbnail', 'comments'),
        'taxonomies'    => array('opinion_topics'),
    );
    
    $post_taxonomy_args = array(
        "hierarchical"      => true,
        "label"             => "Thema",
        "singular_label"    => "Thema",
        "rewrite"           => true,
    );
        
        
	
	register_taxonomy("opinion_topics", array("opinion"), $post_taxonomy_args);
	register_post_type('opinion', $post_type_args);
    
}


function kleem_get_post_views($postID){
    $count_key = '_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    return ($count=='')? 0 : intval($count);
}


function kleem_update_post_views($postID) {
    $meta_key = '_post_views_count';
    $count = get_post_meta($postID, $meta_key, true);
	$count = ($count == "")? 0 : intval($count);
	
	update_post_meta($postID, $meta_key, $count + 1);
}


function kleem_update_controversity($postID, $agreement = -1, $disaffirmation = -1) {
    if($agreement == -1) {
        $agreement = get_post_meta($postID, '_agreement', true);
        if($agreement == '') {
            $agreement = 0;
        }
    }
    if($disaffirmation == -1) {
        $disaffirmation = get_post_meta($postID, '_disaffirmation', true);
        if($disaffirmation == '') {
            $disaffirmation = 0;
        }
    }
    
    $disaffirmation = intval($disaffirmation);
    $agreement = intval($agreement);
    
    $sum = $agreement + $disaffirmation;
    $contro = 0;
    if($sum != 0) {
        $contro = ($agreement-$disaffirmation) / $sum;
		$contro = 1 - abs($contro);
        $contro *= sqrt($sum);
    }
    
    update_post_meta($postID, '_controversity', $contro);
}


function is_custom_post_type() {
	return in_array(get_post_type(), get_post_types(array('public' => true, '_builtin' => false), 'names', 'and') ); 
}



?>