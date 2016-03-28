{* page.tpl Smarty *}{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
        
        <!-- FB Meta -->
		<meta property="og:image" content="http://kayak-polo.info/img/KPI.png" />
		<link rel="image_src" href="http://kayak-polo.info/img/KPI.png" />
		<!--<meta property="og:title" content="kayak-polo.info" />-->
		<meta property="og:type" content="article" />
		<!--<meta property="og:url" content="http://kayak-polo.info"/>-->
		<!--<meta property="og:description" content="kayak polo français" />-->
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />
        
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="pingback" href="http://www.ffck.org/kpi/wordpress/xmlrpc.php">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <!-- Mobile Specific Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux" href="http://www.ffck.org/kpi/?feed=rss2" />
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux des commentaires" href="http://www.ffck.org/kpi/?feed=comments-rss2" />

        <link rel='stylesheet' href='css/fullcalendar.min.css' type='text/css' media='all' />
        <link rel='stylesheet' id='material-custom-css'  href='wordpress/wp-content/themes/material/stylesheets/styles.css?ver=4.2.1' type='text/css' media='all' />
        <link rel='stylesheet' id='material-main-css'  href='wordpress/wp-content/themes/material/style.css?ver=4.2.1' type='text/css' media='all' />
        <link rel='stylesheet' id='my_style-css'  href='css/jquery.dataTables.css?ver=4.2.1' type='text/css' media='all' />
        <link rel="stylesheet" href="css/jquery-ui.css">
        <link rel="stylesheet" href="css/fontawesome/font-awesome.css">
        
        <script type='text/javascript' src='wordpress/wp-includes/js/jquery/jquery.js?ver=1.11.2'></script>
        <script type='text/javascript' src='js/jquery-ui-1.11.4.min.js'></script>
        <script type='text/javascript' src='js/jquery.dataTables.min.js'></script>
        <script type='text/javascript' src='wordpress/wp-content/themes/material/javascripts/bootstrap.min.js?ver=4.2.1'></script>
        <script type='text/javascript' src='wordpress/wp-content/themes/material/javascripts/main.js?ver=4.2.1'></script>
        <!--<script type='text/javascript' src='//cdn.datatables.net/responsive/1.0.6/js/dataTables.responsive.min.js'></script>-->
        <!--[if lt IE 9]><script src="http://www.ffck.org/kpi/wordpress/wp-content/themes/material/javascripts/html5shiv.js"></script><![endif]-->
        {if $contenutemplate == 'kpcalendrier'}
            <script type='text/javascript' src='js/moment.min.js'></script>
            <script type='text/javascript' src='js/fullcalendar.min.js'></script>
        {/if}
        {literal}
            <style type="text/css" id="custom-background-css">
                body, .banner { background-color: #2670b3; }
                .titre {color: white}
                .fb-like { padding: 2px 0 3px; }
                .bg-blue { background-color: #2670b3;
                    border: 1px solid #2670b3;
                    color: #ffffff; }
                .bg-blue2 { background-color: #3C2F91;
                    border: 1px solid #3C2F91;
                    color: #ffffff; }
                .bg-green { background-color: #3C9757;
                    border: 1px solid #3C9757;
                    color: #ffffff; }
                .bg-brown { background-color: #993939;
                    border: 1px solid #993939;
                    color: #ffffff; }
            </style>
        {/literal}

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
        <script src="js/formTools.js" defer></script>
        {assign var=temp value="js/$contenutemplate.js"} 
        {if is_file($temp)}
            <script src="js/{$contenutemplate}.js" defer></script>
        {/if}
        <title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body onload="testframe(); alertMsg('{$AlertMessage}'); ">
        {literal}
            <div id="fb-root"></div>
            <script>
                window.fbAsyncInit = function() {
                  FB.init({
                    appId      : '693131394143366',
                    xfbml      : true,
                    version    : 'v2.3'
                  });
                };
                (function(d, s, id){
                   var js, fjs = d.getElementsByTagName(s)[0];
                   if (d.getElementById(id)) {return;}
                   js = d.createElement(s); js.id = id;
                   js.src = "//connect.facebook.net/en_US/sdk.js";
                   fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
        {/literal}
        {include file='kpheader.tpl'}
		{include file="$contenutemplate.tpl"}
		{include file='kpfooter.tpl'}

        {if $contenutemplate|upper eq 'IMPORTPCE' }	
            {literal}
                <script>
                    j(document).ready(function(){
                        Init();
                    });
                </script>
            {/literal}
        {/if}
    
    </body>
</html>