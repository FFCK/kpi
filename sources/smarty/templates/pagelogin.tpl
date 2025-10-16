{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<meta property="og:image" content="http://kayak-polo.info/img/CNAKPI_small.png" />
		<link rel="image_src" href="http://kayak-polo.info/img/CNAKPI_small.png" />
		<meta property="og:type" content="article" />
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />

        <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
        <link href="../js/bootstrap/css/bootstrap.min.css?v={$NUM_VERSION}" rel="stylesheet" type="text/css"/>
        {assign var=temp value="../css/$contenutemplate.css"} 
        {if is_file($temp)}
            <link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css?v={$NUM_VERSION}" />
        {/if}
			
            
            
		<title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body>

        {include file="$contenutemplate.tpl"}
        
        <script src="../js/jquery-1.11.2.min.js?v={$NUM_VERSION}" type="text/javascript"></script>
        <script src="../js/bootstrap/js/bootstrap.min.js?v={$NUM_VERSION}" type="text/javascript"></script>
        {assign var=temp value="../js/$contenutemplate.js"} 
        {if is_file($temp)}
            <script src="../js/{$contenutemplate}.js?v={$NUM_VERSION}" type="text/javascript"></script>
        {/if}

        {literal}
            <!-- Matomo -->
            <script>
            var _paq = window._paq = window._paq || [];
            /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="https://matomo.kayak-polo.info/";
                _paq.push(['setTrackerUrl', u+'matomo.php']);
                _paq.push(['setSiteId', '2']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
            })();
            </script>
            <!-- End Matomo Code -->
        {/literal}

	</body>
</html>