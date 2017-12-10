<?php
/**
 * sidebar-footer.php
 *
 * The footer sidebar.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
	<div class="row">
    <div class="col-md-12">
    <aside class="footer-sidebar" role="complementary">
	    
			<?php dynamic_sidebar( 'sidebar-2' ); ?>
	     <!-- end row -->
	    
	</aside> <!-- end sidebar -->
	</div>
	</div>
<?php endif; ?>