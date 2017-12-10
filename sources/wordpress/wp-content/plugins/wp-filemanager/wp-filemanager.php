<?php
/*
Plugin Name: WP-FileManager
Plugin URI: http://blog.anantshri.info/projects/wp-filemanager/
Description: FileManager for WordPress allows you to easily change, delete, organize and upload files.
Version: 1.4.0
Author: Anant Shrivastava, Johannes Ries
Author URI: http://anantshri.info
*/
/*
Todo list for PHPFM:
-------------------------------------------------------------------------------------

- find work-around for permissions on GNU/Linux & UNIX systems.
- make a login system where each user has his/hers own home directory and permissions.
- some kind of logging system.
- make an install script which makes it easier to install PHPFM.
- add dos2unix or viceversa support
- make hidden files unaccessible by the script (again).
- index with thumbnails of all images in the current directory (uncertain).
- make it possible to change permissions (chmod) on files and directories.
- make it so you can compress a directory and download it.
- do so you can see the full size of a directory (including sub-directories) and how
  many files that are in the directory.
- templates for new (created) files. For instance PHP, HTML etc.
- unix style permissions (e.g. -rw-rw-rw-)
- too long directory- and filenames are shortened so they do not ruin the design.
- templates for PHPFM. Change the look of PHPFM easily! (not provisional)
- more languages.
- add some nifty DHTML?
- add the drive browser again?
- PDF viewer and text/PHP viewer with highlighting.
*/




/* DO NOT EDIT ANYTHING BELOW THIS LINE */
if ( ! defined( 'ABSPATH' ) )
	die();
function get_list_ext($lst_type)
{
	if (get_option($lst_type)  != "")
	{
		$ext_list =  get_option($lst_type);
	}
	else
	{
		$ext_list =  get_option( $lst_type . '_default');
	}
	return $ext_list;
}
function fm_post_add_options() {
	add_menu_page('FileManager', 'FileManager', 8, 'wp-filemanager/fm.php');
	add_submenu_page('wp-filemanager/fm.php','FileManager','Configuration',8,'wpfileman', 'wpfileman_options_admin');
}
function wpfileman_options_admin()
{
//	echo "options panel for wordpress file man";
include_once('conf/config.inc.php');
include_once('wp_filemanager_admin.php');
}
add_action('admin_menu', 'fm_post_add_options');
//add_action('admin_menu', 'wpfileman_admin');
if ($_GET['action'] == 'edit')
{
//	wp_enqueue_script('codepress');
	wp_enqueue_script('jquery');
}
function wp_fileman_rename_file()
{
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/rename.inc.php");
	exit();
}
add_action('wp_ajax_rename', 'rename_file');
add_action("admin_print_scripts",'js_libs');
add_action("admin_print_styles", 'style_libs');
function js_libs() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('thickbox');
} 
function style_libs() {
	wp_enqueue_style('thickbox');
} 	
function wpfileman_download_page(){
    global $pagenow;
//    header("Anant: Hello");
    include_once('conf/config.inc.php');
    //http://localhost/wordpress/wp-admin/admin.php?page=wp-filemanager%2Ffm.php&path=test_create%2F&filename=testme.txt&action=download
    $page = (isset($_GET['page']) ? $_GET['page'] : false);
    $action = (isset($_GET['action']) ? $_GET['action'] : false);
    $filename=(isset($_GET['filename']) ? $_GET['filename'] : false);
    $wp_fileman_path=(isset($_GET['path']) ? $_GET['path'] : false);
    //header("Anant: Hello");
    if($pagenow=='admin.php' && $page=='wp-filemanager/fm.php' && $action=='download' )
    {
        //header("Anant: Hello");
	if (is_file($home_directory.$wp_fileman_path.$filename))
	{
		$fullpath = $home_directory.$wp_fileman_path.$filename;
	}
	//header("Anant2: Hello");    
	//wp_redirect('http://google.com');
	global $MIMEtypes;
	$mime="";
	if (!empty($MIMEtypes))
	{//reset($MIMEtypes);
	$extension = strtolower(substr(strrchr($filename, "."),1));
	if ($extension == "")
		$mime="Unknown/Unknown";
	while (list($mimetype, $file_extensions) = each($MIMEtypes))
		foreach (explode(" ", $file_extensions) as $file_extension)
			if ($extension == $file_extension)
				$mime=$mimetype;
	}
	header("Content-Type: ".$mime);
	header("Content-Length: ".filesize($fullpath));
	header("Content-Disposition: attachment; filename=$filename");
	readfile($fullpath);
    }
}
add_action('admin_init', 'wpfileman_download_page');
/**/
?>
