<?php
/**
 * @package kleem_frontend_post_submitter
 */

/*
 Plugin Name: Frontend Post Submitter
 Description: Provides the message upload form
 Version: 0.1b
 Author: stfn
 License: GPLv2 or later
 */


add_action('init', 'FrontendPostSubmitter::init', 11);
add_action('init', 'fps_init');


function fps_init() {
	add_shortcode( 'fps', 'fps_render' );
}


function fps_render() {
	ob_start();
	$form = new FrontendPostSubmitter();
	$form->render();
	$form->post_render();
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}


class FrontendPostSubmitter {

    private static $ioConfig;
    private static $validationConfig;
    private static $url;
    

    function __construct() {
    }

    function pre_render() {
        $data = array();
        
        // defaults
        $data['nopriv_redirect'] = wp_login_url(get_permalink());
        $data['isLoggedIn'] = get_current_user_id() != 0;
        
        // topics
		$data['topics'] = get_terms("opinion_topics", array(
            'hide_empty'    => false,
            'hierarchical'  => false,
            'parent'		=> get_term_by('slug', '_maintopics', 'opinion_topics')->term_id,
        ));
        
        // images
		$data['images'] = array(
			"arrow" => plugin_dir_url(__FILE__) . "style/images/sidearrow.png",
			"plus" => plugin_dir_url(__FILE__) . "style/images/sideplus.png"
		);  
        return $data;
    }


    function render() {

        $data = $this->pre_render();
        ?>
        
        <div id="messageform_wrap">   	
            <form action="<?php echo $this->form_action ?>" id="messageform" <?php if($data['isLoggedIn'] == false) echo 'nopriv="nopriv"' ?> method="POST" novalidate="novalidate">

		        <div class="wrap">   
		        	<div id="errormessage" class="error">
	        	    <?php if($data['isLoggedIn'] == false) : ?>
                        <p>Um eine <strong>neue Meinung</strong> zu erstellen musst du <a href="<?php echo $data['nopriv_redirect']; ?>">eingeloggt</a> sein!</p>
                    <?php endif; ?>
		        	</div>
		        	
		        	<fieldset form="messageform">
			      	  	<div class="itemhead obligated"><img src="<?php echo $data ["images"]["arrow"];  ?>"/><h2>Sag deine Meinung</h2></div>
			     	    <div class="itembody">
			        	    <textarea id="message" name="message" placeholder="Deine Meinung (kurzgefasst: 300 Zeichen) ..."></textarea>
			        	</div>
			        </fieldset>
			        
			        <fieldset form="messageform">
				        <div class="itemhead"><img class="additional" src="<?php echo $data ["images"]["plus"];  ?>"/><h2>Details und Bild hinzufügen</h2></div>
				        <div class="itembody" style="display: none;">
				            <textarea id="description" name="description" placeholder="Du brauchst noch ein paar Sätze um deinen Standpunkt zu erklären? Dann schreibe hier deinen Text (500 Zeichen) ..."></textarea>
				        	<input type="file" id="image_upload" name="message_image" accept="image/*">
				        </div>
		        	</fieldset>
		
					<fieldset form="messageform">
				        <div class="itemhead obligated"><img src="<?php echo $data ["images"]["arrow"];  ?>"/><h2>Themen hinzufügen</h2></div>
				        <div class="itembody">
				        	
				        	<div class="all_topics_container clearfix">
					        	<div class="topics_container">
						        	<h2>Wähle <u>mindestens</u> ein Thema aus:</h2>
						            <select id="topics" name="topics" multiple="multiple">
						                
						                <?php foreach ( $data['topics'] as &$topic ): ?>
						                    <option value="<?php echo $topic->term_id ?>"><?php echo $topic->name ?></option>
						                <?php endforeach; ?>
						                
						            </select>
						  		</div>
						       	<div class="custom_topics_container">
						            <h2>Eigene Themen hinzufügen:</h2>
						            <input type="select" id="custom_topics" name="custom_topics" placeholder="Thema hinzufügen ..."/>
						        </div>
						        <p style="clear:both;"></p>
					       	</div>			           
				        </div>
					</fieldset>		
			    </div>
			    <div id="progressbar">
                    <div class="bar"></div>
                    <div class="percent">0%</div>
                </div>
	       		<div class="message_submit_container">
                    <button form="messageform" id="opinion_submit" <?php if($data['isLoggedIn'] == false) echo 'nopriv="nopriv"' ?>>Abschicken</button>   
                </div><!-- .message_submit_container-->
                <input id="maxfilesize" type="hidden" name="MAX_FILE_SIZE" value="<?php echo self::$validationConfig['image_size_max'] ; ?>" />

        	</form>
       </div>
	<?php
	}

