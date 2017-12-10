<?php
if ( ! defined( 'ABSPATH' ) )
	die();
$wp_root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
if(file_exists($wp_root . '/wp-load.php')) {
require_once($wp_root . "/wp-load.php");
} else if(file_exists($wp_root . '/wp-config.php')) {
require_once($wp_root . "/wp-config.php");
}else {
//echo "Exiting $wp_root";
exit;
}
$wp_filemanager_path=plugin_dir_path("wp-filemanger");
# Check "docs/configurating.txt" for a more complete description of how you
# set the different settings and what they will do.

# Path from wp-admin to your images directory
# Use forward slashes instead of backslashes and remember a traling slash!
# This path should be RELATIVE to your wp-admin directory
//$home_directory         = "../../../";
if ( function_exists('get_option') && get_option('wp_fileman_home') != '')
{
	$home_directory = get_option('wp_fileman_home');
}
else
{
	$home_directory = plugin_dir_path("wp-filemanger");
}
# Language of PHPFM.
$language       = "english";

# Session save_path information
# *NIX systems      - set this to "/tmp/";
# WINDOWS systems   - set this to "c:/winnt/temp/";
# NB! replace "c:/winnt/" with the path to your windows folder
#
# Uncomment _only_ if you are experiencing errors!
# $session_save_path    = "/tmp/";

# Login is handled by Wordpress in this hack
# DO NOT enable phpfm_auth, as it will likely break the script
$phpfm_auth     = FALSE;
$username       = "";
$password       = "";

# Access configuration
# Each variable can be set to either TRUE or FALSE.
if (function_exists('get_option') && get_option('wp_fileman_Allow_Download') == 'checked')
	$AllowDownload        = TRUE;
else
	$AllowDownload        = FALSE;

if (function_exists('get_option') && get_option('wp_fileman_Create_File') == 'checked')
	$AllowCreateFile        = TRUE;
else
	$AllowCreateFile        = FALSE;

if (function_exists('get_option') && get_option('wp_fileman_Create_Folder') == 'checked')
	$AllowCreateFolder        = TRUE;
else
	$AllowCreateFolder        = FALSE;
	
if (function_exists('get_option') && get_option('wp_fileman_Allow_Rename') == 'checked')
	$AllowRename        = TRUE;
else
	$AllowRename        = FALSE;
if (function_exists('get_option') && get_option('wp_fileman_Allow_Upload') == 'checked')
	$AllowUpload        = TRUE;
else
	$AllowUpload        = FALSE;
if (function_exists('get_option') && get_option('wp_fileman_Allow_Delete') == 'checked')
	$AllowDelete        = TRUE;
else
	$AllowDelete        = FALSE;
if (function_exists('get_option') && get_option('wp_fileman_Allow_View') == 'checked')
	$AllowView        = TRUE;
else
	$AllowView        = FALSE;
if (function_exists('get_option') && get_option('wp_fileman_Allow_Edit') == 'checked')
	$AllowEdit        = TRUE;
else
	$AllowEdit        = FALSE;
if (function_exists('get_option') && get_option('wp_fileman_Show_Extension') == 'checked')
	$ShowExtension        = TRUE;
else
	$ShowExtension        = FALSE;
	
# Icons for files
$IconArray = array(
     "text.gif"       => "txt ini xml xsl ini inf cfg log nfo",
     "layout.gif"     => "html htm shtml htm pdf",
     "script.gif"     => "php php4 php3 phtml phps conf sh shar csh ksh tcl cgi pl js",
     "image2.gif"     => "jpeg jpe jpg gif png bmp",
     "c.gif"          => "c cpp",
     "compressed.gif" => "zip tar gz tgz z ace rar arj cab bz2",
     "sound2.gif"     => "wav mp1 mp2 mp3 mid",
     "movie.gif"      => "mpeg mpg mov avi rm wmv divx",
     "binary.gif"     => "exe com dll bin dat rpm deb",
);

# Files that can be edited in PHPFM's text editor

if (trim(function_exists('get_option') && get_option('wp_fileman_editable_ext')) != "")
{
	$EditableFiles = get_option('wp_fileman_editable_ext');
}
# Files that can be viewed in PHPFM's image viewer.
if (trim(function_exists('get_option') && get_option('wp_fileman_viewable_ext')) != "")
{
	$ViewableFiles = get_option('wp_fileman_viewable_ext');
}

# Format of last modification date
$ModifiedFormat = "Y-m-d H:i";

# Zoom levels for PHPFM's image viewer.
$ZoomArray = array(
     5,
     7,
     10,
     15,
     20,
     30,
     50,
     70,
     100,       # Base zoom level (do not change)
     150,
     200,
     300,
     500,
     700,
     1000,
);

# Hidden files and directories
if (function_exists('get_option') && get_option('wp_fileman_hidden_extension') != "")
{
	$hide_file_extension = explode(',',get_option('wp_fileman_hidden_extension'));
}
else
{
$hide_file_extension       = array(
                                    "foo",
                                    "bar",
                             );
}

