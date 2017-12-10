<?php
/**
 * functions.php
 * Based on Adi Purdila's Alpha WordPress Framework 
 * @package Theme_Material
 * GPL3 Licensed
 */

/**
 * ----------------------------------------------------------------------------------------
 * 1.0 - Define constants.
 * ----------------------------------------------------------------------------------------
 */
define( 'MATERIAL_THEMEROOT', get_stylesheet_directory_uri() );


/**
 * ----------------------------------------------------------------------------------------
 * 2.0 - Load the framework.
 * ----------------------------------------------------------------------------------------
 */
require_once( get_template_directory() . '/framework/widget-social-links.php' );


/**
 * ----------------------------------------------------------------------------------------
 * 3.0 - Set up the content width value based on the theme's design.
 * ----------------------------------------------------------------------------------------
 */
if ( ! isset( $content_width ) ) {
	$content_width = 800;
}


/**
 * ----------------------------------------------------------------------------------------
 * 4.0 - Set up theme default and register various supported features.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_setup' ) ) {
	function material_setup() {
		/**
		 * Make the theme available for translation.
		 */
		$lang_dir = MATERIAL_THEMEROOT . '/languages';
		load_theme_textdomain( 'material', $lang_dir );

		/**
		 * Add support for post formats.
		 */
		add_theme_support( 'post-formats',
			array(
				'gallery',
				'link',
				'image',
				'quote',
				'video',
				'audio'
			)
		);

		/**
		 * Add support for automatic feed links.
		 */
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Add support for post thumbnails.
		 */
		add_theme_support( 'post-thumbnails' );

		/**
		 * Register nav menus.
		 */
		register_nav_menus(
			array(
				'main-menu' => __( 'Main Menu', 'material' )
			)
		);
	}

	add_action( 'after_setup_theme', 'material_setup' );
}


/**
 * ----------------------------------------------------------------------------------------
 * 5.0 - Display meta information for a specific post.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_post_meta' ) ) {
	function material_post_meta() {
		echo '<ul class="list-inline entry-meta">';

		if ( get_post_type() === 'post' ) {
			// If the post is sticky, mark it.
			if ( is_sticky() ) {
				echo '<li class="meta-featured-post"><i class="fa fa-thumb-tack"></i></li>';
			}

			// Get the date.
            
            printf(
				'<li class="meta-date">'.__(' Posted on ','material').'<a href="%1$s" rel="date">%2$s</a></li>',
				esc_url( get_permalink()),
				get_the_date()
			);
            
			// Get the post author.
			printf(
				'<li class="meta-author">'.__(' by ','material').'<a href="%1$s" rel="author">%2$s</a></li>',
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);

		}
	}
}

if ( ! function_exists( 'material_post_meta2' ) ) {
	function material_post_meta2() {
		echo '<ul class="list-inline entry-meta">';

		if ( get_post_type() === 'post' ) {
			
			// The categories.
			$category_list = get_the_category_list( ', ' );
			if ( $category_list ) {
				echo '<li class="meta-categories"> ' . __('Categorized in ','material'). $category_list . ' </li>';
			}

			// The tags.
			$tag_list = get_the_tag_list( '', ', ' );
			if ( $tag_list ) {
				echo '<li class="meta-tags"> ' . __('Tagged with ','material'). $tag_list . ' </li>';
			}

			// Edit link.
			if ( is_user_logged_in() ) {
				echo '<li class="meta-edit"><i class="fa fa-pencil-square-o"></i>';
				edit_post_link( __( 'Edit', 'material' ), '<span class="meta-edit">', '</span>' );
				echo '</li>';
			}

			// Comments link.
			if ( comments_open() ) :
				echo '<li class="pull-right clear-right"><i class="fa fa-comments"></i>';
				echo '<span class="meta-reply">';
				comments_popup_link( __( 'Leave a comment', 'material' ), __( 'One comment so far', 'material' ), __( 'View all % comments', 'material' ) );
				echo '</span>';
				echo '</li>';
			endif;

		}
	}
}


/**
 * ----------------------------------------------------------------------------------------
 * 6.0 - Display navigation to the next/previous set of posts.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_paging_nav' ) ) {
	function material_paging_nav() { ?>
		<ul class="pager">
			<?php 
				if ( get_next_posts_link() ) : ?>
				<li class="prev">
				
					<?php next_posts_link( __( '<i class="fa fa-angle-double-left"></i> Older Posts', 'material' ) ); ?>
				</li>
				<?php endif;
			 ?>
			<?php 
				if ( get_previous_posts_link() ) : ?>
				<li class="nxt">
					<?php previous_posts_link( __( 'Newer Posts <i class="fa fa-angle-double-right"></i>', 'material' ) ); ?>
					
				</li>
				<?php endif;
			 ?>

		</ul> <?php
		/*start php script*/
	}
}


