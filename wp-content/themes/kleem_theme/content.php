<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<header class="entry-header">
		<?php if (has_post_thumbnail() ): ?>
			<div class="entry-thumbnail">
			<?php if (is_singular()): ?>
				<a class="thickbox" href="<?php echo current(wp_get_attachment_image_src( get_post_thumbnail_id(), 'full')); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
			<?php else: ?>
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
			<?php endif; ?>
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
		
				
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->

		<footer class="entry-meta">
			<?php twentytwelve_entry_meta(); ?>
			<?php edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
