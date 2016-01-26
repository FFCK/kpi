{* page.tpl Smarty *}

{config_load file='../../commun/MyLang.conf' section=$lang}

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			<link rel="stylesheet" type="text/css" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/{$contenutemplate}.js"></script>
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css" />
			<link rel="stylesheet" type="text/css" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="../js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/{$contenutemplate}.js"></script>
		{/if}
		{literal}
			<script language="JavaScript" type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAWonor80iC2LsJ4C5x7MJsBRfQyOgPKrZ8po1VXyQgkC373NrwRQugr1dZEkzcuIqpSAIryIaw67HyQ"></script>
			<script language="JavaScript" type="text/javascript">
				function loadparam() {
					if (GBrowserIsCompatible()) {
						{/literal}{$mapParam}{literal}
					}
				}
			</script>
		{/literal}

		<title>{$title}</title>
	</head>	  
	<body onload="testframe(); load(); loadparam(); alertMsg('{$AlertMessage}')" onunload="GUnload()">
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
		{include file="$contenutemplate.tpl"}
		{include file='footer.tpl'}
	</body>
</html>

