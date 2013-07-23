				<?php get_header(); ?>
				<div id="main" class="ninecol first clearfix" role="main">

					
					<?php if ( have_posts() ) : ?>
						
						<?php /* teaser slider */ ?>
						<?php echo do_shortcode("[metaslider id=85]"); ?>
						
					    <?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_template_part( 'content', get_post_format() ); ?>
						<?php endwhile; ?>
			
						<?php kleem_ajax_pagination(); ?> 
						
					<?php else : ?>

						<article id="post-0" class="post no-results not-found">
			
							<header class="entry-header">
								<h1 class="entry-title"><?php _e( 'Nothing Found', 'twentytwelve' ); ?></h1>
							</header>
			
							<div class="entry-content">
								<p><?php _e( 'Apologies, but no results were found. Perhaps searching will help find a related post.', 'twentytwelve' ); ?></p>
								<?php get_search_form(); ?>
							</div><!-- .entry-content -->
						</article><!-- #post-0 -->
					<?php endif; // end current_user_can() check ?>
					
					
			
    		</div> <!-- end #main -->
    
			<?php get_sidebar(); ?>
			<?php get_footer(); ?>
