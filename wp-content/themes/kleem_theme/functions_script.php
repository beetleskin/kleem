<?php

// enqueue scripts
add_action('wp_enqueue_scripts', 'rio_add_script');
// register ajax callbacks
add_action('wp_ajax_rio_ajax_rate', 'rio_ajax_rate');
add_action('wp_ajax_rio_ajax_more_posts', 'rio_ajax_more_posts');
add_action('wp_ajax_nopriv_rio_ajax_more_posts', 'rio_ajax_more_posts');


// add script
function rio_add_script() {
    
    
    // create config
    $nonce = wp_create_nonce('rio-general');
    $protocol = (is_ssl()) ? 'https://' : 'http://';
    $ajaxConfig = array(
        'ajaxurl'           => get_home_url() . '/wp-admin/admin-ajax.php',
        'rateAction'        => 'rio_ajax_rate',
        'morePostsAction'   => 'rio_ajax_more_posts',
        'bookboak'          => $nonce,
    );
    
    
    // enqueue script
    wp_register_script('rio-message-script', get_stylesheet_directory_uri() . '/js/rio-message.js', array('thickbox', 'jquery'));
    wp_enqueue_script('rio-message-script');
    // add configs
    wp_localize_script('rio-message-script', 'rio_msg_ajax', $ajaxConfig);
}


function rio_ajax_rate() {
    if(!rio_ajax_security_check('rio-general', get_home_url())) {
        die('security error');
    }
    
    
    if( key_exists('rating', $_REQUEST) ) {
        
        $msgID = intval(wp_strip_all_tags($_REQUEST['rating']['msgID']));
        $ratingDelta = intval(wp_strip_all_tags($_REQUEST['rating']['val']));
        
        if(isset($msgID) && isset($ratingDelta) ) {
            $thePost = get_post($msgID);
            
            
            if( $thePost != NULL && $thePost->post_type == 'rio_message' ) {
                $uID = get_current_user_id();
                $userRating = get_user_meta($uID, 'rated', true);
                
                
                
                // user has already voted for this message
                if($userRating != "" && array_key_exists($msgID, $userRating)) {
                    
                    // remove old vote from post
                    $metaKey = ($userRating[$msgID] > 0)? 'agreement' : 'disaffirmation';
                    $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
                    update_post_meta($msgID, $metaKey, $metaKeyVal - 1);
                    
                    
                    // voted the same as before, reset vote from user
                    if( $ratingDelta == $userRating[$msgID]) {
                        unset($userRating[$msgID]);
                        
                    // voted something different, update user and post
                    } else {
                        $userRating[$msgID] = $ratingDelta;
                        $metaKey = ($userRating[$msgID] > 0)? 'agreement' : 'disaffirmation';
                        $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
                        update_post_meta($msgID, $metaKey, $metaKeyVal + 1);
                    }
                    
                    update_user_meta($uID, 'rated', $userRating);
                    
                    
                    
                // user has not yet voted
                } else {
                    

                    $metaKey = ($ratingDelta > 0)? 'agreement' : 'disaffirmation';
                    $metaKeyVal = intval(get_post_meta($msgID, $metaKey, true));
                    if($userRating === "") {
                        $userRating = array();
                    }
                    $userRating[$msgID] = $ratingDelta;
                    
                    
                    update_post_meta($msgID, $metaKey, $metaKeyVal + 1);
                    update_user_meta($uID, 'rated', $userRating);
                }
            
            
                setControversity($msgID);
                header("Content-Type: text/plain");
                echo json_encode(array('newBox' => rio_get_the_ratingbox($msgID, $uID)));
                die();
            }
        }
    }
}

function rio_ajax_more_posts() {
    if(!rio_ajax_security_check('rio-general', get_home_url(), true)) {
        die('security error');
    }
    
   
    global $wp_query;
    $page_no; $loop; $query;

    // validate ...
    if (empty($_REQUEST['query']) || empty($_REQUEST['loop'])) {
        echo '<p>query var ERROR</p>';
        die();
    }


    $query = unserialize(base64_decode(wp_strip_all_tags($_REQUEST['query'])));
    $loop = wp_strip_all_tags($_REQUEST['loop']);
    $paged = wp_strip_all_tags($_REQUEST['paged']);
    $query['paged'] = $paged;
    // just to be sure ... 
    $query['post_type'] = 'rio_message';
    $query['nopaging'] = false;
    

    ob_start();
    if ($loop) {
        query_posts($query);
        while (have_posts()) : the_post();
            get_template_part($loop);
        endwhile;
    }

    $buffer = ob_get_contents();
    ob_end_clean();

    echo $buffer;
    exit;
}


function rio_ajax_security_check($nonceTerm, $redirect, $htmlrepsonse = false) {
    $response = array();
    
    
    $nonce = NULL;
    if(key_exists("bookboak", $_REQUEST))
        $nonce = wp_strip_all_tags($_REQUEST["bookboak"]);


    
    if( !isset($nonce) || wp_verify_nonce($nonce, $nonceTerm) != 1 ) {
        $message = '<div id="securityErrorMessage"><p>Sorry, deine Session ist abgelaufen ... </p><a href="' . $redirect . '">Hier </a>gehts weiter</div>';
        
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