	public function post_render() {
		$this->enqueue_styles();
    	$this->enqueue_scripts();
    	$this->print_ajax_config();
	}


    public function print_ajax_config() {
        
        // add security check
        self::$ioConfig['bookboak'] = wp_create_nonce  ('kleem-newmsg');
        
        // Print data to sourcecode
        wp_localize_script('fps-script', 'messageform_config', self::$ioConfig);
        wp_localize_script('fps-script', 'messageform_validation', self::$validationConfig);
    }


    function enqueue_scripts() {
        
        // multiselect widget
        wp_enqueue_script('jquery-ui-multiselect', plugins_url('script/jquery.ui.multiselect/src/jquery.multiselect.min.js', __FILE__), array('jquery-ui-core', 'jquery-ui-widget'));
        
        // autosuggest
        wp_enqueue_script('autosuggest', plugins_url('script/autoSuggestv14/jquery.autoSuggest.packed.js', __FILE__), array('jquery'));
        
        // message form script
        wp_enqueue_script('fps-script', plugins_url('script/frontend_post_form.js', __FILE__), array('jquery', 'jquery-form', 'autosuggest', 'jquery-ui-multiselect'));
    }
    
    
    function enqueue_styles() {
        // multiselect style
        wp_enqueue_style('jquery-ui-multiselect', plugins_url('script/jquery.ui.multiselect/jquery.multiselect.css', __FILE__));
        
        // jquery-ui style
        wp_enqueue_style('jquery-ui-theme', plugins_url('style/jquery_messageform.css', __FILE__));
        
        // autosuggest style
        wp_enqueue_style('autosuggest', plugins_url('script/autoSuggestv14/autoSuggest.css', __FILE__));
        
        // message form style
        wp_enqueue_style('messageform-style', plugins_url('style/messageform.css', __FILE__), array('jquery-ui-multiselect'));
    }


    public static function init() {
    	global $wp_handle_upload_error;
        $wp_handle_upload_error = 'FrontendPostSubmitter::myHandleUploadError';
        self::$url = home_url("mitreden");
        self::$ioConfig = array(
            'ajaxurl'               => get_home_url() . '/wp-admin/admin-ajax.php',
            'submitAction'          => 'messageform_submit',
            'suggestTagsAction'     => 'messageform_tags',
            'suggestTagsQueryParam' => 'qry',
        );
        self::$validationConfig = array(
            'message_max_chars'     => 300,
            'message_min_chars'     => 20,
            'description_max_chars' => 500,
            'sub_topic_min_chars'   => 1,
            'sub_topic_max_chars'   => 15,
            'custom_topics_max'        => 5,
            'image_size_max'        => 3000000,
        );
        
        // register ajax actions
        add_action('wp_ajax_' . self::$ioConfig['submitAction'], 'FrontendPostSubmitter::submit');
        add_action('wp_ajax_nopriv' . self::$ioConfig['submitAction'], 'FrontendPostSubmitter::submit_nopriv');
        add_action('wp_ajax_' . self::$ioConfig['suggestTagsAction'], 'FrontendPostSubmitter::ajax_get_tags');
        add_action('wp_ajax_nopriv_' . self::$ioConfig['suggestTagsAction'], 'FrontendPostSubmitter::ajax_get_tags');
    }


    
    public static function submit_nopriv() {
         if( !self::securityCheck()) {
            die();
        }
         
        $msg = '<p>Um eine <strong>neue Idee</strong> zu erstellen musst du <a href="' . wp_login_url(get_permalink()) . '">eingeloggt</a> sein!</p>';
        self::ajaxRespond($message);
        die();
    }


