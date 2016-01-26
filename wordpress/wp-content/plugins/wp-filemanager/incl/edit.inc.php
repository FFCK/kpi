<?php
if ( ! defined( 'ABSPATH' ) )
	die();
if (!@include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php"))
{
 include_once(WP_CONTENT_DIR . "/plugins/wp-filemanager/incl/auth.inc.php");
}
//echo "save called with path";
if ($AllowEdit && isset($_GET['save']) && isset($_POST['filename']))
{
	//echo "Save Edited file";
	$text = stripslashes($_POST['text']);
	if (!wp_fileman_is_valid_name(stripslashes($_POST['filename']))) 
	{
	print "<font color='#CC0000'>$StrFileInvalidName</font>";
	}
	else if ($fp = @fopen ($home_directory.$wp_fileman_path.stripslashes($_POST['filename']), "wb"))
	{
		@fwrite($fp, $text);
		@fclose($fp);
		print "<font color='#009900'>$StrSaveFileSuccess</font>";
	}
	else
		print "<font color='#CC0000'>$StrSaveFileFail</font>";
}
else if ($AllowEdit && isset($_GET['filename']))
{
	$file_name = explode('.',$_GET['filename']);
	if ($file_name[1] == 'js')
	{
		$file_name[1] = 'javascript';
	}
//	wp_enqueue_script('jquery');
//	wp_enqueue_script('codepress');

/*	<script type="text/javascript">
	var language = '<?php echo $file_name[1]; ?>';
	var engine = 'older';
	var ua = navigator.userAgent;
	var ts = (new Date).getTime(); // timestamp to avoid cache

	if(ua.match('MSIE')) engine = 'msie';
	else if(ua.match('KHTML')) engine = 'khtml'; 
	else if(ua.match('Opera')) engine = 'opera'; 
	else if(ua.match('Gecko')) engine = 'gecko';


	document.write('<link type="text/css" href="<?php echo bloginfo('url') . "/wp-includes/js/codepress/"  ?>codepress.css?ts='+ts+'" rel="stylesheet" />');
	document.write('<link type="text/css" href="<?php echo bloginfo('url') . "/wp-includes/js/codepress/"  ?>languages/'+language+'.css?ts='+ts+'" rel="stylesheet" id="cp-lang-style" />');
	document.write('<scr'+'ipt type="text/javascript" src="<?php echo bloginfo('url') . "/wp-includes/js/codepress/"  ?>engines/'+engine+'.js?ts='+ts+'"></scr'+'ipt>');
	document.write('<scr'+'ipt type="text/javascript" src="<?php echo bloginfo('url') . "/wp-includes/js/codepress/"  ?>languages/'+language+'.js?ts='+ts+'"></scr'+'ipt>');
	</script>


	<script type="text/javascript">
		codepress_path = "<?php echo includes_url('/js/codepress/'); ?>";
		jQuery(document).ready(function($){
        $('#edit_file').submit(function(){
          if ($('#text_cp').length)
          $('#text_cp').val(text.getCode()).removeAttr('disabled');
          });
        $('#reset').click(function(){
          if ($('#text_cp').length)
          $('#text_cp').val(text.getCode()).removeAttr('disabled');
		  $('#edit_file').clearForm();
          });
		});

	</script>
*/
	print "<table class='index' width=800 cellpadding=0 cellspacing=0>";
	print "<tr>";
	print "<td class='iheadline' height=21>";
	print "<font class='iheadline'>&nbsp;$StrEditing \"".htmlentities($filename)."\"</font>";
	print "</td>";
	print "<td class='iheadline' align='right' height=21>";
	print "<font class='iheadline'><a href='$base_url&amp;path=".htmlentities(rawurlencode($wp_fileman_path))."'><img src='" . WP_CONTENT_URL . "/plugins/wp-filemanager/icon/back.gif' border=0 alt='$StrBack'></a></font>";
	print "</td>";
	print "</tr>";
	print "<tr>";
	print "<td valign='top' colspan=2>";

	print "<center><br />";

	if ($fp = @fopen($home_directory.$wp_fileman_path.$filename, "rb"))
	{
		print "<form action='$base_url&amp;output=edit&amp;save=true' method='post' id='edit_file'>";
//		print "<form method='post' name='edit_file' id='edit_file'>";
		//print "<a class='button' href='javascript:text.toggleEditor();' style='float:right'>Code Editor</a>";
		print "\n<textarea cols=120 rows=20 name='text' id='text' class='codepress " . $file_name[1] . "'>";
//		print "\n<textarea cols=120 rows=20 name='text' class='codepress " . $file_name[1] . "'>";
		if (filesize($home_directory.$wp_fileman_path.$filename) > 0 )
		{
			print htmlentities(fread($fp, filesize($home_directory.$wp_fileman_path.$filename)));
			@fclose ($fp);
		}
		print "</textarea>";

		print "<br /><br />";
		print "$StrFilename <input size=40 name='filename' value=\"".htmlentities($filename)."\">";

		print "<br /><br />";
		print "<input class='bigbutton' id='reset' type='reset' value='$StrRestoreOriginal'>&nbsp;<input class='bigbutton' type='submit' value='$StrSaveAndExit'>";

		print "<input type='hidden' name='path' value=\"".htmlentities($wp_fileman_path)."\">";
		print "</form>";
	}
	else
		print "<font color='#CC0000'>$StrErrorOpeningFile</font>";

	print "<br /><br /></center>";

	print "</td>";
	print "</tr>";
	print "</table>";
}
else
	print "<font color='#CC0000'>$StrAccessDenied</font>";
?>
