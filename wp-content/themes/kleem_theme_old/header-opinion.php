<?php
/**
 * Template part for displaying post header.
 *
 * @package Cazuela
 * @since Cazuela 1.0
 */
?>
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
        <?php echo rio_get_the_topic_list(get_the_ID()); ?>
    </div><!-- .opinion_topics -->
    
    <h1 class="entry-title">
        <?php rio_the_colored_title(true); ?>
    </h1><!-- .entry-title -->
	
</header><!-- .entry-header -->
<header class="entry-header">
	 	<div class="thumbnail">
		     <a href="<?php the_permalink(); ?>">
		         <?php  
		            $thumbnailimage = wp_get_attachment_image_src( get_post_thumbnail_id(), array(100,100));
		            if(is_array($thumbnailimage)) {
		                the_post_thumbnail('thumbnail');
		            } else {
						echo '<img src="' . RIO_DEFAULT_THUMBNAIL . '"/>';
					}
		        ?>
	        </a>
        </div>
        <div class="tags">
	        <div class="opinion_topics">
				<?php echo rio_get_the_topic_list(get_the_ID()); ?>
	 		</div>
	 	</div>
	 	<div class="text">
		     <a href="<?php the_permalink(); ?>">
		        <h1 class="entry-title">
		            <?php rio_the_colored_title(false/*, '<a href="' . get_permalink() .'">', '</a>'*/); ?>
		        </h1>
	        </a>
	    </div>
        <div class="clearfix"></div>
    </header>