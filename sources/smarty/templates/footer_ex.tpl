{* Footer.tpl Smarty *}
<div class="footer">
{if $bPublic}
	{literal}
		<!-- Piwik -->
			<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.poloweb.org/piwik/" : "http://www.poloweb.org/piwik/");
			document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
			</script><script type="text/javascript">
			try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
			} catch( err ) {}
			</script><noscript><p><img src="http://www.poloweb.org/piwik/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Tracking Code -->
	{/literal}
{else}
	{literal}
		<!-- Piwik -->
			<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.poloweb.org/piwik/" : "http://www.poloweb.org/piwik/");
			document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
			</script><script type="text/javascript">
			try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 2);
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
			} catch( err ) {}
			</script><noscript><p><img src="http://www.poloweb.org/piwik/piwik.php?idsite=2" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Tracking Code -->
	{/literal}
{/if}
</div>

				  
