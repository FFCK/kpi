 	 	<span class="repere">&nbsp;(<a href="index.php">{#Retour#}</a>)</span>
	
		<div class="main">
			<form method="POST" action="Classements.php" name="formClassement" enctype="multipart/form-data">
				{literal}
					<div id="fb-root"></div>
					<script>(function(d, s, id) {
						  var js, fjs = d.getElementsByTagName(s)[0];
						  if (d.getElementById(id)) return;
						  js = d.createElement(s); js.id = id;
						  js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0";
						  fjs.parentNode.insertBefore(js, fjs);
						}(document, 'script', 'facebook-jssdk'));
					</script>
				{/literal}
				<input type='hidden' name='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' Value='' />
				<div class='blocCentre'>
					<div class='titrePage'>{#Classement#}</div>
					<br />
					<div class='soustitrePage'>
						<label>{#Saison#} :</label>
						<select name="saisonTravail" id="saisonTravail" onChange="submit()">
							{section name=i loop=$arraySaison} 
								<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{if $arraySaison[i].Code eq $sessionSaison}=> {/if}{$arraySaison[i].Code}</Option>
							{/section}
						</select>
						<label>{#Competition#} :</label>
						<select name="codeCompetGroup" id="codeCompetGroup" onChange="submit();">
								<Option Value="">{#Selectionnez#}...</Option>
							{section name=i loop=$arrayCompetitionGroupe}
								{assign var='temporaire' value=$arrayCompetitionGroupe[i][1]}
								<Option Value="{$arrayCompetitionGroupe[i][1]}" {$arrayCompetitionGroupe[i][3]}>{$smarty.config.$temporaire|default:$arrayCompetitionGroupe[i][2]}</Option>
							{/section}
						</select>
						<a href='Historique.php?Compet={$idCompet}'>{#Historique#}...</a>
						<div class="fb-like" data-href="http://www.kayak-polo.info/Classements.php?Group={$codeCompetGroup}&Saison={$sessionSaison}" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
					</div>
					<br />
					<div class="centre">
						{if $recordCompetition[0].Web != ''}
							<a href='{$recordCompetition[0].Web}' target='_blank'>
						{/if}
						{if $recordCompetition[0].LogoLink != ''}
								<img class="img2" width="700" id='logo' src='{$recordCompetition[0].LogoLink}' alt="logo" />
								<br />
						{/if}
						{if $recordCompetition[0].Web != ''}
								{$recordCompetition[0].Web}
							</a>
							<br />
						{/if}
					</div>
					{if $arrayEquipe_publi[0].CodeCompet}
					<div>
						{assign var='idCompet' value=$arrayEquipe_publi[0].CodeCompet}
						{assign var='idTour' value=$arrayEquipe_publi[0].Code_tour}
						{assign var='idSaison' value=$arrayEquipe_publi[0].CodeSaison}
						<div class='droite'>
							<a href='Classement.php?Compet={$idCompet}'>{#Details#}...</a>
							{if $arrayEquipe_publi[0].existMatch == 1}
								&nbsp;<a href='Journees.php?Compet={$idCompet}'>{#Matchs#}...</a>
							{/if}
						</div>
						<table class='tableau tableauPublic'>
							<thead>
								{if $arrayEquipe_publi[0].Titre_actif != 'O' && $arrayEquipe_publi[0].Soustitre2 != ''}
									<tr>
										<th colspan=12 {if $arrayEquipe_publi[0].Code_tour == '10'}class='TitrePhaseFinale'{/if}>{$arrayEquipe_publi[0].Soustitre}
											<br />{$arrayEquipe_publi[0].Soustitre2}
											{if $arrayEquipe_publi[0].Statut != 'END'}<br /><span class="gris">- {#PROVISOIRE#} -</span>{/if}
										</th>
									</tr>
								{else}
									<tr>
										<th colspan=12 {if $arrayEquipe_publi[0].Code_tour == '10'}class='TitrePhaseFinale'{/if}>{$arrayEquipe_publi[0].CodeSaison} - {$arrayEquipe_publi[0].LibelleCompet}<br />{$arrayEquipe_publi[0].Soustitre2}</th>
									</tr>
								{/if}
								{if $arrayEquipe_publi[0].Code_typeclt=='CHPT'}
									<tr>
										<th colspan=2>&nbsp;</th>
										<th>&nbsp;</th>
										<th>{#Pts#}</th>
										<th>{#J#}</th>
										<th>{#G#}</th>
										<th>{#N#}</th>
										<th>{#P#}</th>
										<th>{#F#}</th>
										<th>+</th>
										<th>-</th>
										<th>{#Diff#}</th>
									</tr>
								{else}
								{*	<th colspan=2 width="12%">{#Clt#}</th>
									<th width="88%">{#Equipe#}</th>	*}
								{/if}
							</thead>
							<tbody>
						{section  name=i loop=$arrayEquipe_publi}
							{if $arrayEquipe_publi[i].CodeCompet != $idCompet or $arrayEquipe_publi[i].CodeSaison != $idSaison}
									</tbody>
								</table>
								{if $arrayEquipe_publi[i].Code_tour != $idTour}
									<br />
									<br />
								{/if}
								{assign var='idCompet' value=$arrayEquipe_publi[i].CodeCompet}
								{assign var='idTour' value=$arrayEquipe_publi[i].Code_tour }
								{assign var='idSaison' value=$arrayEquipe_publi[i].CodeSaison}
								{assign var='ordre' value=0}
								<div class='droite'><a href='Classement.php?Compet={$idCompet}'>{#Details#}...</a>
								{if $arrayEquipe_publi[i].existMatch == 1}
									&nbsp;<a href='Journee.php?Compet={$idCompet}'>{#Matchs#}...</a>
								{/if}
								</div>
								<table class="tableau tableauPublic">
									<thead>
										{if $arrayEquipe_publi[i].Titre_actif != 'O' && $arrayEquipe_publi[i].Soustitre2 != ''}
											<tr>
												<th colspan=12 {if $arrayEquipe_publi[i].Code_tour == '10'}class="TitrePhaseFinale"{/if}>{$arrayEquipe_publi[i].Soustitre}
													<br />{$arrayEquipe_publi[i].Soustitre2}
													{if $arrayEquipe_publi[i].Statut != 'END'}<br /><span class="gris">- {#PROVISOIRE#} -</span>{/if}
												</th>
											</tr>
										{else}
											<tr>
												<th colspan=12 {if $arrayEquipe_publi[i].Code_tour == '10'}class="TitrePhaseFinale"{/if}>{$arrayEquipe_publi[i].CodeSaison} - {$arrayEquipe_publi[i].LibelleCompet}<br />{$arrayEquipe_publi[i].Soustitre2}</th>
											</tr>
										{/if}
										<tr>
											{if $arrayEquipe_publi[i].Code_typeclt=='CHPT'}
												<th colspan=2>{#Clt#}</th>
												<th>{#Equipe#}</th>
												<th>{#Pts#}</th>
												<th>{#J#}</th>
												<th>{#G#}</th>
												<th>{#N#}</th>
												<th>{#P#}</th>
												<th>{#F#}</th>
												<th>+</th>
												<th>-</th>
												<th>{#Diff#}</th>
											{else}
											{*	<th colspan=2 width="12%">{#Clt#}</th>
												<th width="88%">{#Equipe#}</th>	*}
											{/if}
										</tr>
									</thead>
									<tbody>
							{/if}
							{assign var='ordre' value=$ordre+1}
									<tr class='{cycle values="impair2,pair2"}'>
										{if $arrayEquipe_publi[i].Code_typeclt=='CHPT' && $arrayEquipe_publi[i].Code_tour=='10' && $arrayEquipe_publi[i].Clt <= 3 && $arrayEquipe_publi[i].Clt > 0 && $arrayEquipe_publi[i].Statut == 'END'}
											<td class='medaille'><img width="16" height="16" src="img/medal{$arrayEquipe_publi[i].Clt}.gif" alt="Podium" title="Podium" /></td>
										{elseif $arrayEquipe_publi[i].Code_typeclt=='CP' && $arrayEquipe_publi[i].Code_tour=='10' && $arrayEquipe_publi[i].CltNiveau <= 3 && $arrayEquipe_publi[i].CltNiveau > 0 && $arrayEquipe_publi[i].Statut == 'END'}
											<td class='medaille'><img width="16" height="16" src="img/medal{$arrayEquipe_publi[i].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
										{elseif $arrayEquipe_publi[i].Code_typeclt=='CHPT'}
											{if $ordre <= $arrayEquipe_publi[i].Qualifies}
												<td class='qualifie'><img width="16" height="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
											{elseif $ordre > $arrayEquipe_publi[i].Nb_equipes - $arrayEquipe_publi[i].Elimines}
												<td class='elimine'><img width="16" height="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
											{else}
												<td>&nbsp;</td>
											{/if}
										{else}
											{if $ordre <= $arrayEquipe_publi[i].Qualifies}
												<td class='qualifie'><img width="16" height="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
											{elseif $ordre > $arrayEquipe_publi[i].Nb_equipes - $arrayEquipe_publi[i].Elimines}
												<td class='elimine'><img width="16" height="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
											{else}
												<td>&nbsp;</td>
											{/if}
										{/if}
										
										{if $arrayEquipe_publi[i].Code_typeclt=='CHPT'}
											<td class="droite">
												{$arrayEquipe_publi[i].Clt}
												{if $arrayEquipe_publi[i].Code_niveau == 'INT'}
													<img class="img2" width="25" height="16" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
												{/if}
											</td>
											<td class="cliquableNomEquipe"><a href='Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[i].Libelle}</a></td>
											<td>{$arrayEquipe_publi[i].Pts/100}</td>
											<td>{$arrayEquipe_publi[i].J}</td>
											<td>{$arrayEquipe_publi[i].G}</td>
											<td>{$arrayEquipe_publi[i].N}</td>
											<td>{$arrayEquipe_publi[i].P}</td>
											<td>{$arrayEquipe_publi[i].F}</td>
											<td>{$arrayEquipe_publi[i].Plus}</td>
											<td>{$arrayEquipe_publi[i].Moins}</td>
											<td>{$arrayEquipe_publi[i].Diff}</td>
										{else}
											<td class="droite">
												{$arrayEquipe_publi[i].CltNiveau}.
												{if $arrayEquipe_publi[i].Code_niveau == 'INT'}
													<img class="img2" width="25" height="16" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
												{/if}
											</td>
											<td class="cliquableNomEquipe"><a href='Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[i].Libelle}</a></td>
										{/if}
									</tr>
						{/section}
								</tbody>
							</table>
					</div>
					{else}
					<div>
						<br />
						<br />
						{#Pas_de_classement#}.
						<br />
						<br />
						<br />
					</div>
					{/if}
				</div>
			</form>			
		</div>	  	   
