<?php // Widgetized Sidebar.
function chocolate_widgets_init() {
	register_sidebar(array(
		'name' => __('Primary Widget Area','chocolate'),
		'id' => 'primary-widget-area',
		'description' => __('The primary widget area','chocolate'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
}
add_action( 'widgets_init', 'chocolate_widgets_init' );

// Custom Comments List.
function chocolate_mytheme_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php echo get_avatar($comment,$size='40',$default='' ); ?>
			<cite class="fn"><?php comment_author_link(); ?></cite>
			<span class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()); ?></a> <?php edit_comment_link(__('[edit]','chocolate'),' ',''); ?></span>
		</div>
		<?php if ($comment->comment_approved == '0') : ?>
		<em class="approved"><?php _e('Your comment is awaiting moderation.','chocolate'); ?></em>
		<br />
		<?php endif; ?>
		<div class="comment-text">
			<?php comment_text(); ?>
		</div>
		<div class="reply">
			<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
		</div>
	</div>
<?php }

if ( ! isset( $content_width ) ) $content_width = 650;
	
// Add default posts and comments RSS feed links to head
add_theme_support( 'automatic-feed-links' );

// This theme styles the visual editor with editor-style.css to match the theme style.
add_editor_style();

// This theme allows users to set a custom background
add_custom_background();
	
// This theme uses post thumbnails
add_theme_support( 'post-thumbnails' );
add_image_size( 'extra-featured-image', 650, 100, true );
function chocolate_featured_content($content) {
	if (is_home() || is_archive()) {
		the_post_thumbnail( 'extra-featured-image' );
	}
	return $content;
}
add_filter( 'the_content', 'chocolate_featured_content',1 );
function my_post_image_html( $html, $post_id, $post_image_id ) {
	$html = '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_post_field( 'post_title', $post_id ) ) . '">' . $html . '</a>';
	return $html;
}
add_filter( 'post_thumbnail_html', 'my_post_image_html', 10, 3 );

// WP nav menu
if (function_exists('wp_nav_menu')) {
register_nav_menus(array('primary' => 'Primary Navigation'));
}

// LOCALIZATION
load_theme_textdomain('chocolate', get_template_directory() . '/lang');

// excerpt
function chocolate_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'chocolate_excerpt_length' );

function chocolate_continue_reading_link() {
	return '<p class="read-more"><a href="'. get_permalink() . '">' . __( 'Continue reading &raquo;', 'chocolate' ) . '</a></p>';
}

function chocolate_auto_excerpt_more( $more ) {
	return ' &hellip;' . chocolate_continue_reading_link();
}
add_filter( 'excerpt_more', 'chocolate_auto_excerpt_more' );

function chocolate_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= chocolate_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'chocolate_custom_excerpt_more' );

//Related Posts function
if (!function_exists('chocolate_related_posts')) :
function chocolate_related_posts($post_num=5) { //Code by willin, edit by zwwooooo.
	global $post;
	$exclude_id = $post->ID;
	$posttags = get_the_tags(); $i = 0;
	if ( $posttags ) {
		$tags = ''; foreach ( $posttags as $tag ) $tags .= $tag->term_id . ',';
		$args = array(
			'post_status' => 'publish',
			'tag__in' => explode(',', $tags),
			'post__not_in' => explode(',', $exclude_id),
			'ignore_sticky_posts' => 1,
			'orderby' => 'comment_date',
			'posts_per_page' => $post_num
		);
		query_posts($args);
		while( have_posts() ) { the_post(); ?>
			<li><a rel="bookmark" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php echo get_the_title(); ?></a></li>
		<?php
			$exclude_id .= ',' . $post->ID; $i++;
		} wp_reset_query();
	}
	if ( $i < $post_num ) {
		$cats = ''; foreach ( get_the_category() as $cat ) $cats .= $cat->cat_ID . ',';
		$args = array(
			'category__in' => explode(',', $cats),
			'post__not_in' => explode(',', $exclude_id),
			'ignore_sticky_posts' => 1,
			'orderby' => 'comment_date',
			'posts_per_page' => $post_num - $i
		);
		query_posts($args);
		while( have_posts() ) { the_post(); ?>
			<li><a rel="bookmark" href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></li>
		<?php
			$i++;
		} wp_reset_query();
	}
	if ( $i == 0 )  echo '<li>'.__('No related posts!','chocolate').'</li>';
}
endif;

