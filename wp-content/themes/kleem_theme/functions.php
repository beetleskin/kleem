<?php

add_action('init', 'kleem_setup');

register_activation_hook(__FILE__, 'kleem_activate');
register_deactivation_hook(__FILE__, 'kleem_deactivate');

add_action('generate_rewrite_rules', 'kleem_add_rewrite_rules');
add_action( 'after_setup_theme', 'theme_formats');
add_filter('query_vars', 'kleem_queryvars');
add_filter('pre_get_posts', 'kleem_filter');
/* front end action hooks */
add_filter('created_klimo_idea_topics', 'kleem_add_idea_menu_term_item_hook');
add_filter('sidebar_login_widget_logged_out_links', 'kleem_sidebar_login_loggedout_links_hook');
add_filter('sidebar_login_widget_logged_in_links', 'kleem_sidebar_login_loggedin_links_hook');
add_filter('wpmem_register_form', 'kleem_adapt_register_form');
add_filter('login_url', 'kleem_override_login_page', 10, 2);
add_filter('register_url', 'kleem_override_register_page', 10, 2);
add_filter('lostpassword_url', 'kleem_override_lostpw_page', 10, 2);

include_once ('inc/functions_admin.php');
include_once ('inc/functions_render.php');
include_once ('inc/functions_ajax.php');
include_once ('inc/functions_data.php');


function kleem_setup() {
    
	// post formats
	add_theme_support( 'post-formats', array( 'status', 'video', 'quote' ) );
	
	// navs
	register_nav_menu('top', 'Topmenu');
	register_nav_menu('bottom', 'Footermenu');

	// data stuff
	kleem_register_pt_opinion();

	// other stuff
	render_init();
	ajax_init();
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
//WP Members

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


function kleem_adapt_register_form($form) {
	$form = str_replace("First Name", "Vorname", $form);
	$form = str_replace("Last Name", "Nachname", $form);
	$form = str_replace("Wähle einen Mitgliedernamen", "Benutzername", $form);
	$form = str_replace('<small>Powered by <a href="http://rocketgeek.com" target="_blank">WP-Members</a></small>', '', $form);
	
	//FIXME: register and profile edit have the same form. the following adaptions dont work for profile edit.
	/*$fieldset1_begin = '<label for="username" class="text">Benutzername<font class="req">*</font></label>';
	$fieldset_inter = '<label for="first_name" class="text">Vorname</label>';
	$fieldset2_end = '<div class="div_text"><input name="tos"';
	$form = str_replace($fieldset1_begin, '<fieldset><legend>Benötigt</legend><div class="slide-wrap">' . $fieldset1_begin, $form);
	$form = str_replace($fieldset_inter, '</div></fieldset><fieldset><legend>Details</legend><div class="slide-wrap" collapsed="collapsd">' . $fieldset_inter, $form);
	$form = str_replace($fieldset2_end, '</div></fieldset>' . $fieldset2_end, $form);
	 
	
	
	$script = 'jQuery(function($) { 
			$(document).ready(function() {
				$("#wpmem_reg div.slide-wrap[collapsed]").slideToggle(0);
				$("#wpmem_reg form fieldset fieldset legend").click(function() {
  					$(".slide-wrap", $(this).parent()).slideToggle();
				});
			});
		});';
	return $form . "<script>" . $script . "</script>";*/
	return $form;
	 
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

?>