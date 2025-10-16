{* page.tpl Smarty *}
{config_load file='../../commun/MyLang.conf' section=$lang}
<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">

<head>
  <meta charset="utf-8" />
  <meta name="Author" Content="LG" />

  <!-- Mobile Specific Meta -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <link rel='stylesheet' href='css/fullcalendar.min.css' type='text/css' media='all' />
  <link rel='stylesheet' id='material-custom-css' href='css/wordpress_material_stylesheets_styles.css?v={$NUM_VERSION}'
    type='text/css' media='all' />
  <link rel='stylesheet' id='material-main-css' href='css/wordpress_material_style.css?v={$NUM_VERSION}' type='text/css'
    media='all' />
  <link rel='stylesheet' id='my_style-css' href='css/jquery.dataTables.css?v={$NUM_VERSION}' type='text/css'
    media='all' />
  <link rel='stylesheet' href='css/dataTables.fixedHeader.min.css?v={$NUM_VERSION}' type='text/css' media='all' />
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

<body>
  {include file="$contenutemplate.tpl"}

  <script>
    masquer = 0;
  </script>

  <script type='text/javascript' src='js/jquery-1.11.2.min.js?v={$NUM_VERSION}'></script>
  <script type='text/javascript' src='js/jquery-ui-1.11.4.min.js?v={$NUM_VERSION}'></script>
  <script type='text/javascript' src='js/jquery.dataTables.min.js?v={$NUM_VERSION}'></script>
  <script type='text/javascript' src='js/dataTables.fixedHeader.min.js?v={$NUM_VERSION}'></script>
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
        _paq.push(['setSiteId', '1']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <!-- End Matomo Code -->
  {/literal}

  {if $voie}
    <script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
    <script type="text/javascript" src="js/voie.js?v={$NUM_VERSION}"></script>
    <script type="text/javascript">
      SetVoie({$voie}, {$intervalle});
    </script>
  {/if}
</body>

</html>