// Theme Options
function chocolate_theme_options_items(){
	$items = array (
		array(
			'id' => 'logo_src',
			'name' => __('Logo image', 'chocolate'),
			'desc' => __('Put your logo image address here (max size: 280px*100px). If empty, display blog title with text.', 'chocolate')
		),
		array(
			'id' => 'rss_url',
			'name' => __('RSS URL', 'chocolate'),
			'desc' => __('Put your full rss subscribe address here.(with http://). If empty, auto-set system defaults.', 'chocolate')
		),
		array(
			'id' => 'excerpt_check',
			'name' => __('Excerpt?', 'chocolate'),
			'desc' => __('If the home page and archive pages to display excerpt of post, check.', 'chocolate')
		),
		array(
			'id' => 'related_posts',
			'name' => __('Display Related Posts?', 'chocolate'),
			'desc' => __('If you need to display related posts in the bottom of the article, check.', 'chocolate')
		),
		array(
			'id' => 'comment_notes',
			'name' => __('Disable the comment notes','chocolate'),
			'desc' => __('Disabling this will remove the note text that displays with more options for adding to comments (html).', 'chocolate')
		)
	);
	return $items;
}

add_action( 'admin_init', 'chocolate_theme_options_init' );
add_action( 'admin_menu', 'chocolate_theme_options_add_page' );
function chocolate_theme_options_init(){
	register_setting( 'chocolate_options', 'chocolate_theme_options', 'chocolate_options_validate' );
}
function chocolate_theme_options_add_page() {
	add_theme_page( __( 'Theme Options' ), __( 'Theme Options' ), 'edit_theme_options', 'theme_options', 'chocolate_theme_options_do_page' );
}
function chocolate_default_options() {
	$options = get_option( 'chocolate_theme_options' );
	foreach ( chocolate_theme_options_items() as $item ) {
		if ( ! isset( $options[$item['id']] ) ) {
			$options[$item['id']] = '';
		}
	}
	update_option( 'chocolate_theme_options', $options );
}
add_action( 'init', 'chocolate_default_options' );
function chocolate_theme_options_do_page() {
	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false;
?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . sprintf( __( '%1$s Theme Options', 'chocolate' ), get_current_theme() )	 . "</h2>"; ?>
		<?php if ( false !== $_REQUEST['updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'chocolate' ); ?></strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'chocolate_options' ); ?>
			<?php $options = get_option( 'chocolate_theme_options' ); ?>
			<table class="form-table">
			<?php foreach (chocolate_theme_options_items() as $item) { ?>
				<?php if ($item['id'] == 'excerpt_check' || $item['id'] == 'related_posts' || $item['id'] == 'comment_notes') { ?>
				<tr valign="top" style="margin:0 10px;">
					<th scope="row"><?php echo $item['name']; ?></th>
					<td>
						<input name="<?php echo 'chocolate_theme_options['.$item['id'].']'; ?>" type="checkbox" value="true" <?php if ( $options[$item['id']] ) { $checked = "checked=\"checked\""; } else { $checked = ""; } echo $checked; ?> />
						<label class="description" for="<?php echo 'chocolate_theme_options['.$item['id'].']'; ?>"><?php echo $item['desc']; ?></label>
					</td>
				</tr>
				<?php } else { ?>
				<tr valign="top" style="margin:0 10px;">
					<th scope="row"><?php echo $item['name']; ?></th>
					<td>
						<input name="<?php echo 'chocolate_theme_options['.$item['id'].']'; ?>" type="text" value="<?php if ( $options[$item['id']] != "") { echo $options[$item['id']]; } else { echo ''; } ?>" size="80" />
						<br/>
						<label class="description" for="<?php echo 'chocolate_theme_options['.$item['id'].']'; ?>"><?php echo $item['desc']; ?></label>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'chocolate' ); ?>" />
			</p>
		</form>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="max-width:780px;">
			<div class="wrap" style="background:#DCEEFC; margin-bottom:1em;">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><strong><?php _e('Donation','chocolate'); ?></strong></th>
							<td>
								<?php _e('If you feel my work is useful and want to support the development of more free resources, you can donate me. Thank you very much!', 'chocolate'); ?>
								<br />
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
								">
								<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
<?php
}
function chocolate_options_validate($input) {
	foreach ( chocolate_theme_options_items() as $item ) {
		$input[$item['id']] = wp_filter_nohtml_kses($input[$item['id']]);
	}
	return $input;
}