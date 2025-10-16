{* Footer.tpl Smarty *}
<div class="footer">
{if $bPublic}
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
{else}
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
{/if}
</div>

				  
