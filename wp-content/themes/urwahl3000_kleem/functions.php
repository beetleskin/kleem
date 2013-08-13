<?php

// kre8tive stuff
add_action( 'after_setup_theme', 'kr8_childtheme',9999 );
function kr8_childtheme() {
	if (!is_admin()) {
    	wp_register_style( 'kr8-childtheme', get_stylesheet_directory_uri() . '/style.css', array('kr8-stylesheet'), '', 'all' );
    	wp_enqueue_style( 'kr8-childtheme' );
	}
	

	// wp thumbnails
	add_theme_support('post-thumbnails');

	// default thumb size
	set_post_thumbnail_size(150, 150, true);
	add_image_size( 'medium', 540, 370,true );
	add_image_size( 'large', 1024, 768, true );
	
	
	
	update_option('thumbnail_size_w', 175);
	update_option('thumbnail_size_h', 175);
	update_option('medium_size_w', 540);
	update_option('medium_size_h', 370);
	update_option('large_size_w', 1024);
	update_option('large_size_h', 768);
}


// our stuff
add_action('init', 'kleem_setup');

register_activation_hook(__FILE__, 'kleem_activate');
register_deactivation_hook(__FILE__, 'kleem_deactivate');

add_action('generate_rewrite_rules', 'kleem_add_rewrite_rules');
add_action('after_setup_theme', 'theme_formats');
add_action('widgets_init', 'kleem_widgets_init' );
add_filter('query_vars', 'kleem_queryvars');
add_filter('pre_get_posts', 'kleem_filter');


/* front end action hooks */
add_filter('sidebar_login_widget_logged_out_links', 'kleem_sidebar_login_loggedout_links_hook');
add_filter('sidebar_login_widget_logged_in_links', 'kleem_sidebar_login_loggedin_links_hook');
add_filter('login_url', 'kleem_override_login_page', 10, 2);
add_filter('register_url', 'kleem_override_register_page', 10, 2);
add_filter('lostpassword_url', 'kleem_override_lostpw_page', 10, 2);
add_action( 'template_redirect', 'kleem_redirect' );

add_filter('sidebar_login_include_css', 'kleem_sidebar_login_include_css', 10, 2);
add_filter('sidebar_login_widget_logged_out_title', 'kleem_sidebar_login_widget_logged_out_title', 10, 2);
add_filter('sidebar_login_widget_form_args', 'kleem_sidebar_login_widget_form_args', 10, 2);



include_once ('functions/functions_admin.php');
include_once ('functions/functions_render.php'); 
include_once ('functions/functions_ajax.php');
include_once ('functions/functions_data.php');



function kleem_setup() {
    
	// data stuff
	kleem_register_pt_opinion();

	// other stuff
	render_init();
	ajax_init();
}


function kleem_widgets_init() {
	// widget area stuff
	register_sidebar(array(
		'name' 			=> 'Benutzer-Menu',
		'id' 			=> 'sidebar-header',
		'description'   => 'Widgetd für das Ein- und Ausloggen',
		'before_widget' => "\n\t\t" . '<li id="%1$s" class="widget %2$s">',
		'after_widget' 	=> '</li>',
		'before_title' 	=> "\n\t" . '<h3 class="widgettitle">',
		'after_title' 	=> '</h3>',
	));
}

function theme_formats(){
     add_theme_support( 'post-formats', array( 'page', 'opinion' ) );
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
			'order' 			=> 'ASC',
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
		return $query;
	} 
	
	// alter author page post query by custom post types
	if ( $query->is_author === true && !empty($query->query_vars['author_name']) ) {
        $query->set( 'post_type', array('post', 'opinion') );
    	remove_action( 'pre_get_posts', 'kleem_filter' );
		return $query;
	}
	

	
	return $query;
}


function kleem_redirect() {
	// redirect to login page if not authenticated user wants to visit the profile page
    if( is_page( 'wpm_profile' ) && ! is_user_logged_in() ) {
        auth_redirect();
    }
}


function kleem_activate() {
	kleem_add_post_type();
	flush_rewrite_rules();
}


function kleem_deactivate() {
	flush_rewrite_rules();
}



/**
 * WP_MEMBERS
 */
function kleem_override_login_page($login_url, $redirect) {
	$wpm_login_url = get_permalink(get_page_by_path( 'wpm_login' ) );

	if(!$wpm_login_url) {
		// TODO: log error
		return $login_url;
	}
	
	if ( !empty($redirect) )
		$wpm_login_url = add_query_arg('redirect_to', urlencode($redirect), $wpm_login_url);	

	return $wpm_login_url;
}

function kleem_override_register_page($register_url, $redirect) {
	$wpm_register_url = get_permalink(get_page_by_path( 'wpm_register' ) );
	
	if(!$wpm_register_url) {
		// TODO: log error
		return $register_url;
	}
	
	if ( !empty($redirect) )
		$wpm_register_url = add_query_arg('redirect_to', urlencode($redirect), $wpm_register_url);	

	return $wpm_register_url;
}

function kleem_override_lostpw_page($lostpw_url, $redirect) {
	$wpm_lostpw_url = get_permalink(get_page_by_path( 'wpm_password' ) );

	if(!$wpm_lostpw_url) {
		// TODO: log error
		return $lostpw_url;
	}
	
	if ( !empty($redirect) )
		$wpm_lostpw_url = add_query_arg('redirect_to', urlencode($redirect), $wpm_lostpw_url);	

	return $wpm_lostpw_url;
}



/**
 * SIDEBAR LOGIN
 */
function kleem_sidebar_login_loggedout_links_hook($links) {
	if( array_key_exists('register', $links)) {
		$links['register']['href'] = home_url( '/wpm_register/' );
	}
	if( array_key_exists('lost_password', $links)) {
		$links['lost_password']['href'] = home_url( '/wpm_password/' );
	}
	return $links;
}

function kleem_sidebar_login_loggedin_links_hook($links) {
	if( array_key_exists('profile', $links)) {
		$links['profile']['href'] = home_url( '/wpm_profile/' );
	}
	return $links;
}

function kleem_sidebar_login_include_css($args) {
	// no custom css
	return false;
}

function kleem_sidebar_login_widget_logged_out_title($args) {
	// no title
	return "";
}

function kleem_sidebar_login_widget_form_args($args) {
	$args['label_username'] = "Benutzer";
	$args['label_password'] = "Pwd";
	$args['label_remember'] = "merken";
	$args['label_login'] = "Login";
	$args['remember'] = false;
	return $args;
}

?>