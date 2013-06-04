<?php
/**
 * @package Message-To-Rio
 */

/*
 Plugin Name: Frontend Post Submitter
 Description: Provides the message upload form
 Version: 0.1b
 Author: stfn
 License: GPLv2 or later
 */


add_action('init', 'FrontendPostSubmitter::init', 11);


class FrontendPostSubmitter {

    private $form_action;
    private $form_id;
    private $form_method;
    
    private static $ioConfig;
    private static $validationConfig;
    private static $url;
    

    function __construct() {
        $this -> form_action = "";
        $this -> form_id = "messageform";
        $this -> form_method = "POST";
    }

    function preRender() {
        $data = array();
        
        $parentID = get_term_by( 'slug', 'main-topic', 'rio_topics')->term_id ;
        $query_args = array(
            'parent'        => $parentID,
            'hide_empty'    => 0,
            'order_by'      => 'count'
        );
        
    
        $nopriv = 'nopriv';
        $submitLink = wp_login_url(get_permalink());
        $onClick = '';
		$isLoggedIn = get_current_user_id() != 0;
        
        if($isLoggedIn) {
            $nopriv = '';
            $submitLink = '#';
            $onClick = 'onclick="return false;"';
        }
        
        $data['isLoggedIn'] = $isLoggedIn;
        $data['nopriv'] = $nopriv;
        $data['submitLink'] = $submitLink;
        $data['onClick'] = $onClick;
        $topic_data = get_terms('rio_topics', $query_args);
        $data['topics'] = $topic_data;
		$data['images'] = array(
			"arrow" => plugin_dir_url(__FILE__) . "style/images/sidearrow.png",
			"plus" => plugin_dir_url(__FILE__) . "style/images/sideplus.png"
					
		);  
        return $data;
    }


    function render() {

        $data = $this->preRender();
        ?>
        
        <?php if($data['isLoggedIn'] == false) : ?>
        <div class="entry-content">
            <?php the_content();  ?>
        </div>
        <?php endif; ?>
        <div id="messageform_wrap">   	
		    <form action="<?php echo $this->form_action ?>" id="<?php echo $this->form_id ?>" class="<?php echo $data['nopriv'] ?>" method="<?php echo $this->form_method ?>">
		        <div class="wrap">   
		        	<div id="errormessage"></div>
		        
		      	  	<div class="itemhead obligated"><img src="<?php echo $data ["images"]["arrow"];  ?>"/><h2>Deine Message an Rio</h2></div>
		     	    <div class="itembody">
		        	    <textarea id="message" name="message" placeholder="Deine Nachricht an Rio (kurzgefasst: 300 Zeichen) ..."></textarea>
		        	</div>
		        
			        <div class="itemhead"><img class="additional" src="<?php echo $data ["images"]["plus"];  ?>"/><h2>Erklärungstext hinzufügen</h2></div>
			        <div class="itembody" style="display: none;">
			            <textarea id="description" name="description" placeholder="Du brauchst noch ein paar Sätze um deine Message zu erklären, dann schreibe hier deinen Text (500 Zeichen) ..."></textarea>
			        </div>
		        
		
			        <div class="itemhead"><img class="additional" src="<?php echo $data ["images"]["plus"];  ?>"/><h2>Bild oder Link hinzufügen</h2></div>
			        <div class="itembody" id="input_files_special" style="display: none;">
			        	<input type="file" id="image_upload" name="message_image" accept="image/*">
			        	<div class="progress">
                            <div class="bar"></div>
                            <div class="percent">0%</div>
                        </div>
			        	<input type="text" id="reference_input" name="reference" placeholder="Schreibe hier deinen Link (z.B. http://www.mirantao.de) ...">
			        </div>
		        
		        
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
					       	<div class="sub_topics_container">
					            <h2>Eigene Themen hinzufügen:</h2>
					            <input type="select" id="sub_topics" name="sub_topics" placeholder="Thema hinzufügen ..."/>
					        </div>
					        <p style="clear:both;"></p>
				       	</div>			           
			        </div>		
			    </div>	
	       		<div class="message_submit_container"><a href="<?php echo $data['submitLink'] ?>" <?php echo $data['onClick'] ?> id="message_submit">Abschicken</a></div>
	       		<input id="maxfilesize" type="hidden" name="MAX_FILE_SIZE" value="<?php echo self::$validationConfig['image_size_max'] ; ?>" />	
        	</form>
       </div>
       
        
<?php
}


