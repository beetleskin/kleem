<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>
    	<div id="primary">
    		<div id="content" role="main">
    
    			<?php while ( have_posts() ) : the_post(); ?>
    			    
    			    <?php setPostViews(get_the_ID()); ?>
    			    
    				<?php get_template_part( 'content', 'single-rio_message' ); ?>
    
    				<?php comments_template( '', true ); ?>
    
    			<?php endwhile; // end of the loop. ?>
    
    		</div><!-- #content -->
    	</div><!-- #primary -->
<?php get_footer(); ?>