if (function_exists('get_option') && get_option('wp_fileman_hidden_file') != "")
{
	$hide_file_string = explode(',',get_option('wp_fileman_hidden_file'));
}
else
{
$hide_file_string          = array(
                                  ".htaccess"
                             );
}
if (function_exists('get_option') && get_option('wp_fileman_hidden_dir') != "")
{
		$hide_directory_string = explode(',',get_option('wp_fileman_hidden_dir'));
}
else
{
$hide_directory_string     = array(
                                    "secret dir",
                             );
}

$MIMEtypes = array(
     "application/andrew-inset"       => "ez",
     "application/mac-binhex40"       => "hqx",
     "application/mac-compactpro"     => "cpt",
     "application/msword"             => "doc",
     "application/octet-stream"       => "bin dms lha lzh exe class so dll",
     "application/oda"                => "oda",
     "application/pdf"                => "pdf",
     "application/postscript"         => "ai eps ps",
     "application/smil"               => "smi smil",
     "application/vnd.ms-excel"       => "xls",
     "application/vnd.ms-powerpoint"  => "ppt",
     "application/vnd.wap.wbxml"      => "wbxml",
     "application/vnd.wap.wmlc"       => "wmlc",
     "application/vnd.wap.wmlscriptc" => "wmlsc",
     "application/x-bcpio"            => "bcpio",
     "application/x-cdlink"           => "vcd",
     "application/x-chess-pgn"        => "pgn",
     "application/x-cpio"             => "cpio",
     "application/x-csh"              => "csh",
     "application/x-director"         => "dcr dir dxr",
     "application/x-dvi"              => "dvi",
     "application/x-futuresplash"     => "spl",
     "application/x-gtar"             => "gtar",
     "application/x-hdf"              => "hdf",
     "application/x-javascript"       => "js",
     "application/x-koan"             => "skp skd skt skm",
     "application/x-latex"            => "latex",
     "application/x-netcdf"           => "nc cdf",
     "application/x-sh"               => "sh",
     "application/x-shar"             => "shar",
     "application/x-shockwave-flash"  => "swf",
     "application/x-stuffit"          => "sit",
     "application/x-sv4cpio"          => "sv4cpio",
     "application/x-sv4crc"           => "sv4crc",
     "application/x-tar"              => "tar",
     "application/x-tcl"              => "tcl",
     "application/x-tex"              => "tex",
     "application/x-texinfo"          => "texinfo texi",
     "application/x-troff"            => "t tr roff",
     "application/x-troff-man"        => "man",
     "application/x-troff-me"         => "me",
     "application/x-troff-ms"         => "ms",
     "application/x-ustar"            => "ustar",
     "application/x-wais-source"      => "src",
     "application/zip"                => "zip",
     "audio/basic"                    => "au snd",
     "audio/midi"                     => "mid midi kar",
     "audio/mpeg"                     => "mpga mp2 mp3",
     "audio/x-aiff"                   => "aif aiff aifc",
     "audio/x-mpegurl"                => "m3u",
     "audio/x-pn-realaudio"           => "ram rm",
     "audio/x-pn-realaudio-plugin"    => "rpm",
     "audio/x-realaudio"              => "ra",
     "audio/x-wav"                    => "wav",
     "chemical/x-pdb"                 => "pdb",
     "chemical/x-xyz"                 => "xyz",
     "image/bmp"                      => "bmp",
     "image/gif"                      => "gif",
     "image/ief"                      => "ief",
     "image/jpeg"                     => "jpeg jpg jpe",
     "image/png"                      => "png",
     "image/tiff"                     => "tiff tif",
     "image/vnd.wap.wbmp"             => "wbmp",
     "image/x-cmu-raster"             => "ras",
     "image/x-portable-anymap"        => "pnm",
     "image/x-portable-bitmap"        => "pbm",
     "image/x-portable-graymap"       => "pgm",
     "image/x-portable-pixmap"        => "ppm",
     "image/x-rgb"                    => "rgb",
     "image/x-xbitmap"                => "xbm",
     "image/x-xpixmap"                => "xpm",
     "image/x-xwindowdump"            => "xwd",
     "model/iges"                     => "igs iges",
     "model/mesh"                     => "msh mesh silo",
     "model/vrml"                     => "wrl vrml",
     "text/css"                       => "css",
     "text/html"                      => "html htm",
     "text/plain"                     => "asc txt",
     "text/richtext"                  => "rtx",
     "text/rtf"                       => "rtf",
     "text/sgml"                      => "sgml sgm",
     "text/tab-separated-values"      => "tsv",
     "text/vnd.wap.wml"               => "wml",
     "text/vnd.wap.wmlscript"         => "wmls",
     "text/x-setext"                  => "etx",
     "text/xml"                       => "xml xsl",
     "video/mpeg"                     => "mpeg mpg mpe",
     "video/quicktime"                => "qt mov",
     "video/vnd.mpegurl"              => "mxu",
     "video/x-msvideo"                => "avi",
     "video/x-sgi-movie"              => "movie",
     "x-conference/x-cooltalk"        => "ice",
);
?>
