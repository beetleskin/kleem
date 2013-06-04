<?php



add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
    add_submenu_page('tools.php', 'Rio-Nachrichten exportieren', 'Rio-Nachrichten exportieren', 'administrator', 'rio_tool_export', 'rio_tool_export_form_cb');
}

function rio_tool_export_form_cb() {
    $form_param =  "export_rio_messages";
    
    
    // handle export request
    if (key_exists($form_param, $_REQUEST)) {
        // a export request was sent
        rio_tool_export_dl();
        return;
    }


    $form_action = "";
    $form_id = "export_form";
    $form_method = "post";
    
    $form = '<div id="' . $form_id . '_wrap">';
    $form .= '<form action="' . $form_action . '" id="' . $form_id . '" method="' . $form_method . '">';
    $form .= '<input type="submit" name ="' . $form_param . '" value="Nachrichten Exportieren">';
    $form .= '</form></div>';

    echo $form;
}

function rio_tool_export_dl() {
    $query_args = array(
        "post_status" => "publish",
        "post_type" => "rio_message",
        "nopaging" => true,
    );
    
    $the_query = new WP_Query($query_args);
    
    $rio_messages = array();


    while ($the_query -> have_posts()) : $the_query -> the_post();
        $rio_messages[] = array(
            "title" => get_the_title(),
            "content" => get_the_content(),
            "popularity" => intval(get_post_meta(get_the_ID(), 'agreement', true)) - intval(get_post_meta(get_the_ID(), 'disaffirmation', true)),
        );
            
    endwhile;

    usort($rio_messages, "rio_popularity_sort");

    foreach ($rio_messages as $msg) {
        echo '<div style="margin-top: 30px; font: 1em Georgia, Arial; border-bottom: 1px dashed black;" id="post_export"><p><b>';
        echo '"' . $msg['title'] . '"';
        echo "</b></p>";
        echo $msg['content'];
        echo "</div>";
    }
}

function rio_popularity_sort(&$a, &$b) {
    return $a["popularity"] < $b["popularity"];
}

?>
