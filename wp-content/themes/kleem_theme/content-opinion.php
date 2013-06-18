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
		
		<header class="entry-header">
			<?php if (has_post_thumbnail() ): ?>
			<div class="entry-thumbnail">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
			</div><!-- .entry-thumbnail -->
			<?php endif; ?>
	        <div class="opinion_topics">
				<?php echo kleem_get_the_topic_list(get_the_ID()); ?>
	 		</div><!-- .opinion_topics -->
		     <a href="<?php the_permalink(); ?>">
		        <h1 class="entry-title">
		            <?php kleem_the_colored_title(); ?>
		        </h1>
	        </a>
		</header><!-- .entry-header -->
		
		
		<footer class="entry-meta">
		    <?php echo kleem_get_the_commentbox(); ?>
			<?php echo kleem_get_the_ratingbox(); ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
