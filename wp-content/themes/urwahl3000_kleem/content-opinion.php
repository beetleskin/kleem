	<article id="opinion-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
		
		<header class="article-header">
			<div class="opinion_topics">
				<?php echo kleem_get_the_topic_list(get_the_ID()); ?>
	 		</div><!-- .opinion_topics -->
	 		<div class="clearfix"></div>
			<?php if (has_post_thumbnail() ): ?>
			<div class="article-thumbnail">
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
			</div><!-- .article-thumbnail -->
			<?php endif; ?>
	        <h1 class="h2">
		    	<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php kleem_the_colored_title(); ?></a>
	    	</h1>
			<p class="byline"><a href="<?php the_permalink(); ?>" title="Permanenter Verweis zu <?php the_title(); ?>"><time class="updated" datetime="<?php echo the_time('c'); ?>"><?php the_time('j. F Y')?>, um <?php the_time('H:i')?> Uhr</time></a> •  <?php comments_popup_link( 'Keine Kommentare', '1 Kommentar', '% Kommentare', 'comments-link', 'Kommentare sind geschlossen'); ?></p>
		</header><!-- .entry-header -->
		
		
		<footer class="article-footer">
		    <?php echo kleem_get_the_commentbox(); ?>
			<?php echo kleem_get_the_ratingbox(); ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
	<div class="clearfix"></div>
