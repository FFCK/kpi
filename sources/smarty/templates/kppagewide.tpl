{* page.tpl Smarty *}
{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">

<head>
  <meta charset="utf-8" />
  <meta name="Author" Content="LG" />

  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <!-- Mobile Specific Meta -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <link rel='stylesheet' id='material-custom-css' href='css/wordpress_material_stylesheets_styles.css?v={$NUM_VERSION}'
    type='text/css' media='all' />
  <link rel='stylesheet' id='material-main-css' href='css/wordpress_material_style.css?v={$NUM_VERSION}' type='text/css'
    media='all' />
  <link rel="stylesheet" href="css/jquery-ui.css?v={$NUM_VERSION}">
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

<body onload="testframe(); alertMsg('{$AlertMessage}'); ">
  <div id="fb-root"></div>

  {include file='kpheaderwide.tpl'}
  {include file="$contenutemplate.tpl"}

  <script>
    masquer = 0;
    lang = '{$lang}';
    version = '{$NUM_VERSION}';
  </script>

  <script type='text/javascript' src='js/jquery-3.5.1.min.js?v={$NUM_VERSION}'></script>
  <script type='text/javascript' src='js/jquery-ui-1.12.1.min.js?v={$NUM_VERSION}'></script>
  <script type='text/javascript' src='js/bootstrap/js/bootstrap.min.js?v={$NUM_VERSION}'></script>
  <script type="text/javascript" src="js/wordpress_material_javascripts_main.js"></script>
  <script type="text/javascript" src="js/formTools.js?v={$NUM_VERSION}" defer></script>
  {assign var=temp value="js/$contenutemplate.js"}
  {if is_file($temp)}
    <script type="text/javascript" src="js/{$contenutemplate}.js?v={$NUM_VERSION}" defer></script>
  {/if}
  {if $contenutemplate == 'kpcalendrier'}
    <script type='text/javascript' src='js/moment.min.js?v={$NUM_VERSION}'></script>
    <script type='text/javascript' src='js/fullcalendar.min.js?v={$NUM_VERSION}'></script>
  {/if}
  {if $contenutemplate|upper eq 'IMPORTPCE' }
    {literal}
      <script>
        Init();
      </script>
    {/literal}
  {/if}
  <script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
  <script type="text/javascript" src="js/voie.js?v={$NUM_VERSION}" defer></script>

  {literal}
    <!-- Maintien connexion -->
    <script type="text/javascript">
      setInterval(() => {
        fetch('../check.php?' + new Date().getTime())
      }, 300000);
    </script>
    <!-- End Maintien connexion -->

    <!-- Matomo -->
    <script>
      var _paq = window._paq = window._paq || [];
      /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
      _paq.push(['trackPageView']);
      _paq.push(['enableLinkTracking']);
      (function() {
        var u="{/literal}{$smarty.const.MATOMO_SERVER_URL}{literal}";
        _paq.push(['setTrackerUrl', u+'matomo.php']);
        _paq.push(['setSiteId', '{/literal}{$smarty.const.MATOMO_SITE_ID_ADMIN}{literal}']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <!-- End Matomo Code -->
  {/literal}
</body>

</html>