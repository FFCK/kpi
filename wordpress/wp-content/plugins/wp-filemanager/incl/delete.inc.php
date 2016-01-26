<?php

if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php"))
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php");

if ($AllowDelete && isset($_GET['directory_name']) || $AllowDelete && isset($_GET['filename']))
{
 if (isset($_GET['delete']) && isset($_GET['directory_name']))
 {
  if ($_GET['directory_name'] == "../" || ($_GET['directory_name'] == "./"))
   print "<font color='#CC0000'>$StrFolderInvalidName</font>";
  else if (!file_exists($home_directory.$wp_fileman_path.$directory_name))
   print "<font color='#CC0000'>$StrDeleteFolderNotFound</font>";
  else if (wp_fileman_remove_directory($home_directory.$wp_fileman_path.$directory_name) && @rmdir($home_directory.$wp_fileman_path.$directory_name))
   print "<font color='#009900'>$StrDeleteFolderSuccess</font>";
  else
  {
   print "<font color='#CC0000'>$StrDeleteFolderFail</font><br /><br />";
   print $StrDeleteFolderFailHelp;
  }
 }

 else if (isset($_GET['delete']) && isset($_GET['filename']))
 {
  if ($_GET['filename'] == ".." || ($_GET['filename'] == "."))
   print "<font color='#CC0000'>$StrFileInvalidName</font>";
  else if (!file_exists($home_directory.$wp_fileman_path.$filename))
   print "<font color='#CC0000'>$StrDeleteFileNotFound</font>";
  else if (@unlink($home_directory.$wp_fileman_path.$filename))
   print "<font color='#009900'>$StrDeleteFileSuccess</font>";
  else
  {
   print "<font color='#CC0000'>$StrDeleteFileFail</font><br /><br />";
   print $StrDeleteFileFailHelp;
  }
 }

 else
 {
   print "<table class='index' width=500 cellpadding=0 cellspacing=0>";
    print "<tr>";
     print "<td class='iheadline' height=21>";
      if (isset($_GET['directory_name'])) print "<font class='iheadline'>&nbsp;$StrDeleteFolder \"".htmlentities(basename($directory_name))."\"?</font>";
      else if (isset($_GET['filename'])) print "<font class='iheadline'>&nbsp;$StrDeleteFile \"".htmlentities($filename)."\"?</font>";
     print "</td>";
     print "<td class='iheadline' align='right' height=21>";
      print "<font class='iheadline'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/back.gif' border=0 alt='$StrBack'></a></font>";
     print "</td>";
    print "</tr>";
    print "<tr>";
     print "<td valign='top' colspan=2>";

     print "<center><br />";

     if (isset($_GET['directory_name']))
     {
      print "$StrDeleteFolderQuestion<br /><br />";
      print "/".htmlentities($wp_fileman_path.$directory_name);
     }
     else if (isset($_GET['filename']))
     {
      print "$StrDeleteFileQuestion<br /><br />";
      print "/".htmlentities($wp_fileman_path.$filename);
     }

     print "<br /><br />";

     if (isset($_GET['directory_name'])) print "<a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;directory_name=".htmlentities(rawurlencode($directory_name))."&amp;output=delete&amp;delete=true'>$StrYes</a>";
     else if (isset($_GET['filename'])) print "<a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($filename))."&amp;output=delete&amp;delete=true'>$StrYes</a>";
     print "&nbsp;$StrOr&nbsp;";
     print "<a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'>$StrCancel</a>";

     print "<br /><br /></center>";

     print "</td>";
    print "</tr>";
   print "</table>";
 }
}
else
 print "<font color='#CC0000'>$StrAccessDenied</font>";

?>
