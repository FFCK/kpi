<?php
/**
 * WPFrame
 * A simple framework to make WP Plugin development easier.
 */

$GLOBALS['wpframe_home'] = get_option('siteurl');
$GLOBALS['wpframe_wordpress'] = $GLOBALS['wpframe_siteurl'] = get_option('siteurl');
if(!$GLOBALS['wpframe_wordpress']) $GLOBALS['wpframe_wordpress'] = $GLOBALS['wpframe_home'];
$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_siteurl'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];
//$GLOBALS['wpframe_plugin_data'] = get_plugin_data($GLOBALS['wpframe_plugin_name'] . '.php');
//* :DEBUG: */ $GLOBALS['wpdb']->show_errors();

if(!function_exists('wpframe_add_editor_js')) { //Make sure multiple plugins can be created using WPFrame

/// Adds the JS code needed for the editor. Changes often. So made it centralized
function wpframe_add_editor_js() {
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('add_thickbox')) add_thickbox();
	wp_print_scripts('media-upload');
	if (function_exists('wp_tiny_mce')) wp_tiny_mce();
	wp_admin_css();
	wp_enqueue_script('utils');
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
}

/// Make sure that the user do not call this file directly - forces the use of the WP interface
function wpframe_stop_direct_call($file) {
	if(preg_match('#' . basename($file) . '#', $_SERVER['PHP_SELF'])) die(__('Don\'t call this page directly.', 'mtouchquiz')); // Stop direct call
}

/// Shows a message in the admin interface of Wordpress
function wpframe_message($message, $type='updated') {
	if($type == 'updated') $class = 'updated fade';
	elseif($type == 'error') $class = 'updated error';
	else $class = $type;
	
	print '<div id="message" class="'.$class.'"><p>' . __($message, 'mtouchquiz') . '</p></div>';
}

/// Globalization function - Returns the translated string
//function t($message) {
//	return __($message, 'mtouchquiz');
//}

/// Globalization function - prints the translated string
//function e($message) {
//	_e($message, 'mtouchquiz');
//}

}