{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<meta property="og:image" content="https://www.kayak-polo.info/img/KPI.png" />
		<link rel="image_src" href="https://www.kayak-polo.info/img/KPI.png" />
		<!--<meta property="og:title" content="kayak-polo.info" />-->
		<meta property="og:type" content="article" />
		<!--<meta property="og:url" content="https://www.kayak-polo.info"/>-->
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
                            <link type="text/css" rel="stylesheet" href="css/{$contenutemplate}.css?v={$NUM_VERSION}" />
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
			<script src="js/formTools.js?v={$NUM_VERSION}"></script>
            {assign var=temp value="js/$contenutemplate.js"} 
            {if is_file($temp)}
                <script src="js/{$contenutemplate}.js?v={$NUM_VERSION}"></script>
            {/if}

		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css?v={$NUM_VERSION}" />
			<link type="text/css" rel="stylesheet" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.tooltip.css" media="screen" />
			{assign var=temp value="../css/$contenutemplate.css"} 
			{if is_file($temp)}
				<link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css?v={$NUM_VERSION}" />
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

		{/if}
		<title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body onload="testframe(); alertMsg('{$AlertMessage}')">
		{include file='header.tpl'}
		{include file='main_menu.tpl'}
		{include file="$contenutemplate.tpl"}
        {if !$bPublic}
            <script>
                masquer = {$masquer};
                lang = '{$lang}';
            </script>
			<script src="../js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script src="../js/jquery-1.5.2.min.js"></script>
			<script src="../js/jquery.autocomplete.min.js"></script>
			<script src="../js/jquery.tooltip.min.js"></script>
			<script src="../js/jquery.maskedinput.min.js"></script>
			<script src="../js/jquery.fixedheadertable.min.js"></script>
			<script src="../js/formTools.js?v={$NUM_VERSION}"></script>
			{assign var=temp value="../js/$contenutemplate.js"} 
			{if is_file($temp)}
				<script src="../js/{$contenutemplate}.js?v={$NUM_VERSION}"></script>
			{/if}
            
            {literal}
            <!-- Piwik -->
            <script type="text/javascript">
                var _paq = _paq || [];
                /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
                _paq.push(['trackPageView']);
                _paq.push(['enableLinkTracking']);
                (function() {
                    var u="//poloweb.org/piwik/";
                    _paq.push(['setTrackerUrl', u+'piwik.php']);
                    _paq.push(['setSiteId', '2']);
                    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <!-- End Piwik Code -->
            {/literal}

        {/if}
        {include file='footer.tpl'}
	</body>
</html>