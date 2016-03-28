<?php
/**
 * single.php
 *
 * The template for displaying single posts.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php get_header(); ?>

	<div class="main-content col-md-9" role="main">
		<?php while( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'content', get_post_format() ); ?>

			<?php comments_template(); ?>
		<?php endwhile; ?>
	</div> <!-- end main-content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>