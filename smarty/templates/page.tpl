{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<meta property="og:image" content="http://kayak-polo.info/img/KPI.png" />
		<link rel="image_src" href="http://kayak-polo.info/img/KPI.png" />
		<!--<meta property="og:title" content="kayak-polo.info" />-->
		<meta property="og:type" content="article" />
		<!--<meta property="og:url" content="http://kayak-polo.info"/>-->
		<!--<meta property="og:description" content="kayak polo français" />-->
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />
		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link type="text/css" rel="stylesheet" href="css/jquery.autocomplete.css" media="screen" />
			<link type="text/css" rel="stylesheet" href="css/jquery.tooltip.css" media="screen" />
                        {assign var=temp value="css/$contenutemplate.css"} 
                        {if is_file($temp)}
                            <link type="text/css" rel="stylesheet" href="css/{$contenutemplate}.css" />
                        {/if}
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
                        {assign var=temp value="css/$css_supp.css"} 
			{if $css_supp && is_file($temp)}
				<link type="text/css" rel="stylesheet" href="css/{$css_supp}.css">
			{/if}
			<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script src="js/jquery-1.5.2.min.js"></script>
			<script src="js/jquery.autocomplete.min.js"></script>
			<script src="js/jquery.tooltip.min.js"></script>
			<script src="js/jquery.maskedinput.min.js"></script>
			<script src="js/jquery.fixedheadertable.min.js"></script>
			<script src="js/formTools.js"></script>
                        {assign var=temp value="js/$contenutemplate.js"} 
                        {if is_file($temp)}
                            <script src="js/{$contenutemplate}.js"></script>
                        {/if}

			<!--
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="http://hayageek.github.io/jQuery-Upload-File/jquery.uploadfile.min.js"></script>
			-->
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css" />
			<link type="text/css" rel="stylesheet" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.tooltip.css" media="screen" />
			{assign var=temp value="../css/$contenutemplate.css"} 
			{if is_file($temp)}
				<link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css" />
			{/if}
			
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
            {assign var=temp value="..css/$css_supp.css"} 
			{if $css_supp && is_file($temp)}
				<link type="text/css" rel="stylesheet" href="..css/{$css_supp}.css">
			{/if}
			<script src="../js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script src="../js/jquery-1.5.2.min.js"></script>
			<script src="../js/jquery.autocomplete.min.js"></script>
			<script src="../js/jquery.tooltip.min.js"></script>
			<script src="../js/jquery.maskedinput.min.js"></script>
			<script src="../js/jquery.fixedheadertable.min.js"></script>
			<script src="../js/formTools.js"></script>
			{assign var=temp value="../js/$contenutemplate.js"} 
			{if is_file($temp)}
				<script src="../js/{$contenutemplate}.js"></script>
			{/if}
			<!--
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="http://hayageek.github.io/jQuery-Upload-File/jquery.uploadfile.min.js"></script>
			-->
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