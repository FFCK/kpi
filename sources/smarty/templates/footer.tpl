{* Footer.tpl Smarty *}
<div class="footer">
{if $bPublic}
	<div class="Left3">
		<a href="https://www.facebook.com/ffckkp/" target="_blank">
            <img src="img/ffck_kayakpolo.jpg" height=120 border=none alt=""/>
		</a>
		<br />
		La page officielle 
		<a href="https://www.facebook.com/ffckkp/" target="_blank">
			<img src="img/facebook.png" width="20" border="none">
		</a>
    </div>
    <div class="Right4">
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="img/KIPSport.png" height=120 border=none>
		</a>
		<br />
		Notre partenaire 
		<a href="https://www.facebook.com/KIPsport" target="_blank">
			<img src="img/facebook.png" width="20" border="none">
		</a> et 
		<a href="https://twitter.com/KipSport" target="_blank">
			<img src="img/twitter.png" width="20" border="none">
		</a>
	</div>
{else}
	<div class="Left3">
		<a href="https://www.facebook.com/ffckkp/" target="_blank"><img src="../img/ffck_kayakpolo.jpg" height=80 border=none alt=""/></a>
		<br />
		{#La_page_officielle#} 
		<a href="https://www.facebook.com/ffckkp/" target="_blank"><img src="../img/facebook.png" width="20" border="none"></a>
    </div>
    <div class="Right4">
		<a href="https://www.facebook.com/KIPsport" target="_blank"><img src="../img/KIPSport.png" height=80 border=none></a>
		<br />
		{#Notre_partenaire#} 
		<a href="https://www.facebook.com/KIPsport" target="_blank"><img src="../img/facebook.png" width="20" border="none"></a>
        & 
		<a href="https://twitter.com/KipSport" target="_blank"><img src="../img/twitter.png" width="20" border="none"></a>
	</div>
{/if}

{*<script>console.log('TZ : {$tzOffset}')</script>*}
</div>

				  
