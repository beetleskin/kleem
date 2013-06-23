<?php

add_filter('wpmem_register_form', 'kleem_adapt_register_form');

function render_init() {
	// enqueue main script
	wp_enqueue_style('thickbox');
    wp_enqueue_script('kleem-opinion-script', get_stylesheet_directory_uri() . '/inc/opinion.js', array('thickbox', 'jquery'));
	wp_enqueue_script('ajaxpaging', get_stylesheet_directory_uri() . '/inc/ajaxpaging.js', array('jquery'));
	
	// hide toolbar ...
	if ( current_user_can('subscriber') ) {
		show_admin_bar(false);	
	}
}



function kleem_the_colored_title($withLinks = false, $beforeWord = "", $afterWord = "", $postID = -1, $inQuotes = true, $echo = true) {
    if($postID === -1) {
        $postID = get_the_ID();
    } 
    
    $title = get_the_title($postID);
    $terms = get_terms( 'opinion_topics' );
    
    
    if(is_wp_error($title) || is_wp_error($terms)) {
        return "";
    }
    
    
    // reorder $terms
    foreach ($terms as $key => $term) {
        $terms[strtolower($term->name)] = $term;
        unset($terms[$key]);
    }
    
    
    // examine each word
    $words = explode(" ", $title);
    foreach ($words as &$word) {
        $escWord = preg_replace('/^[^a-zA-Z0-9]*/','', $word);
        $escWord = preg_replace('/[^a-zA-Z0-9]*$/','', $escWord);
        $lowWord = strtolower($escWord);

        if(array_key_exists($lowWord, $terms)) {
            $term = &$terms[$lowWord];
            
            
            $color = wp_strip_all_tags($term->description);

            if(strlen($color) < 3|| strlen($color) > 10) {
                $color = "";
            }
            
            
            $newWord = "";
            $containerStart = "";
            $containerEnd = "";
            
            if($withLinks === true) {
                $containerStart .= '<a href="' . get_term_link($term) . '" rel="topic"';
                $containerEnd .= '</a>';
            } else {
                $containerStart .= '<span style="color:#1982D1"';
                $containerEnd .= '</span>';
            }
            
            
            if($term->parent != '0') {
                $newWord .= $containerStart . ' class="opinion_topic main_topic" style="color: ' . $color . ';">' . $escWord . $containerEnd;
            } else {
                $newWord .= $containerStart . ' class="opinion_topic user_topic">' . $escWord . $containerEnd;
            }
            $word = str_replace($escWord, $newWord, $word);
        } else {
            $word = $beforeWord . $word . $afterWord;
        }
    }



    // reconstruct and wrap
    $title = implode(" ", $words);
    $before = ($inQuotes === true)? '<span class="quote">„&nbsp;</span>': '';
    $after = ($inQuotes === true)? '<span class="quote">&nbsp;“</span>': '';
    $title = $before . $title . $after;
    
    
    // return
    if($echo === true) {
        echo $title;
    } else {
        return $title;
    }
}



function kleem_get_the_reference($id = 0, $parseYoutube = true) {
    
    $url = get_post_meta($id, 'reference', true);
    if (is_wp_error($url) || strlen($url) == 0)
        return "";
    
    
    if ($parseYoutube === true && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        return '<iframe width="300" height="200" modestbranding="1" rel="0" showinfo="0" controls="0" src="http://www.youtube-nocookie.com/embed/' . $match[1] . '?wmode=transparent" frameborder="0" allowfullscreen></iframe>';
    }


    return '<a target="_blank "href="' . $url . '" title="Message-Quellenangabe"">' . __('Referenz') . '</a>';
}


function kleem_get_the_topic_list($id = 0, $sep = "") {
    $taxonomy = 'opinion_topics';
    $terms = get_the_terms($id, $taxonomy);

    if (is_wp_error($terms))
        return $terms;

    if (empty($terms))
        return false;

    foreach ($terms as $term) {
        $link = get_term_link($term, $taxonomy);
        if (is_wp_error($link))
            return $link;
        $color = wp_strip_all_tags($term->description);
        if(strlen($color) < 3|| strlen($color) > 10) {
            $color = "";
        }
        if($term->parent != '0') {
            $term_links[] = '<div class="opinion_topic_wrap"><a href="' . $link . '" rel="topic" class="main_topic" style="background-color: ' . $color . ';">' . $term->name . '</a></div>';
        } else {
            $term_links[] = '<div class="opinion_topic_wrap"><a href="' . $link . '" rel="topic" class="user_topic">' . $term->name . '</a></div>';
        }
    }

    return join($sep, $term_links);
}


function kleem_get_the_commentbox($postID = 0) {
    if($postID == 0) {
        $postID = get_the_ID();
        
        if(! isset($postID)) {
            return "";
        }
    }
    
    
    $html = "";
    $commentCount = get_comments_number($postID);
    
    
    if ( $commentCount > 0 ) {
        $html .= '<div class="comment_box">';
        $html .= '<a class="comment_number" href="' . get_permalink() . '">' . $commentCount . '</a>';
        $html .= '</div>';
    }
    
    
    return $html;
    
}