    public function printAjaxConfig() {
        
        // add security check
        self::$ioConfig['bookboak'] = wp_create_nonce  ('rio-newmsg');
        
        // Print data to sourcecode
        wp_localize_script('messageform-script', 'messageform_config', self::$ioConfig);
        wp_localize_script('messageform-script', 'messageform_validation', self::$validationConfig);
    }


    function enqueue_scripts() {
        
        // multiselect widget
        wp_enqueue_script('jquery-ui-multiselect', plugins_url('script/jquery-ui-multiselect-widget/src/jquery.multiselect.min.js', __FILE__), array('jquery-ui-core', 'jquery-ui-widget'));
        
        // autosuggest
        wp_enqueue_script('autosuggest', plugins_url('script/autoSuggestv14/jquery.autoSuggest.packed.js', __FILE__), array('jquery'));
        
        // jquery-ajaxForm
        wp_deregister_script('jquery-form');
        wp_enqueue_script('jquery-form-new', 'http://malsup.github.com/jquery.form.js', array('jquery'));
        
        // message form script
        wp_enqueue_script('messageform-script', plugins_url('script/messageform.js', __FILE__), array('jquery', 'jquery-form-new', 'autosuggest', 'jquery-ui-multiselect'));
    }
    
    
    function enqueue_styles() {
        // multiselect style
        wp_enqueue_style('jquery-ui-multiselect', plugins_url('script/jquery-ui-multiselect-widget/jquery.multiselect.css', __FILE__));
        
        // jquery-ui style
        wp_enqueue_style('jquery-ui-theme', plugins_url('style/jquery_messageform.css', __FILE__));
        
        // autosuggest style
        wp_enqueue_style('autosuggest', plugins_url('script/autoSuggestv14/autoSuggest.css', __FILE__));
        
        // message form style
        wp_enqueue_style('messageform-style', plugins_url('style/messageform.css', __FILE__), array('jquery-ui-multiselect'));
    }


    public static function init() {
        $wp_handle_upload_error = 'FrontendPostSubmitter::myHandleUploadError';
        
        
        self::$url = home_url("schreiben");
        
        // Check for SSL
        $protocol = (is_ssl()) ? 'https://' : 'http://';

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
            'sub_topics_max'        => 5,
            'image_size_max'        => 5000000,
            'reference_max_chars'   => 1000,
        );
        
        
        
        // form ajax
        add_action('wp_ajax_' . self::$ioConfig['submitAction'], 'FrontendPostSubmitter::submit');
        
