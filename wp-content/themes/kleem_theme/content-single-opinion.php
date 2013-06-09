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
			<a class="thickbox" href="<?php echo current(wp_get_attachment_image_src( get_post_thumbnail_id(), 'full')); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
		</div><!-- .entry-thumbnail -->
		<?php endif; ?>
        <div class="opinion_topics">
			<?php echo kleem_get_the_topic_list(get_the_ID()); ?>
 		</div><!-- .opinion_topics -->
        <h1 class="entry-title">
        	<?php kleem_the_colored_title(true); ?>
        </h1>
		</header><!-- .entry-header -->
		
		
		<div class="opinion-reference">
            <?php echo kleem_get_the_reference(get_the_ID()); ?>
    	</div><!-- .opinion-reference -->
	    <div class="entry-content">
	        <?php the_content(); ?>
	    </div><!-- .entry-content -->

	    <footer class="entry-meta">
	        <?php 
	            $author = get_the_author();
	            $autorRef = esc_url( get_author_posts_url( get_the_author_meta( 'ID' )));
	            $date = get_the_date();
	            $date_c = get_the_date( 'c' );
	        ?>
	        <a href="<?php echo $autorRef; ?>" rel="author" title="Alle Meinungen von <?php echo $author; ?> ansehen"> <?php echo $author; ?></a>
	        &nbsp;|&nbsp;<time class="entry-date" datetime="<?php echo $date_c?>" pubdate><?php echo $date ?></time>
	    </footer><!-- .entry-meta -->
	    
	    
	    <footer class="entry-control">
	    	<?php if(function_exists('get_twoclick_buttons')) {get_twoclick_buttons(get_the_ID());}?>
	        <?php echo kleem_get_the_ratingbox(); ?>
	    </footer><!-- .entry-control -->
	</article><!-- #post -->
