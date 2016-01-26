{* page.tpl Smarty *}

{config_load file='../../commun/MyLang.conf' section=$lang}

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
			<link rel="stylesheet" type="text/css" href="css/style.css">
			<link rel="stylesheet" type="text/css" href="css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/{$contenutemplate}.js"></script>
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico">
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css">
			<link rel="stylesheet" type="text/css" href="../css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/{$contenutemplate}.js"></script>
		{/if}

		<title>{$title}</title>
	</head>	  
	<body onload="initialiser()">
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
		{include file="$contenutemplate.tpl"}
		{include file='footer.tpl'}
	</body>
</html>

