{* Header.tpl Smarty *}

{if $bPublic}
	<div id="banniere">
		<img src="img/FFCK1.gif" height=99 alt="FFCK" title="FFCK" />
	</div>
{elseif $bProd}
	<div id="banniere">
		<img src="../img/FFCK2-ADMIN.gif" height=99 alt="FFCK Administration" title="FFCK Administration" />
		<div class="connexion">
			{$userName}<br>Adh.{$user} (profil {$profile})<br>
			Limite : {$Limit_Clubs|default:'Aucune'}<br>
			<a href="GestionParamUser.php">Mes paramètres</a><br>
			<a href="UnLogin.php">Déconnexion</a><br>
            <a href="" id="masquer" title="Masquer la bannière">Masquer</a><br>
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
			<a href="UnLogin.php">Déconnexion</a><br>
		</div>
	</div>
{/if}