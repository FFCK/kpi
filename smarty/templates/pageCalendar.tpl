{* pageCalendar.tpl Smarty *}

{config_load file='../../commun/MyLang.conf' section=$lang}

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />

		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel='stylesheet' type='text/css' href='css/redmond/theme.css' />
		<link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
		<link rel="stylesheet" type="text/css" href="css/{$contenutemplate}.css" />

		<script language="JavaScript" type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.core.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.draggable.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.resizable.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/fullcalendar.min.js'></script>
		<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
		<script language="JavaScript" type="text/javascript" src="js/{$contenutemplate}.js"></script>

		<title>{$title}</title>
	</head>	  
	<body onload="testframe(); alertMsg('{$AlertMessage}')">
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
		{include file="$contenutemplate.tpl"}
		{include file='footer.tpl'}
</body>
</html>

