<?php
// TODO: wrap rating post meta in one array

function ajax_init() {
	// register ajax callbacks
	add_action('wp_ajax_ajax_rate', 'ajax_rate');
	add_action('wp_ajax_ajax_more_posts', 'ajax_more_posts');
	add_action('wp_ajax_nopriv_ajax_more_posts', 'ajax_more_posts');
	
	kleem_add_script();
}



function kleem_add_script() {
    
    // create config
    $nonce = wp_create_nonce('kleem-general');
    $protocol = (is_ssl()) ? 'https://' : 'http://';
    $ajaxConfig = array(
        'ajaxurl'           => get_home_url() . '/wp-admin/admin-ajax.php',
        'rateAction'        => 'ajax_rate',
        'morePostsAction'   => 'ajax_more_posts',
        'bookboak'          => $nonce,
    );
    
    
    // add configs
    wp_localize_script('kleem-opinion-script', 'kleem_ajax_config', $ajaxConfig);
}


/**
 * Ajax callback for rating requests
 */
function ajax_rate() {
    if(!ajax_security_check('kleem-general', get_home_url())) {
        die('security error');
    }
    
    
    if( !key_exists('rating', $_REQUEST) )
		die();
        
	
    $msgID = intval(wp_strip_all_tags($_REQUEST['rating']['msgID']));
    $ratingDelta = intval(wp_strip_all_tags($_REQUEST['rating']['val']));
    
    if( !(isset($msgID) && isset($ratingDelta)) )
		die();
	
	
    $thePost = get_post($msgID);
    
    
    if( $thePost != NULL && $thePost->post_type == 'opinion' ) {
        $uID = get_current_user_id();
        $userRating = get_user_meta($uID, '_rated', true);
        
        
        // user has already voted for this message
        if($userRating != "" && array_key_exists($msgID, $userRating)) {
            
            // remove old vote from post
            $metaKey = ($userRating[$msgID] > 0)? '_agreement' : '_disaffirmation';
            $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
            update_post_meta($msgID, $metaKey, $metaKeyVal - 1);
            
            
            // voted the same as before, reset vote from user
            if( $ratingDelta == $userRating[$msgID]) {
                unset($userRating[$msgID]);
                
            // voted something different, update user and post
            } else {
                $userRating[$msgID] = $ratingDelta;
                $metaKey = ($userRating[$msgID] > 0)? '_agreement' : '_disaffirmation';
                $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
                update_post_meta($msgID, $metaKey, $metaKeyVal + 1);
            }
            
            update_user_meta($uID, '_rated', $userRating);
            
            
            
        // user has not yet voted
        } else {
            

            $metaKey = ($ratingDelta > 0)? '_agreement' : '_disaffirmation';
            $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
            if($userRating === "") {
                $userRating = array();
            }
            $userRating[$msgID] = $ratingDelta;
            
            
            update_post_meta($msgID, $metaKey, $metaKeyVal + 1);
            update_user_meta($uID, '_rated', $userRating);
        }
    
    
        kleem_update_controversity($msgID);
        header("Content-Type: text/plain");
        echo json_encode(array('newBox' => kleem_get_the_ratingbox($msgID, $uID)));
        die();
    }
}

// TODO: template_part is kind of hard coded ...
// TODO: generate post-format for custom post type and render according to post format ...? would be more consistent to default theming ...
function ajax_more_posts() {
    if(!ajax_security_check('kleem-general', get_home_url(), true)) {
        die('security error');
    }
    
   
    global $wp_query;
    $page_no; $loop; $query;

    // validate ...
    if (empty($_REQUEST['query']) || empty($_REQUEST['template_part'])) {
        echo '<p>query var ERROR</p>';
        die();
    }


    $query = unserialize(base64_decode(wp_strip_all_tags($_REQUEST['query'])));
    $template_part = wp_strip_all_tags($_REQUEST['template_part']);
    $paged = wp_strip_all_tags($_REQUEST['paged']);
    $query['paged'] = $paged;
    // just to be sure ... 
    $query['post_type'] = 'opinion';
    $query['nopaging'] = false;
    

    ob_start();
    if ($template_part) {
        query_posts($query);
        while (have_posts()) : the_post();
            get_template_part($template_part);
        endwhile;
    }

    $buffer = ob_get_contents();
    ob_end_clean();

    echo $buffer;
    exit;
}


function ajax_security_check($nonceTerm, $redirect, $htmlrepsonse = false) {
    $response = array();
    
    
    $nonce = NULL;
    if(key_exists("bookboak", $_REQUEST))
        $nonce = wp_strip_all_tags($_REQUEST["bookboak"]);


    
    if( !isset($nonce) || wp_verify_nonce($nonce, $nonceTerm) != 1 ) {
        $message = '<div class="error"><p>Sorry, deine Session ist abgelaufen ... </p><a href="' . $redirect . '">Hier </a>gehts weiter</div>';
        
        header("Content-Type: text/plain");
        if($htmlrepsonse) {
            echo $message;
        } else {
            $response['securityError'] = array(
                'redirect' => $redirect,
                'message'  => $message,
            );
            echo json_encode($response);
        }
        
        return false;
    }
    
    return true;
}
?>