/**
 * ----------------------------------------------------------------------------------------
 * 7.0 - Register the widget areas.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_widget_init' ) ) {
	function material_widget_init() {

		if ( function_exists( 'register_sidebar' ) ) {
			register_sidebar(
				array(
					'name' => __( 'Main Widget Area', 'material' ),
					'id' => 'sidebar-1',
					'description' => __( 'Appears on posts and pages.', 'material' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div> <!-- end widget -->',
					'before_title' => '<h5 class="widget-title">',
					'after_title' => '</h5>',
				)
			);

			register_sidebar(
				array(
					'name' => __( 'Footer Widget Area', 'material' ),
					'id' => 'sidebar-2',
					'description' => __( 'Appears on the footer.', 'material' ),
					'before_widget' => '<div id="%1$s" class="widget col-md-3 %2$s">',
					'after_widget' => '</div> <!-- end widget -->',
					'before_title' => '<h5 class="widget-title">',
					'after_title' => '</h5>',
				)
			);
		}
	}

	add_action( 'widgets_init', 'material_widget_init' );
}

/**
 * ----------------------------------------------------------------------------------------
 * 8.0 - Function that validates a field's length.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_validate_length' ) ) {
	function material_validate_length( $fieldValue, $minLength ) {
		// First, remove trailing and leading whitespace
		return ( strlen( trim( $fieldValue ) ) > $minLength );
	}
}


/**
 * ----------------------------------------------------------------------------------------
 * 9.0 - Include the generated CSS in the page header.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_load_wp_head' ) ) {
	
	function material_load_wp_head() {
		
		  $banner = get_template_directory_uri()."/images/banner.png";
		// Add theme support for Custom Header
		$header_args = array(
			'default-image'          => $banner,
			'default-text-color'     => 'fff',
			'header-text'			 => false,
			'flex-width'			 => true,
			'width'					 => '1920',
			'height'				 => '150',
			'uploads'                => true,
			'admin-preview-callback' => 'material_admin_header_image_preview',
		);
        
        $background_args = array(
              'default-color'          => 'eee',
//            'default-image'          => '',
//            'default-repeat'         => '',
//            'default-position-x'     => '',
//            'default-attachment'     => '',
//            'wp-head-callback'       => '_custom_background_cb',
//            'admin-head-callback'    => '',
//            'admin-preview-callback' => ''
        );

		function material_admin_header_image_preview(){ 
			
			$header_image = get_header_image();
			
			if ( ! empty( $header_image ) ) : ?>

			<div id="headimg" style="height: 100px; margin-bottom:-70px; background: #428bca url('<?php header_image(); ?>') no-repeat left top">
			<?php else : ?>

			<div id="headimg" style="height: 100px; margin-bottom:-70px; background: #428bca url('<?php echo $banner; ?>') no-repeat left top">	
			<?php endif; ?>

			</div>

			<h1 style="color:#fff;padding-left:10px;font-size:36px;"> <?php bloginfo( 'name' ); ?> </h1>

			 <?php
		}


			add_theme_support( 'custom-header', $header_args );
            add_theme_support( 'custom-background', $background_args );



		}
	
	add_action( 'after_setup_theme', 'material_load_wp_head' );

}

/**
 * ----------------------------------------------------------------------------------------
 * 10.0 - Load the custom scripts and stylesheets for the theme.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'material_scripts' ) ) {
	function material_scripts() {
		// Adds support for pages with threaded comments (reply form for desktop)
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Load scripts
		wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/javascripts/bootstrap.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'material-custom', get_template_directory_uri() . '/javascripts/main.js', array( 'jquery' ), false, true );

		// Load the stylesheets
		wp_enqueue_style( 'material-custom', MATERIAL_THEMEROOT . '/stylesheets/styles.css' );
        wp_enqueue_style( 'material-main', get_stylesheet_uri() );
	}

	add_action( 'wp_enqueue_scripts', 'material_scripts' );
}

// add ie conditional html5 shim to header
if ( ! function_exists( 'material_h5_script' ) ) {
    function material_h5_script() {
        echo '<!--[if lt IE 9]>';
        echo '<script src="' . get_template_directory_uri() . '/javascripts/html5shiv.js"></script>';
        echo '<![endif]-->';
}
    add_action('wp_head', 'material_h5_script');
}
    
/**
 * 11.0
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
if ( ! function_exists( 'material_name_wp_title' ) ) {
	function material_name_wp_title( $title, $sep ) {
		if ( is_feed() ) {
			return $title;
		}
		
		global $page, $paged;

		// Add the blog name
		$title .= get_bloginfo( 'name', 'display' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary:
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );
		}

		return $title;
	}
	add_filter( 'wp_title', 'material_name_wp_title', 10, 2 );
}

/**
 * ----------------------------------------------------------------------------------------
 * 10.0 - Modify TinyMCE visual editor.
 * ----------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'material_editor_styles' ) ) {

	function material_editor_styles() {
	    add_editor_style( MATERIAL_THEMEROOT . '/stylesheets/editor.css' );
	}
	
	add_action( 'after_setup_theme', 'material_editor_styles' );
}


/**
 * ----------------------------------------------------------------------------------------
 * Fonctions personnelles Laurent
 * Ajout scripts et styles personnalisés
 * ----------------------------------------------------------------------------------------
 */

function lolo_scripts() {
    wp_register_script('handle', '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js', false, null, false);
    wp_register_script('handle2', '//cdn.datatables.net/responsive/1.0.6/js/dataTables.responsive.min.js', false, null, false);
    wp_enqueue_script('handle'); 
    wp_enqueue_script('handle2');
}
add_action('wp_enqueue_scripts', 'lolo_scripts', 100);

function lolo_styles() {
    wp_register_style( 'my_style', '//cdn.datatables.net/1.10.7/css/jquery.dataTables.css' );
    wp_register_style( 'my_style2', '//cdn.datatables.net/responsive/1.0.6/css/dataTables.responsive.css' );
    wp_enqueue_style( 'my_style' );
    wp_enqueue_style( 'my_style2' );
}
add_action('wp_enqueue_scripts','lolo_styles');









?>