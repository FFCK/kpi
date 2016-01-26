<?php get_header(); ?>
<?php get_sidebar(); ?>
<div id="content">
	<div class="post post_singular">
		<h2 class="title title_page"><?php _e('Error 404 - Not Found', 'chocolate'); ?></h2>
		<div class="entry">
			<p><?php _e('Sorry, but you are looking for something that isn&#8217;t here.', 'chocolate'); ?></p>
			<h3><?php _e('Try searching', 'chocolate'); ?></h3>
			<?php get_search_form(); ?>
			<h3><?php _e('Random Posts', 'chocolate'); ?></h3>
			<ul>
				<?php
					$rand_posts = get_posts('numberposts=5&orderby=rand');
					foreach( $rand_posts as $post ) :
				?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<h3><?php _e('Tag Cloud', 'chocolate'); ?></h3>
			<?php wp_tag_cloud('smallest=9&largest=22&unit=pt&number=200&format=flat&orderby=name&order=ASC');?>
		</div><!--entry-->
	</div><!--post-->
</div><!--content-->
<?php get_footer(); ?>