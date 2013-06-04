<?php
/**
 * The default template for displaying content
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<article id="rio_message-<?php the_ID(); ?>" <?php post_class(); ?>>
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
	        <div class="rio_topics">
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
	<div class="clearfix"></div>
	<footer class="entry-control">
	    <?php echo rio_get_the_commentbox(); ?>
		<?php echo rio_get_the_ratingbox(); ?>
		<p class="clearfix" style="clear:both;"></p>
	</footer>
</article>