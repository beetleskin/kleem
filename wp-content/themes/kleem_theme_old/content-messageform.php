<?php
/*
 Template Name: Message Form
*/

    global $theme;
    get_header();
    
    if ( have_posts() ) {
        the_post();
    }
    
    $form = new FrontendPostSubmitter();
    $form->enqueue_styles();
    $form->enqueue_scripts();
    $form->printAjaxConfig();
    $form->render();

    get_footer(); 
    
?>


