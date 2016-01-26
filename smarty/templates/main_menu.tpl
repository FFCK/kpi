{* main_menu.tpl Smarty *}	 

<!--
	<div id="boutonsH">
	<div id="nav2Left"></div>
	<ul id="nav2">
-->
	<ul id="nav">
		{section name=i loop=$arraymenu} 
			{assign var='temporaire' value=$arraymenu[i].name}
			{if $currentmenu eq $arraymenu[i].name}
				<li class="current"><a href="{$arraymenu[i].href}">{$smarty.config.$temporaire|default:$temporaire}</a></li>
			{else}
				<li {if $arraymenu[i].name == 'Forum' || $arraymenu[i].name == 'Accueil Public'}class="forum"{/if}>
					<a href="{$arraymenu[i].href}">{$smarty.config.$temporaire|default:$temporaire}</a>
				</li>
			{/if}
			
		{/section}
		{if $bPublic}
			<li {if $lang == 'EN'} class="current"{/if}><a href="?lang=EN"><img width="22" src="img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
			<li {if $lang == 'FR'} class="current"{/if}><a href="?lang=FR"><img width="22" src="img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
		{else}
			<li {if $lang == 'EN'} class="current"{/if}><a href="?lang=EN"><img width="22" src="../img/Pays/GBR.png" alt="EN" title="EN" /></a></li>
			<li {if $lang == 'FR'} class="current"{/if}><a href="?lang=FR"><img width="22" src="../img/Pays/FRA.png" alt="FR" title="FR" /></a></li>
		{/if}
	</ul>
<!--	
	<div id="nav2Right"></div>
	</div>
	<br />
-->
	{if $currentmenu != 'Accueil'}
		<span class='saison'>{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
		<span class='repere'>{$smarty.config.$headerTitle|default:$headerTitle}</span>
		<span class='repere'>></span>
		<span class='repere'>{$smarty.config.$headerSubTitle|default:$headerSubTitle}</span>
	{/if}
