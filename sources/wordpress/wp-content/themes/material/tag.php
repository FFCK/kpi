<?php
/**
 * tag.php
 *
 * The template for displaying tag pages.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php get_header(); ?>

	<div class="main-content col-md-9" role="main">
		<?php if ( have_posts() ) : ?>
			<header class="page-header">
				<h3>
					<?php 
						printf( __( 'Tag Archives for %s', 'material' ), single_tag_title( '', false ) );
					?>
				</h3>

				<?php 
					// Show an optional tag description.
					if ( tag_description() ) {
						echo '<p>' . tag_description() . '</p>';
					}
				?>
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