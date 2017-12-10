<?php
/**
 * content-quote.php
 *
 * The default template for displaying posts with the Quote post format.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!-- Article content -->
	<div class="entry-content">
		<?php
			the_content( __( 'Continue reading &rarr;', 'material' ) );

			wp_link_pages();
		?>
	</div> <!-- end entry-content -->

	<!-- Article footer -->
	<footer class="entry-footer">
		<p class="entry-meta">
			<?php 
				// Display the meta information
				material_post_meta();
			?>
		</p>
	</footer> <!-- end entry-footer -->
</article>