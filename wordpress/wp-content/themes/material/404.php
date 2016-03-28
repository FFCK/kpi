<?php
/**
 * 404.php
 *
 * The template for displaying 404 pages (Not Found).
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php get_header(); ?>
	<div class="main-content col-md-12" role="main">
	<div class="container-404">
		<h1><?php _e( 'Error 404 - Nothing Found', 'material' ); ?></h1>

		<p><?php _e( 'It looks like nothing was found here. Maybe try a search?', 'material' ); ?></p>

		<?php get_search_form(); ?>
	</div> <!-- end container-404 -->
	</div>
<?php get_footer(); ?>