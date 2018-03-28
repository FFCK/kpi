{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
        
        <!-- FB Meta -->
		<meta property="og:image" content="https://www.kayak-polo.info/img/newKPI2.jpg" />
		<link rel="image_src" href="https://www.kayak-polo.info/img/newKPI2.jpg" />
		<meta property="og:title" content="kayak-polo.info" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="https://www.kayak-polo.info"/>
		<meta property="og:description" content="FFCK - Commission Nationale d'Activité Kayak-Polo" />
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />
        
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="pingback" href="httpw://www.kayak-polo.info/wordpress/xmlrpc.php">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <!-- Mobile Specific Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        
        <link href="js/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="js/fullPage/jquery.fullpage.min.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="css/fontawesome/font-awesome.css?v={$NUM_VERSION}">
        

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
            <link type="text/css" rel="stylesheet" href="css/{$css_supp}.css?v={$NUM_VERSION}">
        {/if}
        <title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body>
       
		{include file="$contenutemplate.tpl"}
		
        
        
        <script src="js/jquery-1.11.2.min.js" type="text/javascript"></script>
        <script src="js/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/fullPage/jquery.fullpage.min.js" type="text/javascript"></script>
{*        <script src="js/formTools.js?v={$NUM_VERSION}" defer></script>*}
        {assign var=temp value="js/$contenutemplate.js"} 
        {if is_file($temp)}
            <script src="js/{$contenutemplate}.js?v={$NUM_VERSION}" defer></script>
        {/if}
    
    </body>
</html>