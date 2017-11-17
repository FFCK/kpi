{* Header.tpl Smarty *}

{if $bPublic}
	<div id="banniere">
		<img src="img/FFCK1.gif" height=99 alt="FFCK" title="FFCK" />
	</div>
{elseif $bProd}
	<div id="banniere">
		<img src="../img/FFCK2-ADMIN.gif" height=99 alt="FFCK Administration" title="FFCK Administration" />
		<div class="connexion">
			{$userName}<br>{$user} ({#Profil#} {$profile})<br>
			{#Limite#} : {$Limit_Clubs|default:$smarty.config.Aucune}<br>
			<a href="GestionParamUser.php">{#Mes_parametres#}</a><br>
			<a href="UnLogin.php">{#Deconnexion#}</a><br>
            <a href="" id="masquer">{#Masquer#}</a><br>
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