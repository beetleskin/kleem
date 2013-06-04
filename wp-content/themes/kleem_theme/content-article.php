<?php
/**
 * The default template for displaying content
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="entry-header">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">
                <div class="thumbnail">
                	<?php the_post_thumbnail(array(100, 100)); ?>
                </div>
                <div class="title">
	                <h1 class="entry-title">
	                    <?php the_title(); ?>
	                </h1>
            		<time class="entry-date" datetime="<?php echo get_the_date( 'c' ); ?>" pubdate> <?php echo get_the_date(); ?></time>
	            </div>
	            <div class="summary">
	                <div class="entry-summary">
	     		       <?php the_excerpt(); ?>
	    		    </div><!-- .entry-summary -->
    		    </div>
            </a>
        </header><!-- .entry-header -->

        <footer class="entry-meta">
        </footer><!-- #entry-meta -->
    </article><!-- #post-<?php the_ID(); ?> -->
