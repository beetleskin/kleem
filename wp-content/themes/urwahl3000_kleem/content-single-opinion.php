<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>

	<article id="opinion-<?php the_ID(); ?>" <?php post_class(); ?>>
		
		<header class="article-header">
			<div class="opinion_topics">
				<?php echo kleem_get_the_topic_list(get_the_ID()); ?>
	 		</div><!-- .opinion_topics -->
	 		<div class="clearfix"></div>
			<?php if (has_post_thumbnail() ): ?>
				<div class="article-thumbnail">
					<a class="thickbox" rel="bookmark" title="<?php the_title_attribute(); ?>" href="<?php echo current(wp_get_attachment_image_src( get_post_thumbnail_id(), 'full')); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
				</div><!-- .article-thumbnail -->
			<?php endif; ?>
	        <h1 class="h2">
	        	<?php kleem_the_colored_title(true); ?>
	        </h1>
			<p class="byline"><a href="<?php the_permalink(); ?>" title="Permanenter Verweis zu <?php the_title(); ?>"><time class="updated" datetime="<?php echo the_time('c'); ?>"><?php the_time('j. F Y')?>, um <?php the_time('H:i')?> Uhr</time></a> •  <?php comments_popup_link( 'Keine Kommentare', '1 Kommentar', '% Kommentare', 'comments-link', 'Kommentare sind geschlossen'); ?></p>
		</header><!-- .article-header -->
			
			
	    <div class="entry-content clearfix">
	        <?php the_content(); ?>
	    </div><!-- .entry-content -->

	    
	    <footer class="entry-control">
	    	<?php echo kleem_get_the_ratingbox(); ?>
	    	<?php if(function_exists('get_twoclick_buttons')) { get_twoclick_buttons(get_the_ID()); }?>
	    </footer><!-- .entry-control -->
	</article><!-- #post -->
	<div class="clearfix"></div>
