{* Footer.tpl Smarty *}
<div class="container">
    <div class="footer copyright padTopBottom">
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
        <div class="flex-footer">
            <div class="btn btn-default text-center">
                <a href="https://www.facebook.com/ffckkp/" target="_blank"><img class="img-rounded" src="img/ffck_kayakpolo.jpg" alt="" height="70" border="none"></a>
                <br>La page officielle <a href="https://www.facebook.com/ffckkp/" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" border="none" width="20"></a>
                </div>

                <div class="btn btn-default text-center">
                <a href="https://www.facebook.com/KIPsport" target="_blank"><img class="img-rounded" src="wordpress/wp-content/uploads/2015/01/KIPSport.png" height="70" border="none"></a>
                <br>
                Notre partenaire <a href="https://www.facebook.com/KIPsport" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" border="none" width="20"></a> et 
                <a href="https://twitter.com/KipSport" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/twitter.png" border="none" width="20"></a>
            </div>
        </div>
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
        <div>
            <div class="col-md-6 text-info" align="center">
                <a href="https://www.facebook.com/ffckkp/" target="_blank"><img class="img-rouded" src="img/ffck_kayakpolo.jpg" alt="" width="180" border="none"></a>
                <br>La page officielle <a href="https://www.facebook.com/ffckkp/" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" border="none" width="20"></a>
                </div>

                <div class="col-md-6 text-info" align="center">
                <a href="https://www.facebook.com/KIPsport" target="_blank"><img class="img-rouded" src="wordpress/wp-content/uploads/2015/01/KIPSport.png" width="70" border="none"></a>
                <br>
                Notre partenaire <a href="https://www.facebook.com/KIPsport" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/facebook.png" border="none" width="20"></a> et 
                <a href="https://twitter.com/KipSport" target="_blank"><img src="wordpress/wp-content/plugins/social-media-feather/synved-social/image/social/regular/32x32/twitter.png" border="none" width="20"></a>
            </div>
        </div>
    {/if}
    </div>
</div>
				  
