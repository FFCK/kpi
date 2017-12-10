<?php
/**
 * footer.php
 *
 * The template for displaying the footer.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>
		</div> <!-- end row -->
	</div> <!-- end container -->

	<!-- FOOTER -->
	<footer class="site-footer">
		<div class="container">
			<?php get_sidebar( 'footer' ); ?>

			<div class="copyright">
				<p>
					&copy; <?php echo date( 'Y' ); ?>
					<a href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ); ?></a>
					&#12288;  
					<a class="author-url" href="http://standew.com/klyment"><?php echo __('Theme Material', 'material'); ?></a>
				</p>
			</div> <!-- end copyright -->
		</div> <!-- end container -->
	</footer> <!-- end site-footer -->
	<?php wp_footer(); ?>
</body>
</html>