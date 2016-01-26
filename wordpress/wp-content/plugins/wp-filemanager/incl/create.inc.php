<?php
if ( ! defined( 'ABSPATH' ) )
	die();
if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php"))
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php");

if ($AllowCreateFolder && isset($_GET['create']) && isset($_POST['directory_name']))
{
 $umask = @umask(0);
 if (!wp_fileman_is_valid_name(stripslashes($_POST['directory_name'])))
  print "<font color='#CC0000'>$StrFolderInvalidName</font>";
 else if (file_exists($home_directory.$wp_fileman_path.stripslashes($_POST['directory_name']."/")))
  print "<font color='#CC0000'>$StrAlreadyExists</font>";
 else if (@mkdir($home_directory.$wp_fileman_path.stripslashes($_POST['directory_name']), 0777))
  print "<font color='#009900'>$StrCreateFolderSuccess</font>";
 else
 {
  print "<font color='#CC0000'>$StrCreateFolderFail</font><br /><br />";
  print $StrCreateFolderFailHelp;
 }
 @umask($umask);
}

else if ($AllowCreateFile && isset($_GET['create']) && isset($_POST['filename']))
{
 if (!wp_fileman_is_valid_name(stripslashes($_POST['filename'])))
  print "<font color='#CC0000'>$StrFileInvalidName</font>";
 else if (file_exists($home_directory.$wp_fileman_path.stripslashes($_POST['filename'])))
  print "<font color='#CC0000'>$StrAlreadyExists</font>";
 else if (@fopen($home_directory.$wp_fileman_path.stripslashes($_POST['filename']), "w+"))
  print "<font color='#009900'>$StrCreateFileSuccess</font>";
 else
 {
  print "<font color='#CC0000'>$StrCreateFileFail</font><br /><br />";
  print $StrCreateFileFailHelp;
 }
}

else if ($AllowCreateFolder || $AllowCreateFile)
{
 print "<table class='index' width=500 cellpadding=0 cellspacing=0>";
  print "<tr>";
   print "<td class='iheadline' height=21>";
    if ($_GET['type'] == "directory") print "<font class='iheadline'>&nbsp;$StrCreateFolder</font>";
    else if ($_GET['type'] == "file") print "<font class='iheadline'>&nbsp;$StrCreateFile</font>";
   print "</td>";
   print "<td class='iheadline' align='right' height=21>";
    print "<font class='iheadline'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/back.gif' border=0 alt='$StrBack'></a></font>";
   print "</td>";
  print "</tr>";
  print "<tr>";
   print "<td valign='top' colspan=2>";

    print "<center><br />";

    if ($_GET['type'] == "directory") print "$StrCreateFolderQuestion<br /><br />";
    else if ($_GET['type'] == "file") print "$StrCreateFileQuestion<br /><br />";
    print "<form action='$base_url&amp;output=create&amp;create=true' method='post'>";
    if ($_GET['type'] == "directory") print "<input name='directory_name' size=40>&nbsp;";
    else if ($_GET['type'] == "file") print "<input name='filename' size=40>&nbsp;";
    print "<input class='bigbutton' type='submit' value='$StrCreate'>";
    print "<input type='hidden' name=path value=\"".htmlentities($wp_fileman_path)."\">";
    print "</form>";

    print "<br /><br /></center>";

   print "</td>";
  print "</tr>";
 print "</table>";
}
else
 print "<font color='#CC0000'>$StrAccessDenied</font>";

?>