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
			<li {if $lang == 'en'} class="current"{/if}><a href="?lang=en"><img width="22" src="img/Pays/GBR.png" alt="en" title="en" /></a></li>
			<li {if $lang == 'fr'} class="current"{/if}><a href="?lang=fr"><img width="22" src="img/Pays/FRA.png" alt="fr" title="fr" /></a></li>
		{else}
			<li {if $lang == 'en'} class="current"{/if}><a href="?lang=en"><img width="22" src="../img/Pays/GBR.png" alt="en" title="en" /></a></li>
			<li {if $lang == 'fr'} class="current"{/if}><a href="?lang=fr"><img width="22" src="../img/Pays/FRA.png" alt="fr" title="fr" /></a></li>
            {if $currentmenu == 'Matchs'}
                <li class="hideall"><a href=""><img src="../img/hideall2.png" height="14" alt="Masquer tout" title="Masquer tout" ></a></li>
            {/if}
		{/if}
	</ul>
<!--	
	<div id="nav2Right"></div>
	</div>
	<br />
-->
	{if $currentmenu != 'Accueil'}
        {assign var="headerTitle0" value=$headerTitle|replace:' ':'_'}
		<span class='saison'>{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
		<span class='repere'>{$smarty.config.$headerTitle0|default:$headerTitle}</span>
		{if $headerSubTitle}
            {assign var="headerSubTitle0" value=$headerSubTitle|replace:' ':'_'}
            <span class='repere'>></span>
            <span class='repere'>{$smarty.config.$headerSubTitle0|default:$headerSubTitle}</span>
        {/if}
	{/if}
