{* Header.tpl Smarty *}

{if $bPublic}
	<div id="banniere">
		<img src="img/FFCK1.gif" height=99 alt="FFCK" title="FFCK" />
	</div>
{elseif $bProd}
	<div id="banniere">
		<div>
			{* <a class="connexion" href="GestionParamUser.php" title="{#Mes_parametres#}">{$userName} ({$profile})</a> *}
			{if $bMirror == 1}
				<br>
				<span class='vert'>Base Mirror</span>
			{/if}
		</div>
	</div>
{else}
	<div id="banniere">
		<img src="../img/FFCK2-LOCAL.gif" height=99 alt="FFCK Mode Local" title="FFCK Mode Local" />
		<div class="connexion">
			<br>
			<br>
			{$userName}<br>
			<a href="UnLogin.php">{#Deconnexion#}</a><br>
		</div>
	</div>
{/if}