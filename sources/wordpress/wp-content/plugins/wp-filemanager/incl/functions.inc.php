<?php
if ( ! defined( 'ABSPATH' ) )
	die();
function wp_fileman_remove_directory($directory)	## Remove a directory recursively
{
 $list_sub = array();
 $list_files = array();

 if (!($open = @opendir($directory)))
  return FALSE;

 while(($index = @readdir($open)) != FALSE)
 {
  if (is_dir($directory.$index) && $index != "." && $index != "..")
   $list_sub[] = $index."/";
  else if (is_file($directory.$index))
   $list_files[] = $index;
 }

 closedir($open);

 foreach($list_files as $file)
  if (!@unlink($directory.$file))
   return FALSE;

 foreach($list_sub as $sub)
 {
  wp_fileman_remove_directory($directory.$sub);
  if (!@rmdir($directory.$sub))
   return FALSE;
 }

 return TRUE;
}

function wp_fileman_get_icon($filename)	## Get the icon from the filename
{
 global $IconArray;

 @reset($IconArray);

 $extension = strtolower(substr(strrchr($filename, "."),1));

 if ($extension == "")
  return "unknown.gif";

 while (list($icon, $types) = each($IconArray))
  foreach (explode(" ", $types) as $type)
   if ($extension == $type)
    return $icon;

 return "unknown.gif";
}

function wp_fileman_compare_filedata ($a, $b)	## Compare filedata (used to sort)
{
 if (is_int($a[$_GET['sortby']]) && is_int($b[$_GET['sortby']]))
 {
  if ($a[$_GET['sortby']]==$b[$_GET['sortby']]) return 0;

  if ($_GET['order'] == "asc")
  {
   if ($a[$_GET['sortby']] > $b[$_GET['sortby']]) return 1;
   else return -1;
  }
  else if ($_GET['order'] == "desc")
  {
   if ($a[$_GET['sortby']] < $b[$_GET['sortby']]) return 1;
   else return -1;
  }
 }

 else if (is_string($a[$_GET['sortby']]) && is_string($b[$_GET['sortby']]) && $_GET['order'] == "asc")
  return strcmp($a[$_GET['sortby']], $b[$_GET['sortby']]);
 else if (is_string($a[$_GET['sortby']]) && is_string($b[$_GET['sortby']]) && $_GET['order'] == "desc")
  return -strcmp($a[$_GET['sortby']], $b[$_GET['sortby']]);
}

function wp_fileman_get_opposite_order($input, $order)	## Get opposite order
{
 if ($_GET['sortby'] == $input)
 {
  if ($order == "asc")
   return "desc";
  else if ($order == "desc")
   return "asc";
 }
 else
  return "asc";
}

function wp_fileman_is_editable_file($filename)	## Checks whether a file is editable
{
 global $EditableFiles;

 $extension = strtolower(substr(strrchr($filename, "."),1));

 foreach(explode(",", $EditableFiles) as $type)
  if ($extension == $type)
   return TRUE;

 return FALSE;
}

function wp_fileman_is_viewable_file($filename)	## Checks whether a file is viewable
{
 global $ViewableFiles;

 $extension = strtolower(substr(strrchr($filename, "."),1));

 foreach(explode(",", $ViewableFiles) as $type)
  if ($extension == $type)
   return TRUE;

 return FALSE;
}

function wp_fileman_is_valid_name($input)	## Checks whether the directory- or filename is valid
{
 if (strstr($input, "\\"))
  return FALSE;
 else if (strstr($input, "/"))
  return FALSE;
 else if (strstr($input, ":"))
  return FALSE;
 else if (strstr($input, "?"))
  return FALSE;
 else if (strstr($input, "*"))
  return FALSE;
 else if (strstr($input, "\""))
  return FALSE;
 else if (strstr($input, "<"))
  return FALSE;
 else if (strstr($input, ">"))
  return FALSE;
 else if (strstr($input, "|"))
  return FALSE;
 else
  return TRUE;
}

