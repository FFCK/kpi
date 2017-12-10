<?php
/**
 * content-link.php
 *
 * The default template for displaying posts with the Link post format.
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

		<?php 
			// If we have a single page and the author bio exists, display it
			if ( is_single() && get_the_author_meta( 'description' ) ) {
				echo '<h2>' . __( 'Written by ', 'material' ) . get_the_author() . '</h2>';
				echo '<p>' . the_author_meta( 'description' ) . '</p>';
			}
		?>
	</footer> <!-- end entry-footer -->
</article>