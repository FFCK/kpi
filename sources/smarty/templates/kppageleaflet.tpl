{* page.tpl Smarty *}
{config_load file='../../commun/MyLang.conf' section=$lang}
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
        <link rel="pingback" href="https://www.kayak-polo.info/wordpress/xmlrpc.php">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <!-- Mobile Specific Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux" href="http://kayak-polo.info/?feed=rss2" />
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux des commentaires" href="http://kayak-polo.info/?feed=comments-rss2" />

        <link rel='stylesheet' id='material-custom-css' href='css/wordpress_material_stylesheets_styles.css?v={$NUM_VERSION}' type='text/css' media='all' />
        <link rel='stylesheet' id='material-main-css' href='css/wordpress_material_style.css?v={$NUM_VERSION}' type='text/css' media='all' />
        {* <link rel='stylesheet' id='my_style-css' href='css/jquery.dataTables.css?v={$NUM_VERSION}' type='text/css' media='all' /> *}
        {* <link rel='stylesheet' href='css/dataTables.fixedHeader.min.css?v={$NUM_VERSION}' type='text/css' media='all' /> *}
        <link rel="stylesheet" href="css/jquery-ui.css?v={$NUM_VERSION}">
        <link rel="stylesheet" href="css/fontawesome/font-awesome.css?v={$NUM_VERSION}">
        <link rel="stylesheet" type="text/css" href="js/leaflet/leaflet.css" />
        
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
    <body onload="testframe(); alertMsg('{$AlertMessage}'); ">
        <div id="fb-root"></div>
        
        {include file='kpheader.tpl'}
        {include file="$contenutemplate.tpl"}
        {include file='kpfooter.tpl'}
        
        <script>
            masquer = 0;
            lang = '{$lang}';
            version = '{$NUM_VERSION}';
        </script>

        <script type='text/javascript' src='js/jquery-3.5.1.min.js?v={$NUM_VERSION}'></script>
        <script type='text/javascript' src='js/jquery-ui-1.12.1.min.js?v={$NUM_VERSION}'></script>
        {* <script type='text/javascript' src='js/jquery.dataTables.min.js?v={$NUM_VERSION}'></script> *}
        {* <script type='text/javascript' src='js/dataTables.fixedHeader.min.js?v={$NUM_VERSION}'></script> *}
        <script type='text/javascript' src='js/bootstrap/js/bootstrap.min.js?v={$NUM_VERSION}'></script>
        <script type="text/javascript" src="js/wordpress_material_javascripts_main.js"></script>
        <script type="text/javascript" src="js/formTools.js?v={$NUM_VERSION}"></script>
        <script type="text/javascript" src="js/leaflet/leaflet.js"></script>
        {assign var=temp value="js/$contenutemplate.js"} 
        {if is_file($temp)}
            <script type="text/javascript" src="js/{$contenutemplate}.js?v={$NUM_VERSION}"></script>
        {/if}
        
        {*{literal}
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
            <!-- Piwik -->
            <script type="text/javascript">
                var _paq = _paq || [];
                /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
                _paq.push(['trackPageView']);
                _paq.push(['enableLinkTracking']);
                (function() {
                    var u="piwik/";
                    _paq.push(['setTrackerUrl', u+'piwik.php']);
                    _paq.push(['setSiteId', '1']);
                    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <!-- End Piwik Code -->
        {/literal}*}
    
    </body>
</html>