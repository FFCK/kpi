{* page.tpl Smarty *}{config_load file='../../commun/MyLang_processed.conf' section=$lang}<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
<head>
  <meta charset="utf-8" />
  <meta name="Author" Content="LG" />
  <link rel="image_src" href="https://www.kayak-polo.info/img/CNAKPI_small.png" />
  <meta name="description" content="Commission Nationale d&#039;Activité Kayak-Polo" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://www.kayak-polo.info" />
  <link rel="next" href="https://www.kayak-polo.info/?paged=2" />
  {if $bPublic}
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Kayak-polo.info">
    <meta property="og:url" content="https://www.kayak-polo.info">
    <meta property="og:site_name" content="Kayak-polo.info">
    <meta property="og:image" content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
    <meta property="og:image:secure_url"
      content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kayak-polo.info">
    <meta name="twitter:image" content="https://www.kayak-polo.info/wordpress/wp-content/uploads/2020/04/kpi_og2.png">
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
    {if isset($css_supp)}
      {assign var=temp value="css/$css_supp.css"}
      {if $css_supp && is_file($temp)}
        <link type="text/css" rel="stylesheet" href="css/{$css_supp}.css">
      {/if}
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
    {if isset($css_supp)}
      {assign var=temp value="..css/$css_supp.css"}
      {if $css_supp && is_file($temp)}
        <link type="text/css" rel="stylesheet" href="..css/{$css_supp}.css">
      {/if}
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
      var languageCode = lang; // dhtmlgoodies_calendar. Possible values: 	en,ge,no,nl,es,pt-br,fr
    </script>
    <script src="../js/dhtmlgoodies_calendar.js?random=20060118"></script>
    <script src="../js/jquery-1.5.2.min.js"></script>
    <script src="../js/jquery.autocomplete.min.js"></script>
    <script src="../js/jquery.tooltip.min.js"></script>
    <script src="../js/jquery.maskedinput.min.js"></script>
    <!--<script src="../js/jquery.fixedheadertable.min.js"></script>-->
    <script src="../js/formTools.js?v={$NUM_VERSION}"></script>
    {assign var=temp value="../js/$contenutemplate.js"}
    {if is_file($temp)}
      <script src="../js/{$contenutemplate}.js?v={$NUM_VERSION}"></script>
    {/if}
    {literal}
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

      <!-- Maintien connexion -->
      <script type="text/javascript">
        setInterval(() => {
          fetch('../check.php?' + new Date().getTime())
        }, 300000);
      </script>
      <!-- End Maintien connexion -->
    {/literal}

  {/if}
  {include file='footer.tpl'}
</body>

</html>