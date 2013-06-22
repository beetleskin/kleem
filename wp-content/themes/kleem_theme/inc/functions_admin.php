<?php


add_action('admin_menu', 'add_export_tool_menu_item');


function add_export_tool_menu_item() {
    add_submenu_page('tools.php', 'Meinungen exportieren', 'Meinungen exportieren', 'administrator', 'opinion_export', 'opinion_export_form_cb');
}


function opinion_export_form_cb() {
    $form_param =  "export_opinions";
    
    // handle export request
    if (key_exists($form_param, $_REQUEST)) {
        // a export request was sent
        opinion_export_dl();
        return;
    }


    $form_action = "";
    $form_id = "export_form";
    $form_method = "post";
    
    $form = '<div id="' . $form_id . '_wrap">';
    $form .= '<form action="' . $form_action . '" id="' . $form_id . '" method="' . $form_method . '">';
    $form .= '<input type="submit" name ="' . $form_param . '" value="Meinungen Exportieren">';
    $form .= '</form></div>';

    echo $form;
}


function opinion_export_dl() {
    $query_args = array(
        "post_status" => "publish",
        "post_type" => "opinion",
        "nopaging" => true,
    );
    
    $the_query = new WP_Query($query_args);
    
    $opinions = array();


    while ($the_query -> have_posts()) : $the_query -> the_post();
		$msg_id = get_the_ID();
		$agreement = intval(get_post_meta($msg_id, '_agreement', true));
		$disaffirmation = intval(get_post_meta($msg_id, '_disaffirmation', true));
        $opinions[] = array(
            "title" 		=> get_the_title(),
            "content" 		=> get_the_content(),
            "popularity" 	=> ($agreement+$disaffirmation == 0)? 0 : (($agreement - $disaffirmation) / ($agreement + $disaffirmation)),
            "clicks"		=> kleem_get_post_views($msg_id),
        );
            
    endwhile;

    usort($opinions, "popularity_sort");

    foreach ($opinions as $msg) {
        echo '<div style="margin-top: 30px; font: 1em Georgia, Arial; border-bottom: 1px dashed black;" id="post_export"><p><strong>';
        echo '"' . $msg['title'] . '"';
        echo "</strong></p>";
        echo $msg['content'];
        echo '<p>Beliebtheit: ' . $msg['popularity'] . '</p>';
		echo '<p>Clicks: ' . $msg['clicks'] . '</p>';
        echo "</div>";
    }
}


function popularity_sort(&$a, &$b) {
    return $a["popularity"] < $b["popularity"];
}

?>
