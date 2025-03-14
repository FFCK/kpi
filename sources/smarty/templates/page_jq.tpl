{* page.tpl Smarty *}
{config_load file='../../commun/MyLang.conf' section=$lang}<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">

		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link type="text/css" rel="stylesheet" href="css/style.css" />
			<!--<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
			<link type="text/css" rel="stylesheet" href="css/jquery.autocomplete.css" media="screen">
			<link type="text/css" rel="stylesheet" href="css/tablesorter.blue.css" media="screen">
			<link type="text/css" rel="stylesheet" href="css/flexigrid.css" media="screen">
			<link type="text/css" rel="stylesheet" href="css/dataTables_table.css" media="screen">
			<link type="text/css" rel="stylesheet" href="css/dataTables_page.css" media="screen">
			<link type="text/css" rel="stylesheet" href="css/dataTables_table_jui.css" media="screen">
			
			<script language="JavaScript" type="text/javascript" src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script language="JavaScript" src="js/jquery-1.5.2.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="js/jquery.autocomplete.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="js/jquery.tablesorter.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="js/jquery.jeditable.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="js/jquery.maskedinput.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="js/formTools.js" type="text/javascript"></script>
			-->
			<link type="text/css" rel="stylesheet" href="css/{$contenutemplate}.css" />
			{if $css_supp}
				<link type="text/css" rel="stylesheet" href="css/{$css_supp}.css" />
			{/if}
			<script language="JavaScript" src="js/{$contenutemplate}.js" type="text/javascript"></script>
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link type="text/css" rel="stylesheet" href="../css/GestionStyle2.css" />
			<!--<link type="text/css" rel="stylesheet" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/tablesorter.blue.css" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/flexigrid.css" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/dataTables_table.css" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/dataTables_page.css" media="screen">
			<link type="text/css" rel="stylesheet" href="../css/dataTables_table_jui.css" media="screen">
			
			<script language="JavaScript" type="text/javascript" src="../js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script language="JavaScript" src="../js/jquery-1.5.2.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="../js/jquery.autocomplete.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="../js/jquery.tablesorter.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="../js/jquery.jeditable.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="../js/jquery.maskedinput.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="../js/formTools.js" type="text/javascript"></script>
			-->
			<link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css" />
			{if $css_supp}
				<link type="text/css" rel="stylesheet" href="../css/{$css_supp}.css" />
			{/if}
			<script language="JavaScript" src="../js/{$contenutemplate}.js" type="text/javascript"></script>
		{/if}
		<title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body onload="testframe(); alertMsg('{$AlertMessage}')">
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
		{include file="$contenutemplate.tpl"}
		{include file='footer.tpl'}
	</body>
</html>

