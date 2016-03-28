<?php
/**
 * author.php
 *
 * The template for displaying author archive pages.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php get_header(); ?>

	<div class="main-content col-md-9" role="main">
		<?php if ( have_posts() ) : the_post(); ?>
			<header class="page-header">
				<h3>
					<?php printf( __( 'All posts by %s.', 'material' ), get_the_author() ); ?>
				</h3>

				<?php 
					// If the author bio exists, display it.
					if ( get_the_author_meta( 'description' ) ) {
						echo '<p>' . the_author_meta( 'description' ) . '</p>';
					}
				?>

				<?php rewind_posts(); ?>
			</header>

			<?php while( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php material_paging_nav(); ?>
		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>
	</div> <!-- end main-content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>