function kleem_get_the_ratingbox($postID = 0, $userID = 0) {
    if($postID == 0) {
        $postID = get_the_ID();
        
        if(! isset($postID)) {
            return "";
        }
    }
    
    
    $agreement = intval(get_post_meta($postID, '_agreement', true));
    $disaffirmation = intval(get_post_meta($postID, '_disaffirmation', true));
    $sum = $agreement + $disaffirmation;
    $agreementWidth = 50;
    $disaffirmationWidth = 50;
    if ($sum > 0) {
        $agreementWidth = floor($agreement / $sum * 100);
        $disaffirmationWidth = floor($disaffirmation / $sum * 100);
    }
    
    $agreementChecked = "";
    $disaffirmationChecked = "";
    $nopriv = "nopriv";
    $ratedClass = "";
    $link = wp_login_url(get_permalink());
    $onClick = '';
    
    
    $userID = ($userID == 0)? get_current_user_id() : $userID;
    if($userID == 0) {
        $agreementChecked = "checked";
        $disaffirmationChecked = "checked";
    } else {
        $nopriv = "";
        $onClick = 'onclick="return false;"';
        $link = "#";
        $rated = get_user_meta($userID, 'rated', true);
        if($rated != "" && array_key_exists($postID, $rated)) {
            $ratedClass = 'rated';
            if(intval($rated[$postID]) > 0 ) {
                $agreementChecked = "checked";
            } else {
                $disaffirmationChecked = "checked";
            }
        }
    }
    
    
    
    $html = '<div class="ratingbox ' . $nopriv . ' ' . $ratedClass . '" postID="' . $postID . '">';
    $html .= '<div class="agreement ratebutton ' . $agreementChecked . '">';
    $html .= '<a href="' . $link . '" ' . $onClick . '>Zustimmen</a>';
    $html .= '</div>';
    $html .= '<div class="disaffirmation ratebutton ' . $disaffirmationChecked . '">';
    $html .= '<a href="' . $link . '" ' . $onClick . '>Ablehnen</a>';
    $html .= '</div>';
    $html .= '<div class="rateometer">';
    $html .= '<div class="agreement" style="width:' . $agreementWidth . '%;"></div>';
    $html .= '<div class="disaffirmation" style="width:' . $disaffirmationWidth . '%;"></div>';
    $html .= '<div class="stats"><span class="agreement val">' . $agreement . '</span>&nbsp;Zustimmungen<span class="disaffirmation val">' . $disaffirmation . '</span>&nbsp;Ablehnungen</div>';
    $html .= '</div></div>';
    
    return $html;
}



function kleem_ajax_pagination($readMore = 'Mehr Posts ...', $buttonStyle = 'green') {
    // ajax more posts config
    global $wp_query;
    $maxPages = $wp_query->max_num_pages;
    if ($maxPages <= 1) {
         return;
    }
       
    $clean_query_vars = array();
    foreach ($wp_query->query_vars as $q => &$val) {
        if($val != "") {
            $clean_query_vars[$q] = $val;
        }
    }
    
    $serializedQuery = base64_encode(serialize($clean_query_vars));
    $pagingConfig = array(
        'maxPages'          => $maxPages,
        'query'             => $serializedQuery,
    );
    
    //get the ajax loading animation gif
    $src_path = get_stylesheet_directory_uri() . '/images/ajax-loader.gif';
    
    ?>
    
    <div id="ajax-post-container"></div>
    <div class="ajax_more_posts">
        <a class="large querybutton <?php echo $buttonStyle ?>" id="ajax_pagination_btn" href="#" onclick="return false;"'>
            <span class="_ajax_link_text"><?php echo $readMore ?></span>
       </a>
       <span class="_ajaxpaging_loading" style="display: none;">
           <img src="<?php echo $src_path ?>" alt="Loading.." />
       </span>
    </div>
    <script type="text/javascript">
        var ajax_post_paging_config = <?php echo json_encode($pagingConfig); ?> ;
    </script>
    
    <?php 
}


function kleem_auto_link_text($text) {
   $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
   $callback = create_function('$matches', '
       $url       = array_shift($matches);
       $url_parts = parse_url($url);

       $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
       $text = preg_replace("/^www./", "", $text);

       $last = -(strlen(strrchr($text, "/"))) + 1;
       if ($last < 0) {
           $text = substr($text, 0, $last) . "&hellip;";
       }

       return sprintf(\'<a rel="nofollow" href="%s">%s</a>\', $url, $text);
   ');

   return preg_replace_callback($pattern, $callback, $text);
}


function kleem_posted_on() {
	printf( __( 'Erstellt am <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="byline"> von <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'cazuela'),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s'), get_the_author() ) ),
		get_the_author()
	);
}

function kleem_post_meta() {
	/* get data to be rendered */
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );
	$tax_list = "";
	if($categories_list && $tag_list) {
		$tax_list = $categories_list . ', ' . $tag_list;
	} elseif($categories_list) {
		$tax_list = $categories_list;
	} else {
		$tax_list = $tag_list;
	}
	
	
	if ( $tax_list ) {
		$utility_text = __( 'Tags: %1$s', 'twentytwelve' );
		printf($utility_text, $tax_list);
	}
}


function kleem_adapt_register_form($form) {
	$form = str_replace('<small>Powered by <a href="http://rocketgeek.com" target="_blank">WP-Members</a></small>', '', $form);
	return $form;
}


?>