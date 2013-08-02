
			    
						<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
						 
							<header class="article-header">
								<?php if (has_post_thumbnail() ): ?>
									<div class="article-thumbnail">
									<?php if (is_singular()): ?>
										<a class="thickbox" rel="bookmark" title="<?php the_title_attribute(); ?>" href="<?php echo current(wp_get_attachment_image_src( get_post_thumbnail_id(), 'full')); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
									<?php else: ?>
										<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
									<?php endif; ?>
									</div><!-- .article-thumbnail -->
								<?php endif; ?>
							    <h1 class="h2">
							    	<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						    	</h1>
								<p class="byline"><a href="<?php the_permalink(); ?>" title="Permanenter Verweis zu <?php the_title(); ?>"><time class="updated" datetime="<?php echo the_time('c'); ?>"><?php the_time('j. F Y')?>, um <?php the_time('H:i')?> Uhr</time></a> •  <?php comments_popup_link( 'Keine Kommentare', '1 Kommentar', '% Kommentare', 'comments-link', 'Kommentare sind geschlossen'); ?></p>
							</header><!-- .article-header -->
						
							<?php if (is_singular() || is_sticky()): ?>
							<div class="entry-content clearfix">
								<?php the_content(); ?>
							</div><!-- .entry-content -->
							<?php else: ?>
							<div class="entry-content">
								<?php the_excerpt(); ?>
							</div><!-- .entry-content -->
							<?php endif; ?>
						
							<?php if( has_tag() || has_category() ): ?>
							<footer class="article-footer">
								<p class="tags">
									<?php kleem_post_meta(); ?>
								</p>
							</footer> 
							<?php endif; ?>
						</article>