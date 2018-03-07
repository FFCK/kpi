{* page.tpl Smarty *}

{config_load file='../../commun/MyLang.conf' section=$lang}

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		{if $bPublic}
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			<link rel="stylesheet" type="text/css" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/{$contenutemplate}.js"></script>
		{else}
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css" />
			<link rel="stylesheet" type="text/css" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/{$contenutemplate}.css" />

			<script language="JavaScript" type="text/javascript" src="../js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/{$contenutemplate}.js"></script>
		{/if}
		{literal}
			<!--<script language="JavaScript" type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAWonor80iC2LsJ4C5x7MJsBRfQyOgPKrZ8po1VXyQgkC373NrwRQugr1dZEkzcuIqpSAIryIaw67HyQ"></script>
			<script language="JavaScript" type="text/javascript">
				function loadparam() {
					if (GBrowserIsCompatible()) {
						{/literal}{*{$mapParam}*}{literal}
					}
				}
			</script>-->

		{/literal}

		<title>{$smarty.config.$title|default:$title}</title>
	</head>	  
	<body onload="testframe(); alertMsg('{$AlertMessage}')">
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
		{include file="$contenutemplate.tpl"}
		{include file='footer.tpl'}
        {literal}
        <script>
            function initMap() {
                geocoder = new google.maps.Geocoder();
                var france = {lat: 46.85, lng: 1.75};
                carte = new google.maps.Map(document.getElementById('map_canvas'), {
                    zoom: 5,
                    center: france
                });
                {/literal}{$mapParam}{literal}
                //var marker = new google.maps.Marker({
                //    position: france,
                //    map: map
                //});
            }
        </script>
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCwl3x6Qni1JEghMNAK-PGnmyk0kj-V0ws&callback=initMap">
        </script>
        {/literal}
	</body>
</html>

