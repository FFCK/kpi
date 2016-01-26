<?php
if (!defined( 'ABSPATH' ) )
	die();
if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php"))
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php");

if ($AllowView && isset($_GET['filename']))
{
 $filename = stripslashes($_GET['filename']);

 print "<table class='index' width=800 cellpadding=0 cellspacing=0>";
  print "<tr>";
   print "<td class='iheadline' height=21>";
    print "<font class='iheadline'>&nbsp;$StrViewing \"".htmlentities($filename)."\" $StrAt ".$_GET['size']."%</font>";
   print "</td>";
   print "<td class='iheadline' align='right' height=21>";
    print "<font class='iheadline'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/back.gif' border=0 alt='$StrBack'></a></font>";
   print "</td>";
  print "</tr>";
  print "<tr>";
   print "<td valign='top' colspan=2>";

   print "<center><br />";

    if (is_file($home_directory.$wp_fileman_path.$filename) && wp_fileman_is_viewable_file($filename))
    {
     $image_info = GetImageSize($home_directory.$wp_fileman_path.$filename);
     $size = $_GET['size'];
     $zoom_in = $ZoomArray[wp_fileman_get_current_zoom_level($size, 1)];
     $zoom_out = $ZoomArray[wp_fileman_get_current_zoom_level($size, -1)];
     $width = $image_info[0] * $size / 100;
     $height = $image_info[1] * $size / 100;

     $files = array();
     if ($open = @opendir($home_directory.$wp_fileman_path))
     {
      while ($file = @readdir($open))
       if (is_file($home_directory.$wp_fileman_path.$file) && wp_fileman_is_viewable_file($file))
        $files[] = $file;
      closedir($open);
      sort($files);

      if (count($files)>1)
      {
       for($i=0;$files[$i]!=$filename;$i++);
        if ($i==0) $prev = $files[$i+count($files)-1];
         else $prev = $files[$i-1];
          if ($i==(count($files)-1)) $next = $files[$i-count($files)+1];
           else $next = $files[$i+1];
      }
      else
      {
       $prev = $filename;
       $next = $filename;
      }
     }

     print "<table class='menu' cellpadding=2 cellspacing=0>";
      print "<tr>";
       print "<td align='center' valign='bottom'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($filename))."&amp;action=view&amp;size=$zoom_in'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/plus.gif' width=11 height=11 border=0 alt='$StrZoomIn'>&nbsp;$StrZoomIn</a></td>";
       print "<td align='center' valign='bottom'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($filename))."&amp;action=view&amp;size=$zoom_out'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/minus.gif' width=11 height=11 border=0 alt='$StrZoomOut'>&nbsp;$StrZoomOut</a></td>";
       print "<td align='center' valign='bottom'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($filename))."&amp;action=view&amp;size=100'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/original.gif' width=11 height=11 border=0 alt='$StrOriginalSize'>&nbsp;$StrOriginalSize</a></td>";
       print "<td align='center' valign='bottom'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($prev))."&amp;action=view&amp;size=$size'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/previous.gif' width=11 height=11 border=0 alt='$StrPrevious'>&nbsp;$StrPrevious</a></td>";
       print "<td align='center' valign='bottom'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."&amp;filename=".htmlentities(rawurlencode($next))."&amp;action=view&amp;size=$size'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/next.gif' width=11 height=11 border=0 alt='$StrNext'>&nbsp;$StrNext</a></td>";
      print "</tr>";
     print "</table><br />";
     //echo base
     //print "<img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/incl/libfile.php?path=".htmlentities(rawurlencode($wp_fileman_path))."&filename=".htmlentities(rawurlencode($filename))."&action=view' width='$width' height='$height' alt='$StrImage'>";
     if (is_file($home_directory.$wp_fileman_path.$filename))
	$fullpath = $home_directory.$wp_fileman_path.$filename;
	
     //$file_data=readfile($fullpath);
     print "<img src='data:".wp_fileman_get_mimetype($filename). ";base64," . base64_encode(@file_get_contents($fullpath)) . "' width='$width' height='$height' alt='$StrImage'>";
    }
    else
    {
     print "<font color='#CC0000'>$StrViewFail</font><br /><br />";
     print "$StrViewFailHelp";
    }

   print "<br /><br /></center>";

   print "</td>";
  print "</tr>";
 print "</table>";

 print "<input type='hidden' name='path' value=\"".htmlentities($wp_fileman_path)."\">";
}
else
 print "<font color='#CC0000'>$StrAccessDenied</font>";

?>