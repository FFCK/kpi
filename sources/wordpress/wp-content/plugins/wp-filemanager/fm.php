<?php
/* DO NOT CHANGE ANYTHING HERE */
if ( ! defined( 'ABSPATH' ) )
	die();

require_once('../wp-config.php');
$title = 'FileManager';
define("VERSION", "1.4.0");
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/conf/config.inc.php");
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/functions.inc.php");
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/lang/$language.inc.php");
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/header.inc.php");
include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/html.header.inc.php");
/* register directory/filename */

if (isset($_GET['directory_name']))
{
    $directory_name = basename(stripslashes($_GET['directory_name']))."/";
}
if (isset($_GET['filename']))
{
    $filename = basename(stripslashes($_GET['filename']));
}
if (isset($_POST['directory_name']))
{
    $directory_name = basename(stripslashes($_POST['directory_name']))."/";
}
if (isset($_POST['filename']))
{
    $filename = basename(stripslashes($_POST['filename']));
}
if (isset($_POST['new_directory_name']))
{
    $new_directory_name = basename(stripslashes($_POST['new_directory_name']))."/";
}
if (isset($_POST['new_filename']))
{
    $new_filename = basename(stripslashes($_POST['new_filename']));
}
/* validate path */
if (isset($_GET['path']))
    $wp_fileman_path = wp_fileman_validate_path($_GET['path']);
else if (isset($_POST['path']))
    $wp_fileman_path = wp_fileman_validate_path($_POST['path']);


if (!isset($wp_fileman_path) || $wp_fileman_path == "./" || $wp_fileman_path == ".\\" || $wp_fileman_path == "/" || $wp_fileman_path == "\\")
    $wp_fileman_path = false;

if (isset($_SESSION['session_username']) && $_SESSION['session_username'] == $username && isset($_SESSION['session_password']) && $_SESSION['session_password'] == md5($password) || !$phpfm_auth)
{
    if (!(@opendir($home_directory.$wp_fileman_path)) || (substr($home_directory, -1) != "/"))
    {
        print "<table class='output' width=400 cellpadding=0 cellspacing=0>";
        print "<tr><td align='center'>";

        if (!(@opendir($home_directory)))
            print "<font color='#CC0000'>$StrInvalidHomeFolder</font>";
        else if (!(@opendir($home_directory.$wp_fileman_path)))
            print "<font color='#CC0000'>$StrInvalidPath</font>";
        if (substr($home_directory, -1) != "/")
            print "&nbsp;<font color='#CC0000'>$StrMissingTrailingSlash</font>";

        print "</td></tr>";
    print "</table><br />";
    }
    if (isset($_GET['action']) && is_file(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/".$_GET['action'].".inc.php") && wp_fileman_is_valid_name($_GET['action']))
	{
        include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/".basename($_GET['action']).".inc.php");
	}
    else if (isset($_GET['output']) && is_file(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/".$_GET['output'].".inc.php") && wp_fileman_is_valid_name($_GET['output']))
    {
        print "<table class='output' width=400 cellpadding=0 cellspacing=0>";
            print "<tr><td align='center'>";
                include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/".basename($_GET['output']).".inc.php");
            print "</td></tr>";
        print "</table><br />";
        include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/filebrowser.inc.php");
    }
    else
    {
        include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/filebrowser.inc.php");
    }
}
else
{
    include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/login.inc.php");
}
//include(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/footer.inc.php");
?>