    public static function submit() {
        if( !self::securityCheck()  || !self::validate() ) {
            die();
        }
        
        $error = null;
        $description = wp_strip_all_tags($_POST['description']);
        $message = wp_strip_all_tags($_POST['message']);
        $post_topic_IDs = array();
        $query_topics = array_merge(explode(",", $_POST['topics']), array_slice(explode(",", $_POST['custom_topics']), 0, 5));
        
        
        // validate main topic selection
        foreach ($query_topics as $topic) {
            $topicID = intval($topic);
            
            // valid integer, check if ID exists
            if($topicID > 0) {
                if(!get_term_by('id', $topicID, 'opinion_topics')) {
                    continue;
                }
                
            // string 
            } else {
                
                // remove all the tags!!!
                $topic = wp_strip_all_tags($topic);
                if(strlen($topic) < 3 || strlen($topic) > 15) {
                    continue;
                }
                
                
                $matching_term = term_exists($topic, 'opinion_topics');
                if($matching_term !== NULL) {
                    $topicID = $matching_term['term_id'];
                } else {
                    $new_term = wp_insert_term($topic, 'opinion_topics');
                    if( !is_wp_error($new_term)) {
                        $topicID = $new_term['term_id']; 
                    } else {
                        $error = $new_term->get_error_message();
                    }
                    
                }
            }
             
            if(is_int($topicID)) {
                $post_topic_IDs[] = $topicID;
            }
        }

		// parse links
		if( function_exists("kleem_auto_link_text")) {
			$description = kleem_auto_link_text($description);
		}
        
		// insert post
        $post_args = array(
            'ping_status'   => 'open',
            'post_author'   => get_current_user_id(),
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_title'    => $message,
            'post_type'     => 'opinion',
        );

        $postID = wp_insert_post($post_args);
        if(is_wp_error($postID)) {
            // error($new_term->get_error_message());
            header("HTTP/1.0 500 Internal Server Error");
            die();
        }
        
        // insert opinion_topics
        if(count($post_topic_IDs) > 0) {
            wp_set_object_terms($postID, $post_topic_IDs, 'opinion_topics', true);
        }
		
		// insert meta
        update_post_meta($postID, 'agreement', 0);
        update_post_meta($postID, 'disaffirmation', 0);
        
        // attach image
        if( key_exists('message_image', $_FILES) ) {
            $attach_id = media_handle_upload( 'message_image', $postID );
            if(!is_wp_error($attach_id)) {
                update_post_meta( $postID, '_thumbnail_id', $attach_id );
            } else {
                // TODO: log
            }
        }
        
        $response = "";
        if($error == NULL) {
            $response = array(
                'success'   => self::sumitSuccessHTML($postID),
            );
        } else {
            $response = array(
                'error'   => $error,
            );
        }
            
        
        self::ajaxRespond($response);
        die();
    }


    public static function ajax_get_tags($param) {
        if( !(self::securityCheck() && key_exists(self::$ioConfig['suggestTagsQueryParam'], $_REQUEST) ) ) {
            die();
        }
        
        $query =  $_REQUEST[self::$ioConfig['suggestTagsQueryParam']];
        
        $parent_exclude = get_term_by( 'slug', '_maintopics', 'opinion_topics' )->term_id;
        $exclude =  get_terms('opinion_topics', array('parent' => $parent_exclude, 'hide_empty' => 0, 'fields' => 'ids'));
        $exclude[] = $parent_exclude;
        $query_args = array(
        	'exclude'  		=> $exclude,
            'search'        => $query,
            'hide_empty'    => false,
            'hierarchical'  => false,
            'order_by'      =>'count'
        );
        
        $matches = get_terms('opinion_topics', $query_args);
        
        
        $json = array();
        foreach ($matches as &$match) {
            $json[] = array(
                'value' => $match->term_id,
                'name' => $match->name,
            );
        }
        
        
        header("Content-Type: text/plain");
        echo json_encode($json);
        die();
    }


    public static function myHandleUploadError($file, $message) {
        $response['error'][] = array(
            'element'   => 'message_image',
            'message'   => $message,
        );
        
        
        header("Content-Type: text/plain");
        echo json_encode($response);
        die('upload-error');
    }


