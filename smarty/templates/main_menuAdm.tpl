{* main_menu.tpl Smarty *}	 

	<ul id="nav1" class="nav nav-pills"> 
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
	{if $currentmenu != 'Accueil'}
		<ul class="breadcrumb">
			<li class='saison'>{$smarty.config.Saison|default:'Saison'} {$Saison}</li>
			<li class='repere'>{$smarty.config.$headerTitle|default:$headerTitle}</li>
			<li class='repere'>{$smarty.config.$headerSubTitle|default:$headerSubTitle}</li>
		</ul>
	{/if}