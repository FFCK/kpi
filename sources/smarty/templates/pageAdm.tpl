{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
			<link rel="stylesheet" type="text/css" href="css/style.css">
			<link rel="stylesheet" href="js/bootstrap/css/bootstrap.min.css">
			<link type="text/css" rel="stylesheet" href="css/jquery-ui-1.11.4.min.css">
			<link rel="stylesheet" href="css/dataTables.jqueryui.css">
			<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
			<!--<link type="text/css" rel="stylesheet" href="css/jquery.autocomplete.css" media="screen">-->
			<!--<link type="text/css" rel="stylesheet" href="css/jquery.tooltip.css" media="screen">-->
			<link type="text/css" rel="stylesheet" href="css/{$contenutemplate}.css">
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
			{if $css_supp}
				<link type="text/css" rel="stylesheet" href="css/{$css_supp}.css">
			{/if}
			<script language="JavaScript" type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="js/bootstrap/js/bootstrap.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/dataTables.bootstrap.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.dataTables.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/jqueryui/dataTables.jqueryui.js"></script>-->
			<!--<script language="JavaScript" type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="js/jquery.stickytableheaders.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="js/jquery.tooltip.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="js/jquery.fixedheadertable.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="js/AdmTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/{$contenutemplate}.js"></script>
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico">
			<link rel="stylesheet" href="../js/bootstrap/css/bootstrap.min.css">
			<link type="text/css" rel="stylesheet" href="../css/jquery-ui-1.11.4.min.css">
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css">
		        <link rel="stylesheet" href="../css/dataTables.jqueryui.css">
			<link type="text/css" rel="stylesheet" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
			<!--<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" media="screen">-->
			<!--<link type="text/css" rel="stylesheet" href="../css/jquery.tooltip.css" media="screen">-->
			<link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css">
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
			{if $css_supp}
				<link type="text/css" rel="stylesheet" href="../css/{$css_supp}.css">
			{/if}
			<script language="JavaScript" type="text/javascript" src="../js/jquery-1.11.2.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="../js/bootstrap/js/bootstrap.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/dataTables.bootstrap.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="../js/jquery-ui-1.10.4.custom.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/jqueryui/dataTables.jqueryui.js"></script>-->
			<!--<script language="JavaScript" type="text/javascript" src="../js/jquery-ui-1.10.4.custom.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="../js/jquery.stickytableheaders.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="../js/jquery.tooltip.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="../js/jquery.maskedinput.min.js"></script>
			<!--<script language="JavaScript" type="text/javascript" src="../js/jquery.fixedheadertable.min.js"></script>-->
			<script language="JavaScript" type="text/javascript" src="../js/AdmTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/{$contenutemplate}.js"></script>
		{/if}
		<title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body onload="testframe(); alertMsg('{$AlertMessage}')">
		{include file='headerAdm.tpl'}
		{include file='main_menuAdm.tpl'}
		{include file="$contenutemplate.tpl"}
		{include file='footerAdm.tpl'}
	</body>
	{if $contenutemplate|upper eq 'IMPORTPCE' }	
		{literal}
			<script type="text/javascript">
				$(document).ready(function(){
					Init();
					
				});
			</script>
		{/literal}
	{/if}
</html>