    private static function validate() {
        $response = array();
        
        // check message
        $element = "message";
        $value = trim(wp_strip_all_tags($_POST[$element]));
        
        // too short?
        if(strlen($value) < self::$validationConfig['message_min_chars']) {
            $response['error'][] = array(
                'element'   => $element,
                'message'   => "Die Nachricht muss mindestens " . self::$validationConfig['message_min_chars'] . " Zeichen lang sein.",
            );
            
        // too long?
        } else if(strlen($value) > self::$validationConfig['message_max_chars']) {
            $response['error'][] = array(
                'element'   => $element,
                'message'   => "Die Nachricht darf maximal " . self::$validationConfig['message_max_chars'] . " Zeichen lang sein.",
            );
            
        // check if post is already there
        } else {
            $matchingPosts = get_posts(array(
                'name' => $value,
                'post_type' => 'opinion',
                'post_status' => 'publish',
                'posts_per_page' => 1,)
            );
            
            
            if($matchingPosts && count($matchingPosts) > 0) {
                $post = &$matchingPosts[0];
                $response['error'][] = array(
                    'element'   => $element,
                    'message'   => 'Dieses Thema gibt es <a href="' . get_post_permalink($post->ID , false) . '" title="' . $post->post_title . '" target="_blank">hier</a> schon!',
                );
            }
        }
        
        
        
        
        
        // check description
        $element = "description";
        $value = trim(wp_strip_all_tags($_POST[$element]));
        if(strlen($value) > self::$validationConfig['description_max_chars']) {
            $response['error'][] = array(
                'element'   => $element,
                'message'   => "Die Nachricht darf maximal " . self::$validationConfig['description_max_chars'] . " Zeichen lang sein.",
            );
        }


        // check file
        $element = "message_image";
        if( key_exists($element, $_FILES) && key_exists('size', $_FILES[$element]) ) {
            if( $_FILES[$element]['size'] > self::$validationConfig['image_size_max'] ) {
                $response['error'][] = array(
                    'element'   => $element,
                    'message'   => "Bilder dürfen nicht größer als " . (self::$validationConfig['image_size_max'] / 1000000) . " MB groß sein.",
                );
            }
        }


        // check topics
        $element = "topics";
        $value = explode(',', wp_strip_all_tags($_POST[$element]));
        if(count($value) == 1 && strlen($value[0]) == 0) {
            $response['error'][] = array(
                'element'   => $element,
                'message'   => "Gib bitte mindestens ein Hauptthema an.",
            );
        }

        
        if(key_exists('error', $response)) {
            self::ajaxRespond($response);
            return false;
        }
        
        return true;
    }

    private static function securityCheck() {
        $response = array();
        
        $nonce = NULL;
        if(key_exists("bookboak", $_REQUEST)) {
            $nonce = $_REQUEST["bookboak"];
        }

        if( !isset($nonce) || wp_verify_nonce($nonce, 'kleem-newmsg') != 1 ) {
            $response['securityError'] = array(
                'redirect' => self::$url,
                'message'  => '<div id="securityErrorMessage"><p>Sorry, deine Session ist abgelaufen ... </p><a href="' . self::$url . '">Neue Message Schreiben</a></div>',
            );
        }
        
        if(key_exists('securityError', $response)) {
            header("Content-Type: text/plain");
            echo json_encode($response);
            return false;
        }
        
        return true;
    }
    
    private static function sumitSuccessHTML($postID) {
        $postPermaLink = get_post_permalink($postID, false);
        
        $html = '<div id="submitSuccessMessage">';
        $html .= '<p>Deine Nachricht wurde erfoglreich abgeschickt!</p>';
        $html .= '<div class="redirect thePost"><a href="' . $postPermaLink . '">Meinung ansehen</a></div>';
        $html .= '<div class="redirect newMessage"><a href="' . self::$url . '">Neue Meinung schreiben</a></div>';
        $html .= '</div>';
        
        return $html;
    }
    
    
    public static function ajaxRespond(&$message) {
        header("Content-Type: text/plain");
        echo json_encode($message);
        die();
    }
}

