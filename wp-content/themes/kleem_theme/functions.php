<?php

add_action('init', 'theme_init');

register_activation_hook(__FILE__, 'kleem_activate');
register_deactivation_hook(__FILE__, 'kleem_deactivate');

add_action('generate_rewrite_rules', 'kleem_add_rewrite_rules');
add_filter('query_vars', 'kleem_queryvars');
add_filter('pre_get_posts', 'kleem_filter');

include_once ('inc/functions_admin.php');
include_once ('inc/functions_render.php');
include_once ('inc/functions_ajax.php');
include_once ('inc/functions_data.php');


function theme_init() {
	// theme stuff
	register_nav_menu('top', 'Topmenu');
	register_nav_menu('footer', 'Footermenu');

	// data stuff
	kleem_register_pt_opinion();

	// other stuff
	render_init();
	ajax_init();
}


function kleem_add_rewrite_rules($rewrite) {
	global $wp_rewrite;

	// add rewrite tokens
	$keytag = '%opinion_custom%';
	$wp_rewrite -> add_rewrite_tag($keytag, '(.+?)', 'opinion_custom=');

	$keywords_structure = $wp_rewrite -> root . "opinion_custom/$keytag/";
	$keywords_rewrite = $wp_rewrite -> generate_rewrite_rules($keywords_structure);

	$wp_rewrite -> rules = $keywords_rewrite + $wp_rewrite -> rules;
	return $wp_rewrite -> rules;
}


function kleem_queryvars($qvars) {
	$qvars[] = 'opinion_custom';
	return $qvars;
}


/*
 * Handle cutom queries of the 'opinion' post type: Read $REQUEST params and convert to WP_QUERY params.
 */
function kleem_filter($query) {
	
	// handle custom request params
	if ( isset($query->query_vars['opinion_custom']) && !empty($query->query_vars['opinion_custom']) ) {
		$custom_request = $query -> query_vars['opinion_custom'];

		// reset everything else
		$query_custom_vars = array(
			'post_type'			=> 'opinion',
			'post_status'		=> 'publish',
			'order' 			=> 'DESC',
			'orderby'		 	=> 'meta_value_num', 
			'posts_per_page' 	=> 10);
		
		if ($custom_request == 'most_agreet') {
			$query_custom_vars['meta_key'] = '_agreement';
		} else if ($custom_request == 'most_disagreet') {
			$query_custom_vars['meta_key'] = '_disaffirmation';
		} else if ($custom_request == 'diversive') {
			$query_custom_vars['meta_key'] = '_controversity';
		}

		$query->query_vars = wp_parse_args($query_custom_vars, $query->query_vars);
		remove_action( 'pre_get_posts', 'kleem_filter' );
	} 
	
	// alter author page post query by custom post types
	 if ( $query->is_author === true && !empty($query->query_vars['author_name']) ) {
        $query->set( 'post_type', array('post', 'opinion') );
    	remove_action( 'pre_get_posts', 'kleem_filter' );
	}


	
	return $query;
}


function kleem_activate() {
	kleem_add_post_type();
	flush_rewrite_rules();
}


function kleem_deactivate() {
	flush_rewrite_rules();
}
?>