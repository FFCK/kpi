<?php
/**
 * sidebar.php
 *
 * The primary sidebar.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<aside class="sidebar col-md-3" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside> <!-- end sidebar -->
<?php endif; ?>