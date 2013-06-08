<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<article id="rio_message-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <div class="thumbnail">
	        <?php  
	            $thumbnailimage = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
	            if(is_array($thumbnailimage)) {
	                echo '<a class="thickbox" href="' . current($thumbnailimage) . '">';
	                the_post_thumbnail('thumbnail');
	                echo '</a>';
	            } else {
					echo '<img src="' . RIO_DEFAULT_THUMBNAIL . '"/>';
				}
					
	        ?>
	   	</div>
        <div class="rio_topics">
            <?php echo rio_get_the_topic_list(get_the_ID()); ?>
        </div>
        <h1 class="entry-title">
            <?php rio_the_colored_title(true); ?>
        </h1>
        
    </header><!-- .entry-header -->
	<div class="topic-reference">
            <?php echo rio_get_the_reference(get_the_ID()); ?>
    </div>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <footer class="entry-meta">
        <?php 
            $author = get_the_author();
            $autorRef = esc_url( get_author_posts_url( get_the_author_meta( 'ID' )));
            $date = get_the_date();
            $date_c = get_the_date( 'c' );
            $avatar = get_avatar(get_the_author_meta('ID'), 40);
        ?>
        
        
        <a href="<?php echo $autorRef; ?>" rel="author" title="Alle Messages von <?php echo $author; ?> ansehen"> <?php echo $avatar; ?></a>
        <a href="<?php echo $autorRef; ?>" rel="author" title="Alle Messages von <?php echo $author; ?> ansehen"> <?php echo $author; ?></a>
        &nbsp;|&nbsp;<time class="entry-date" datetime="<?php echo $date_c?>" pubdate><?php echo $date ?></time>
    </footer>
    
    
    <footer class="entry-control">
    	<?php if(function_exists('get_twoclick_buttons')) {get_twoclick_buttons(get_the_ID());}?>
        <?php echo rio_get_the_ratingbox(); ?>
        <p class="clearfix" style="clear:both;"></p>
    </footer>
</article>