function wp_fileman_get_better_filesize($filesize)	## Converts filesize to KB/MB/GB/TB
{
 $kilobyte = 1024;
 $megabyte = 1048576;
 $gigabyte = 1073741824;
 $terabyte = 1099511627776;

 if ($filesize >= $terabyte)
  return number_format($filesize/$terabyte, 2, ',', '.')."&nbsp;TB";
 else if ($filesize >= $gigabyte)
  return number_format($filesize/$gigabyte, 2, ',', '.')."&nbsp;GB";
 else if ($filesize >= $megabyte)
  return number_format($filesize/$megabyte, 2, ',', '.')."&nbsp;MB";
 else if ($filesize >= $kilobyte)
  return number_format($filesize/$kilobyte, 2, ',', '.')."&nbsp;KB";
 else
  return number_format($filesize, 0, ',', '.')."&nbsp;B";
}

function wp_fileman_get_current_zoom_level($current_zoom_level, $zoom)	## Get current zoom level
{
 global $ZoomArray;

 @reset($ZoomArray);

 while(list($number, $zoom_level) = each($ZoomArray))
  if ($zoom_level == $current_zoom_level)
   if (($number+$zoom) < 0) return $number;
   else if (($number+$zoom) >= count($ZoomArray)) return $number;
   else return $number+$zoom;
}

function wp_fileman_validate_path($wp_fileman_path)	## Validate path
{
 global $StrAccessDenied;

 if (stristr($wp_fileman_path, "../") || stristr($wp_fileman_path, "..\\"))
  return TRUE;
 else
  return stripslashes($wp_fileman_path);
}

function wp_fileman_authenticate_user()	## Authenticate user using cookies
{
 global $username, $password;

 if (isset($_COOKIE['cookie_username']) && $_COOKIE['cookie_username'] == $username && isset($_COOKIE['cookie_password']) && $_COOKIE['cookie_password'] == md5($password))
  return TRUE;
 else
  return FALSE;
}

function wp_fileman_is_hidden_file($wp_fileman_path)	## Checks whether the file is hidden.
{
 global $hide_file_extension, $hide_file_string, $hide_directory_string;

 $extension = strtolower(substr(strrchr($wp_fileman_path, "."),1));
 
 if (is_array($hide_file_extension))
 {
 foreach ($hide_file_extension as $hidden_extension)
  {
  if ($hidden_extension == $extension)
  {
   return TRUE;
  }
  }
 }
 if (is_array($hide_file_string))  
 {
  foreach ($hide_file_string as $hidden_string)
  {
   if ($hidden_string != "" && stristr(basename($wp_fileman_path), $hidden_string))
   {
   return TRUE;
}
}
}
 return FALSE;
}

function wp_fileman_is_hidden_directory($wp_fileman_path)	## Checks whether the directory is hidden.
{
 global $hide_directory_string;

 if (is_array($hide_directory_string))
 foreach ($hide_directory_string as $hidden_string)
  if ($hidden_string != "" && stristr($wp_fileman_path, $hidden_string))
   return TRUE;

 return FALSE;
}

function wp_fileman_get_mimetype($filename)	## Get MIME-type for file
{
 global $MIMEtypes;
 @reset($MIMEtypes);
 $extension = strtolower(substr(strrchr($filename, "."),1));
 if ($extension == "")
  return "Unknown/Unknown";
 while (list($mimetype, $file_extensions) = each($MIMEtypes))
  foreach (explode(" ", $file_extensions) as $file_extension)
   if ($extension == $file_extension)
    return $mimetype;

 return "Unknown/Unknown";
}

function wp_fileman_get_linked_path($wp_fileman_path,$base_url)	## Get path with links to each folder
{
 $string = "<a href='$base_url'>.</a> / ";
 $array = explode("/",htmlentities($wp_fileman_path));
 unset($array[count($array)-1]);
 foreach ($array as $entry)
 {
  @$temppath .= $entry."/";
  $string .= "<a href='$base_url&amp;path=".htmlentities(rawurlencode($temppath))."'>$entry</a> / ";
 }

 return $string;
}
?>