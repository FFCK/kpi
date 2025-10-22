{* page.tpl Smarty *}
{config_load file='../../commun/MyLang.conf' section=$lang}
{if $bPublic}{assign var=adm value=""}{else}{assign var=adm value="../"}{/if}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
    <head>
        <meta charset="utf-8" />
        <meta name="Author" Content="LG" />
        
        <!-- FB Meta -->
		<meta name="description" content="Commission Nationale d&#039;Activité Kayak-Polo"/>
		<meta name="robots" content="index, follow"/>
		<link rel="canonical" href="https://www.kayak-polo.info" />
		<link rel="next" href="https://www.kayak-polo.info/?paged=2" />
        {if $bPublic}
            <meta property="og:locale" content="fr_FR">
            <meta property="og:type" content="website">
            <meta property="og:title" content="Kayak-polo.info">
            <meta property="og:url" content="https://www.kayak-polo.info">
            <meta property="og:site_name" content="Kayak-polo.info">
            <meta property="og:image" content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
            <meta property="og:image:secure_url" content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:type" content="image/png">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="Kayak-polo.info">
            <meta name="twitter:image" content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
        {/if}
        <!-- Mobile Specific Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux" href="http://kayak-polo.info/?feed=rss2" />
        <link rel="alternate" type="application/rss+xml" title="Kayak-polo.info &raquo; Flux des commentaires" href="http://kayak-polo.info/?feed=comments-rss2" />

        <link rel='stylesheet' href='{$adm}css/fullcalendar.min.css' type='text/css' media='all' />
        <link rel='stylesheet' id='material-custom-css' href='{$adm}css/wordpress_material_stylesheets_styles.css?v={$NUM_VERSION}' type='text/css' media='all' />
        <link rel='stylesheet' id='material-main-css' href='{$adm}css/wordpress_material_style.css?v={$NUM_VERSION}' type='text/css' media='all' />
        <link rel='stylesheet' id='my_style-css' href='{$adm}css/jquery.dataTables.css?v={$NUM_VERSION}' type='text/css' media='all' />
        <link rel='stylesheet' href='{$adm}css/dataTables.fixedHeader.min.css?v={$NUM_VERSION}' type='text/css' media='all' />
        <link rel="stylesheet" href="{$adm}css/jquery-ui.css?v={$NUM_VERSION}">
        <link rel="stylesheet" href="{$adm}css/fontawesome/font-awesome.css?v={$NUM_VERSION}">
        
        {assign var=temp value="$adm./css/$contenutemplate.css"} 
        {if is_file($temp)}
            <link type="text/css" rel="stylesheet" href="{$adm}css/{$contenutemplate}.css?v={$NUM_VERSION}" />
        {/if}
        <!-- 
            Css = '' (simply, zsainto, ckca...) 
            notamment sur les pages Journee.php et Classements.php 
            intégrer en iframe : 
        -->
        {if isset($css_supp)}
            {assign var=temp value="$adm./css/$css_supp.css"} 
            {if $css_supp && is_file($temp)}
                <link type="text/css" rel="stylesheet" href="{$adm}css/{$css_supp}.css?v={$NUM_VERSION}">
            {/if}
        {/if}
        <title>{$smarty.config.$title|default:$title}</title>
    </head>
    <body onload="testframe(); alertMsg('{$AlertMessage}'); ">
        <div id="fb-root"></div>
        
        {if !($skipheader|default:false)}{include file='kpheader.tpl'}{/if}
        {include file="$contenutemplate.tpl"}
        {if !($skipheader|default:false)}{include file='kpfooter.tpl'}{/if}
        
        <script>
            masquer = 0;
            lang = '{$lang}';
            version = '{$NUM_VERSION}';
        </script>

        <script type='text/javascript' src='{$adm}js/jquery-3.5.1.min.js?v={$NUM_VERSION}'></script>
        <script type='text/javascript' src='{$adm}js/jquery-ui-1.12.1.min.js?v={$NUM_VERSION}'></script>
        <script type='text/javascript' src='{$adm}js/jquery.dataTables-1.10.21.min.js?v={$NUM_VERSION}'></script>
        <script type='text/javascript' src='{$adm}js/dataTables.fixedHeader.min.js?v={$NUM_VERSION}'></script>
        <script type='text/javascript' src='{$adm}js/bootstrap/js/bootstrap.min.js?v={$NUM_VERSION}'></script>
        <script type="text/javascript" src="{$adm}js/wordpress_material_javascripts_main.js"></script>
        <script type="text/javascript" src="{$adm}js/formTools.js?v={$NUM_VERSION}" defer></script>
        {assign var=temp value="$adm./js/$contenutemplate.js"} 
        {if is_file($temp)}
            <script type="text/javascript" src="{$adm}js/{$contenutemplate}.js?v={$NUM_VERSION}" defer></script>
        {/if}
        {if $contenutemplate == 'kpcalendrier'}
            <script type='text/javascript' src='{$adm}js/moment.min.js?v={$NUM_VERSION}'></script>
            <script type='text/javascript' src='{$adm}js/fullcalendar.min.js?v={$NUM_VERSION}'></script>
        {/if}
        {if $contenutemplate|upper eq 'IMPORTPCE' }	
            {literal}
                <script>
                    Init();
                </script>

            {/literal}
        {/if}
            
        {literal}
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
            
            <!-- Matomo -->
            <script>
            var _paq = window._paq = window._paq || [];
            /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="{/literal}{$smarty.const.MATOMO_SERVER_URL}{literal}";
                _paq.push(['setTrackerUrl', u+'matomo.php']);
                _paq.push(['setSiteId', '{/literal}{$smarty.const.MATOMO_SITE_ID_PUBLIC}{literal}']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
            })();
            </script>
            <!-- End Matomo Code -->

        {/literal}
    
    </body>
</html>