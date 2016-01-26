<?php /* Smarty version 2.6.18, created on 2015-04-07 07:01:10
         compiled from footer.tpl */ ?>
<div class="footer">
<?php if ($this->_tpl_vars['bPublic']): ?>
	<?php echo '
		<!-- Piwik -->
			<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.poloweb.org/piwik/" : "http://www.poloweb.org/piwik/");
			document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
			</script><script type="text/javascript">
			try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
			} catch( err ) {}
			</script><noscript><p><img src="http://www.poloweb.org/piwik/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Tracking Code -->
	'; ?>

	<center>
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="wordpress/wp-content/uploads/2015/01/KIPSport.png" width=120 border=none>
		</a>
		<br />
		Suivez notre partenaire info sur 
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" width="20" border="none">
		</a> et 
		<a href="https://twitter.com/KipSport" target="_blank">
			<img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/twitter.png" width="20" border="none">
		</a>
	</center>
<?php else: ?>
	<?php echo '
		<!-- Piwik -->
			<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.poloweb.org/piwik/" : "http://www.poloweb.org/piwik/");
			document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
			</script><script type="text/javascript">
			try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 2);
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
			} catch( err ) {}
			</script><noscript><p><img src="http://www.poloweb.org/piwik/piwik.php?idsite=2" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Tracking Code -->
	'; ?>

	<center>
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="../wordpress/wp-content/uploads/2015/01/KIPSport.png" width=120 border=none>
		</a>
		<br />
		Suivez notre partenaire info sur 
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="../wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" width="20" border="none">
		</a> et 
		<a href="https://twitter.com/KipSport" target="_blank">
			<img src="../wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/twitter.png" width="20" border="none">
		</a>
	</center>
<?php endif; ?>
</div>

				  