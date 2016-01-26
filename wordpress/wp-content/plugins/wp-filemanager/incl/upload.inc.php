<?php
if ( ! defined( 'ABSPATH' ) )
	die();
if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php"))
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php");

if ($AllowUpload && isset($_GET['upload']))
{
 print "<table cellspacing=0 cellpadding=0 class='upload'>";

 if (!isset($_FILES['userfile']))
  // maximum post size reached
  print $StrUploadFailPost;
 else
 {
  for($i=0;$i<count($_FILES['userfile']['tmp_name']);$i++)
  {
   $_FILES['userfile']['name'][$i] = stripslashes($_FILES['userfile']['name'][$i]);
if (@move_uploaded_file($_FILES['userfile']['tmp_name'][$i], realpath($home_directory.$wp_fileman_path)."/".$_FILES['userfile']['name'][$i])) {
print "<tr><td width='250'>$StrUploading ".$_FILES['userfile']['name'][$i]."</td><td width='50' align='center'>[<font color='#009900'>$StrUploadSuccess</font>]</td></tr>";
$new_file = @realpath($home_directory.$wp_fileman_path).'/'.$_FILES['userfile']['name'][$i];
$stat = @stat( dirname( $new_file ));
$perms = $stat['mode'] & 0000666;
@chmod( $new_file, $perms );
 } else if ($_FILES['userfile']['name'][$i])
   print "<tr><td width='250'>$StrUploading ".$_FILES['userfile']['name'][$i]."</td><td width='50' align='center'>[<font color='#CC0000'>$StrUploadFail</font>]</td></tr>";
 }
 }
 print "</table>";
} 

else if ($AllowUpload)
{
 print "<table class='index' width=500 cellpadding=0 cellspacing=0>";
  print "<tr>";
   print "<td class='iheadline' height=21>";
    print "<font class='iheadline'>&nbsp;$StrUploadFilesTo \"/".htmlentities($wp_fileman_path)."\"</font>";
   print "</td>";
   print "<td class='iheadline' align='right' height=21>";
    print "<font class='iheadline'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/back.gif' border=0 alt='$StrBack'></a></font>";
   print "</td>";
  print "</tr>";
  print "<tr>";
   print "<td valign='top' colspan=2>";
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);
$max_files = (int)(ini_get('max_file_uploads'));

//	print "MAX UPload : $max_upload MB, MAX POST : $max_post MB, MEM LIMIT : $memory_limit MB";
print "<br /><b>&nbsp;&nbsp;Maximum File Size Allowed : $upload_mb MB</b>";
print "<br /><b>&nbsp;&nbsp;Maximum Number of Files Allowed : $max_files</b>";
// FIXME : add link to howto on how to change the upload size.
    print "<center><br />";


    print "$StrUploadQuestion<br />";   
    print "<form action='$base_url&amp;output=upload&amp;upload=true' method='post' enctype='multipart/form-data'>";

    print "<table class='upload'>";
     print "<tr><td>$StrFirstFile</td><td><input type='file' name='userfile[]' size=30 multiple='multiple'></td></tr>";
       print "</table>";

    print "<input class='bigbutton' type='submit' value='$StrUpload'>";
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