        // autocomplete tags
        add_action('wp_ajax_' . self::$ioConfig['suggestTagsAction'], 'FrontendPostSubmitter::getTags');
        add_action('wp_ajax_nopriv_' . self::$ioConfig['suggestTagsAction'], 'FrontendPostSubmitter::getTags');
    }


    public static function submit() {
        if( !self::securityCheck()  || !self::validate() ) {
            die();
        }
        
        $error = null;
        $description = wp_strip_all_tags($_POST['description']);
        $message = wp_strip_all_tags($_POST['message']);
        $reference = wp_strip_all_tags($_POST['reference']);
        $topicIDs = array();
        $query_topics = array_merge(explode(",", $_POST['topics']), array_slice(explode(",", $_POST['sub_topics']), 0, 5));
        
        
        // validate main topic selection
        foreach ($query_topics as $topic) {
            $topicID = intval($topic);
            
            // valid integer, check if ID exists
            if($topicID > 0) {
                if(!get_term_by('id', $topicID, 'rio_topics')) {
                    continue;
                }
                
            // string 
            } else {
                
                // remove all the tags!!!
                $topic = wp_strip_all_tags($topic);
                if(strlen($topic) < 3 || strlen($topic) > 15) {
                    continue;
                }
                
                
                $matching_term = term_exists($topic, 'rio_topics');
                if($matching_term !== NULL) {
                    $topicID = $matching_term['term_id'];
                } else {
                    $new_term = wp_insert_term($topic, 'rio_topics');
                    if( !is_wp_error($new_term)) {
                        $topicID = $new_term['term_id']; 
                    } else {
                        // error("User " . get_current_user_id() . " failed to insert new topic: " . $new_term . " - [" . $new_term->get_error_message() . "]");
                        $error = $new_term->get_error_message();
                    }
                    
                }
            }
             
            if(is_int($topicID)) {
                $topicIDs[] = $topicID;
            }
        }
        
        $post_args = array(
            'ping_status'   => 'open',
            'post_author'   => get_current_user_id(),
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_title'    => $message,
            'post_type'     => 'rio_message',
        );

        $postID = wp_insert_post($post_args);
        if(is_wp_error($postID)) {
            // error($new_term->get_error_message());
            header("HTTP/1.0 500 Internal Server Error");
            die();
        }
        
        
        update_post_meta($postID, 'agreement', 0);
        update_post_meta($postID, 'disaffirmation', 0);
        if(strlen($reference) > 0) {
            update_post_meta($postID, 'reference', $reference);
        }
        if(count($topicIDs) > 0) {
            wp_set_object_terms($postID, $topicIDs, 'rio_topics', true);
        }
        
        
        if( key_exists('message_image', $_FILES) ) {
            $attach_id = media_handle_upload( 'message_image', $postID );
            if(!is_wp_error($attach_id)) {
                update_post_meta( $postID, '_thumbnail_id', $attach_id );
            } else {
                //error($attach_id->get_error_message());
                $error = $postID->get_error_message();
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
            
        
        header("Content-Type: text/plain");
        echo json_encode($response);
        die();
    }


    public static function getTags($param) {
        if( !(self::securityCheck() && key_exists(self::$ioConfig['suggestTagsQueryParam'], $_GET) ) ) {
            die();
        }
        
        $query =  $_GET[self::$ioConfig['suggestTagsQueryParam']];
        
        
        $exclude = array();
        $parent_exclude = get_term_by( 'slug', 'main-topic', 'rio_topics')->term_id;
        $exclude =  get_terms('rio_topics', array('parent' => $parent_exclude, 'hide_empty' => 0, 'fields' => 'ids'));
        $exclude[] = $parent_exclude;
        
        $query_args = array(
            'exclude'       => $exclude,
            'search'        => $query,
            'hide_empty'    => 0,
            'hierarchical'  => 0,
            'fields'        => 'all',
            'order_by'      =>'count'
        );
        
        $matches = get_terms('rio_topics', $query_args);
        
        
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
                'post_type' => 'rio_message',
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


        // check reference
        $element = "reference";
        $value = wp_strip_all_tags($_POST[$element]);
        if(strlen($value) > self::$validationConfig['reference_max_chars']) {
            $response['error'][] = array(
                'element'   => $element,
                'message'   => "Der Link darf maximal " . self::$validationConfig['reference_max_chars'] . " Zeichen lang sein.",
            );
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
            header("Content-Type: text/plain");
            echo json_encode($response);
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

        if( !isset($nonce) || wp_verify_nonce($nonce, 'rio-newmsg') != 1 ) {
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
        $html .= '<div class="redirect thePost"><a href="' . $postPermaLink . '">Message ansehen</a></div>';
        $html .= '<div class="redirect newMessage"><a href="' . self::$url . '">Neue Message Schreiben</a></div>';
        $html .= '</div>';
        
        return $html;
    }
}

