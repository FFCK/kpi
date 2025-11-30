{* page.tpl Smarty *}{config_load file='../../commun/MyLang_processed.ini' section=$lang}
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
        <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v={$NUM_VERSION}" rel="stylesheet" type="text/css"/>
        {assign var=temp value="../css/$contenutemplate.css"}
        {if $temp|is_file}
            <link type="text/css" rel="stylesheet" href="../css/{$contenutemplate}.css?v={$NUM_VERSION}" />
        {/if}
        <style>
            /* Formulaire login plus large sur mobile */
            @media (max-width: 767px) {
                .container {
                    padding-left: 5px;
                    padding-right: 5px;
                }

                /* Forcer la largeur des colonnes sur mobile avec sélecteur plus spécifique */
                .row {
                    --bs-gutter-x: 0.5rem;
                }

                .row > .col-11 {
                    width: 91.66666667% !important;
                    flex: 0 0 auto !important;
                    max-width: 91.66666667% !important;
                    padding-left: 0.25rem;
                    padding-right: 0.25rem;
                }

                .row > .col-11.col-sm-10 {
                    width: 91.66666667% !important;
                    max-width: 91.66666667% !important;
                }

                .form-signin2 {
                    width: 100%;
                    max-width: 100%;
                }
            }

            /* Tablette */
            @media (min-width: 576px) and (max-width: 767px) {
                .row > .col-11.col-sm-10 {
                    width: 83.33333333% !important;
                    max-width: 83.33333333% !important;
                }
            }
        </style>

		<title>{$smarty.config.$title|default:$title}</title>
	</head>
	<body>

        {include file="$contenutemplate.tpl"}
        
        <script src="../js/jquery-1.11.2.min.js?v={$NUM_VERSION}" type="text/javascript"></script>
        <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v={$NUM_VERSION}" type="text/javascript"></script>
        {assign var=temp value="../js/$contenutemplate.js"} 
        {if $temp|is_file}
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