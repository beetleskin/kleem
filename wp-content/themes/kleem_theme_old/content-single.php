<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="thumbnail">
		    <?php  
	            $thumbnailimage = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
	            if(is_array($thumbnailimage)) {
	                echo '<a class="thickbox" href="' . current($thumbnailimage) . '">';
	                the_post_thumbnail('thumbnail');
	                echo '</a>';
	            }
	        ?>
	    </div>
	    <div class="title">
		    <h1 class="entry-title">
	            <?php the_title(); ?>
	        </h1>
		</div>
		<time class="entry-date" datetime="<?php echo get_the_date( 'c' ); ?>" pubdate> <?php echo get_the_date(); ?></time>
	</header>

	<div class="entry-content">
			<?php the_content(); ?>
	</div>
	<footer class="entry-meta">
	</footer>
</article>
