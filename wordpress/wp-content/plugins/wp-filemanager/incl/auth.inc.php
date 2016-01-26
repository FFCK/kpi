<?php
if ( ! defined( 'ABSPATH' ) )
	die();
if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/conf/config.inc.php"))
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/conf/config.inc.php");

ini_set('magic_quotes_gpc', 1);
ini_set('session.use_trans_sid', 0);

if (isset($session_save_path)) session_save_path($session_save_path);
session_start();

if (isset($_SESSION['session_username']) && $_SESSION['session_username'] == $username && isset($_SESSION['session_password']) && $_SESSION['session_password'] == md5($password) || !$phpfm_auth);
else exit("<font color='#CC0000'>Access Denied!</font>");
?>
