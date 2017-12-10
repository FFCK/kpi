<?php
/**
 * comments.php
 *
 * The template for displaying comments.
 * @package Theme_Material
 * GPL3 Licensed
 */
?>

<?php 
	// Prevent the direct loading of comments.php.
	if ( ! empty( $_SERVER['SCRIPT-FILENAME'] ) && basename( $_SERVER['SCRIPT-FILENAME'] ) == 'comments.php' ) {
		die( __( 'You cannot access this page directly.', 'material' ) );
	}
?>

<?php 
	// If the post is password protected, display info text and return.
	if ( post_password_required() ) : ?>
		<p>
			<?php 
				_e( 'This post is password protected. Enter the password to view the comments.', 'material' );

				return;
			?>
		</p>
	<?php endif; ?>

<!-- Comments Area -->
<div class="comments-area" id="comments">
	<?php comment_form(); ?>

	<?php if ( have_comments() ) : ?>

		<?php 
			// If the comments are closed, display an info text.
			if ( ! comments_open() && get_comments_number() ) :
		?>
			<h3 class="no-comments">
				<?php _e( 'Comments are closed.', 'material' ); ?>
			</h3>
		<?php else: 
		?>

		<h3 class="comments-title">
			<?php 
				printf( _nx( 'One comment', '%1$s comments', get_comments_number(), 'Comment title', 'material' ), number_format_i18n( get_comments_number() ) );
			?>
		</h3>

		<?php endif; ?>

		<ol class="comments">
			<?php wp_list_comments(); ?>
		</ol>

		<?php 
			// If the comments are paginated, display the controls.
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
		?>
		<nav class="comment-nav" role="navigation">
			<ul class="pager">
			<li class="comment-nav-prev prev">
				<?php previous_comments_link( __( '<i class="fa fa-angle-double-left"></i> Older Comments', 'material' ) ); ?>
			</li>

			<li class="comment-nav-next nxt">
				<?php next_comments_link( __( 'Newer Comments <i class="fa fa-angle-double-right"></i>', 'material' ) ); ?>
			</li>
			</ul><!-- /.pager -->
		</nav> <!-- end comment-nav -->
		<?php endif; ?>


	<?php endif; ?>

	
</div> <!-- end comments-area -->