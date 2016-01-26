 	 	<span class="repere">&nbsp;(<a href="Classements.php">{#Retour#}</a>)</span>
	
		<div class="main">
			<form method="POST" action="Historique.php" name="formHistorique" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre2'>
					<div class='titrePage'>{#Classement#}</div>
					<br>
					<div class='soustitrePage'>
						<label for="codeCompetGroup">{#Competition#} :</label>
						<select name="codeCompetGroup" onChange="submit();">
								<Option Value="">{#Selectionnez#}...</Option>
							{section name=i loop=$arrayCompetitionGroupe}
								{assign var='temporaire' value=$arrayCompetitionGroupe[i][1]}
								<Option Value="{$arrayCompetitionGroupe[i][1]}" {$arrayCompetitionGroupe[i][3]}>{$smarty.config.$temporaire|default:$arrayCompetitionGroupe[i][2]}</Option>
							{/section}
						</select>
					</div>
					<br>
					<div>
						{if $recordCompetition[0].Web != ''}
							<a href='{$recordCompetition[0].Web}' target='_blank'>
						{/if}
						{if $recordCompetition[0].LogoLink != ''}
								<img hspace="2" height="100" border="0" id='logo' src='{$recordCompetition[0].LogoLink}'>
								<br>
						{/if}
						{if $recordCompetition[0].Web != ''}
								{$recordCompetition[0].Web}
							</a>
							<br>
						{/if}
					</div>
					{if $arrayEquipe_publi[0].CodeCompet}
					<div>
						{assign var='idCompet' value=$arrayEquipe_publi[0].CodeCompet}
						{assign var='idGroupe' value=$arrayEquipe_publi[0].CodeGroupe}
						{assign var='idTour' value=$arrayEquipe_publi[0].Code_tour}
						{assign var='idSaison' value=$arrayEquipe_publi[0].CodeSaison}
						<div class='histoLigne'>
								<div class='histoColonne'>
									<br>
									<b>{$arrayEquipe_publi[0].CodeSaison}</b>
									<br>
									{if $arrayEquipe_publi[0].LogoLink != ''}
										<br>
										<img hspace="2" width="110" border="0" id='logo' src='{$arrayEquipe_publi[0].LogoLink}'>
									{/if}
								</div>
							<div class='histoColonne'>
								<table class='tableau tableauPublic'>
									<thead>
										<tr>
											<th width='130' style='word-wrap: break-word;' colspan=3 {if $arrayEquipe_publi[0].Code_tour == '10'}class='TitrePhaseFinale'{/if}>
												{if $arrayEquipe_publi[0].Titre_actif != 'O' && $arrayEquipe_publi[0].Soustitre != ''}
													{$arrayEquipe_publi[0].Soustitre}
												{else}
													{$arrayEquipe_publi[0].LibelleCompet}
												{/if}
												{if  $arrayEquipe_publi[0].Soustitre2 != ''}<br>{$arrayEquipe_publi[0].Soustitre2}{/if}
											</th>
										</tr>
									{*	<tr>
											<th width="17">&nbsp;</th>
											<th>{#Clt#}</th>
											<th>{#Equipe#}</th>
										</tr>	*}
									</thead>
									<tbody>
								{section  name=i loop=$arrayEquipe_publi}
								{if $arrayEquipe_publi[i].CltNiveau > 0 || $arrayEquipe_publi[i].Clt > 0}
									{if $arrayEquipe_publi[i].CodeCompet != $idCompet or $arrayEquipe_publi[i].CodeGroupe != $idGroupe or $arrayEquipe_publi[i].CodeSaison != $idSaison}
											</tbody>
										</table>
										<div class='centre'>
											<i>
											<a  class='grispetit' href='Classement.php?Group={$idGroupe}&Compet={$idCompet}&Saison={$idSaison}'>{#Details#}</a>
											{if $arrayEquipe_publi[i].existMatch == 1}
												&nbsp;&nbsp;&nbsp;<a  class='grispetit' href='Journee.php?Compet={$idCompet}&Saison={$idSaison}'>{#Matchs#}</a>
											{/if}
											</i>
											<br><br>
										</div>
									{if $arrayEquipe_publi[i].CodeGroupe != $idGroupe or $arrayEquipe_publi[i].CodeSaison != $idSaison or $arrayEquipe_publi[i].Code_niveau == 'INT'}</div>{/if}
									{if $arrayEquipe_publi[i].CodeSaison != $idSaison}
										</div>
										<div class='histoLigne'>
											<div class='histoColonne'>
												<br>
												<b>{$arrayEquipe_publi[i].CodeSaison}</b>
												<br>
												{if $arrayEquipe_publi[0].LogoLink != ''}
													<br>
													<img hspace="2" width="110" border="0" id='logo' src='{$arrayEquipe_publi[i].LogoLink}'>
												{/if}
											</div>
									{/if}
									{if $arrayEquipe_publi[i].CodeGroupe != $idGroupe or $arrayEquipe_publi[i].CodeSaison != $idSaison or $arrayEquipe_publi[i].Code_niveau == 'INT'}<div class='histoColonne'>{/if}
										{assign var='idCompet' value=$arrayEquipe_publi[i].CodeCompet}
										{assign var='idGroupe' value=$arrayEquipe_publi[i].CodeGroupe}
										{assign var='idTour' value=$arrayEquipe_publi[i].Code_tour }
										{assign var='idSaison' value=$arrayEquipe_publi[i].CodeSaison}
										{assign var='ordre' value=0}
										<table class='tableau tableauPublic'>
											<thead>
												<tr>
													<th width='130' style='word-wrap: break-word;' colspan=3 {if $arrayEquipe_publi[i].Code_tour == '10'}class='TitrePhaseFinale'{/if}>
														{if $arrayEquipe_publi[i].Titre_actif != 'O' && $arrayEquipe_publi[i].Soustitre != ''}
															{$arrayEquipe_publi[i].Soustitre}
														{else}
															{$arrayEquipe_publi[i].LibelleCompet}
														{/if}
														{if  $arrayEquipe_publi[i].Soustitre2 != ''}<br>{$arrayEquipe_publi[i].Soustitre2}{/if}
													</th>
												</tr>
											{*	<tr>
													<th width="17">&nbsp;</th>
													<th>{#Clt#}</th>
													<th>{#Equipe#}</th>
												</tr>	*}
											</thead>
											<tbody>
									{/if}
									{assign var='ordre' value=$ordre+1}
											<tr height="17" class='{cycle values="impair2,pair2"}'>
												{if $arrayEquipe_publi[i].Code_typeclt=='CHPT' && $arrayEquipe_publi[i].Code_tour=='10' && $arrayEquipe_publi[i].Clt <= 3 && $arrayEquipe_publi[i].Clt > 0}
													<td class='medaille'><img width="16" src="img/medal{$arrayEquipe_publi[i].Clt}.gif" alt="Podium" title="Podium" /></td>
												{elseif $arrayEquipe_publi[i].Code_typeclt=='CP' && $arrayEquipe_publi[i].Code_tour=='10' && $arrayEquipe_publi[i].CltNiveau <= 3 && $arrayEquipe_publi[i].CltNiveau > 0}
													<td class='medaille'><img width="16" src="img/medal{$arrayEquipe_publi[i].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
												{elseif $arrayEquipe_publi[i].Code_typeclt=='CHPT'}
													{if $ordre <= $arrayEquipe_publi[i].Qualifies}
														<td class='qualifie'><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
													{elseif $ordre > $arrayEquipe_publi[i].Nb_equipes - $arrayEquipe_publi[i].Elimines}
														<td class='elimine'><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
													{else}
														<td>&nbsp;</td>
													{/if}
												{else}
													{if $ordre <= $arrayEquipe_publi[i].Qualifies}
														<td class='qualifie'><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
													{elseif $ordre > $arrayEquipe_publi[i].Nb_equipes - $arrayEquipe_publi[i].Elimines}
														<td class='elimine'><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
													{else}
														<td>&nbsp;</td>
													{/if}
												{/if}
												
												{if $arrayEquipe_publi[i].Code_typeclt=='CHPT'}
													<td {if $arrayEquipe_publi[i].Code_niveau == 'INT'}width="48"{else}width="25"{/if}>
														{$arrayEquipe_publi[i].Clt}
														{if $arrayEquipe_publi[i].Code_niveau == 'INT'}
															<img width="25" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
														{/if}
													</td>
													<td class="cliquableNomEquipe" width="155">
														<a href='Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[i].Libelle}</a>
													</td>
												{else}
													<td {if $arrayEquipe_publi[i].Code_niveau == 'INT'}width="48"{else}width="25"{/if} style="text-align:right">
														{$arrayEquipe_publi[i].CltNiveau}
														{if $arrayEquipe_publi[i].Code_niveau == 'INT'}
															<img width="25" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
														{/if}
													</td>
													<td class="cliquableNomEquipe" width="155">
														<a href='Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[i].Libelle}</a>
													</td>
												{/if}
											</tr>
								{/if}
								{/section}
										</tbody>
									</table>
									<div class="centre">
										<i>
											<a class="grispetit" href='Classement.php?Group={$idGroupe}&Compet={$idCompet}&Saison={$idSaison}'>{#Details#}</a>
											{if $arrayEquipe_publi[i].existMatch == 1}
												&nbsp;&nbsp;&nbsp;<a  class='grispetit' href='Journee.php?Compet={$idCompet}&Saison={$idSaison}'>{#Matchs#}</a>
											{/if}
										</i>
										<br><br>
									</div>
								</div>
					</div>
					{else}
					<div>
						<br>
						<br>
						{#Pas_de_classement#}.
						<br>
						<br>
						<br>
					</div>
					{/if}
				</div>
			</form>			
		</div>	  	   
