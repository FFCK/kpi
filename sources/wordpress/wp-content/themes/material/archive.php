<?php
/**
 * archive.php
 *
 * The template for displaying archive pages.
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
						if ( is_day() ) {
							printf( __( 'Daily Archives for %s', 'material' ), get_the_date() );
						} elseif ( is_month() ) {
							printf( __( 'Monthly Archives for %s', 'material' ), get_the_date( _x( 'F Y', 'Monthly archives date format', 'material' ) ) );
						} elseif ( is_year() ) {
							printf( __( 'Yearly Archives for %s', 'material' ), get_the_date( _x( 'Y', 'Yearly archives date format', 'material' ) ) );
						} else {
							_e( 'Archives', 'material' );
						}
					?>
				</h3>
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