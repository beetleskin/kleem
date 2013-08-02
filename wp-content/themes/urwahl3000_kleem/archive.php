			<?php get_header(); ?>	
			<div id="main" class="ninecol first clearfix" role="main">
				
					    <?php if ( is_category() ): ?>
						    <div class="archive-title">
							    <h1 class="h2"><?php single_cat_title(); ?></h1>
							    <?php echo category_description(); ?>
						    </div>
					    <?php  elseif (is_tag()): ?> 
						    <h1 class="archive-title h2">
						    	<?php single_tag_title(); ?>
						    </h1>
						<?php  elseif (is_post_type_archive()): ?>
							<div class="archive-title">
							    <h1 class="h2"><?php echo get_post_type_object(get_post_type())->labels->name; ?></h1>
						    </div>
					    <?php elseif (is_author()): ?>
					    	<?php  
						    	global $post;
						    	$author_id = $post->post_author;
					    	?>
						    <h1 class="archive-title h2">
						    	<span><?php echo get_the_author_meta('display_name', $author_id); ?>
						    </h1>
						<?php endif ?>
						
						
					    <?php if (have_posts()): ?>
					
					    		
					    <?php
							while ( have_posts() ) : the_post();
								if( is_custom_post_type() ) {
									get_template_part( 'content', get_post_type() ); 
								} else {
									get_template_part( 'content', get_post_format() );
								}
							endwhile;
							
							kleem_ajax_pagination();
						?>	
					
					
					    <?php else : ?>
					
    					    <article id="post-not-found" class="hentry clearfix">
    						    <header class="article-header">
    							    <h1><?php _e("Oops, Post Not Found!", "kr8theme"); ?></h1>
    					    	</header>
    						    <section class="entry-content">
    							    <p><?php _e("Uh Oh. Something is missing. Try double checking things.", "kr8theme"); ?></p>
        						</section>
    	    					<footer class="article-footer">
    		    				    <p><?php _e("This is the error message in the archive.php template.", "kr8theme"); ?></p>
    			    			</footer>
    				    	</article>
					
					    <?php endif; ?>
			
			
    		</div> <!-- end #main -->
    
			<?php get_sidebar(); ?>
			<?php get_footer(); ?>
