		<span class='repere'>&nbsp;(<a href="Calendrier.php">{#Retour#}</a>)
		<br></span>
		<div class="main">
			<form method="POST" action="Journee.php" name="formJournee" id="formJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='idEquipeA' Value=''/>
				<input type='hidden' name='idEquipeB' Value=''/>
				<input type='hidden' name='Pub' Value=''/>
				<input type='hidden' name='Verrou' Value=''/>
				
				<div class='titrePage'>{#Liste_des_Matchs#}</div>
				<div class='blocMiddle soustitrePage'>
					<table width=100%>
						<tr>
							<td>
								<label for="saisonTravail">{#Saison#} :</label>
								<select name="saisonTravail" onChange="submit()">
									{section name=i loop=$arraySaison} 
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{if $arraySaison[i].Code eq $sessionSaison}=> {/if}{$arraySaison[i].Code}</Option>
									{/section}
								</select>
								<label for="comboCompet">{#Competition#} :</label>
								<select name="comboCompet" onChange="changeCompetition();">
										<Option Value="">{#Selectionnez#}...</Option>
									{section name=i loop=$arrayCompetition} 
										{assign var='temporaire' value=$arrayCompetition[i][0]}
										<Option Value="{$arrayCompetition[i][0]}" {$arrayCompetition[i][2]}>{$smarty.config.$temporaire|default:$arrayCompetition[i][1]}</Option>
									{/section}
								</select>
								{if $Code_typeclt == 'CHPT'}
									<br>
									<label for="J">{#Journee#} :</label>
									<select name="J" id="J" onChange="submit();">
										<Option Value="*" Selected>{#Toutes#}</Option>
										{section name=i loop=$arrayChoixJournees}
												<Option Value="{$arrayChoixJournees[i].Id}" {if $idSelJournee == $arrayChoixJournees[i].Id}Selected{/if}>{$arrayChoixJournees[i].Date_debut} - {$arrayChoixJournees[i].Libelle} ({$arrayChoixJournees[i].Lieu})</Option>
										{/section}
									</select>
								{/if}
								<label for="orderMatchs">{#Tri#} :</label>
								<select name="orderMatchs" onChange="ChangeOrderMatchs();">
								{section name=i loop=$arrayOrderMatchs}
									{assign var='temporaire' value=$arrayOrderMatchs[i].Value}
									{if $orderMatchs eq $arrayOrderMatchs[i].Key}
										<Option Value="{$arrayOrderMatchs[i].Key}" Selected>{$smarty.config.$temporaire|default:$arrayOrderMatchs[i].Value}</Option>
									{else}
										<Option Value="{$arrayOrderMatchs[i].Key}">{$smarty.config.$temporaire|default:$arrayOrderMatchs[i].Value}</Option>
									{/if}
								{/section}
								</select>
							</td>
							<td align="right">
								<a href='Classements.php?Compet={$codeCompet}&Group={$codeCompetGroup}&Saison={$sessionSaison}'><img width="10" src="img/b_plus.png" alt="Classement" title="Classement" />{#Classements#}</a>
								<a href="PdfListeMatchs.php" Target="_blank" alt="{#Liste_des_Matchs#}" title="{#Liste_des_Matchs#}"><img width="16" src="img/pdf.gif" alt="{#Liste_des_Matchs#}" title="{#Liste_des_Matchs#}" />{#Liste#} (pdf)</a>
								<a href="PdfListeMatchsEN.php" Target="_blank" alt="{#Liste_des_Matchs#} (EN)" title="{#Liste_des_Matchs#} (EN)"><img width="16" src="img/pdf.gif" alt="{#Liste_des_Matchs#} (EN)" title="{#Liste_des_Matchs#} (EN)" />{#Liste#} (EN)</a>
							</td>
						</tr>
					</table>
				</div>
				<div class="centre">
					{if $Web != ''}
						<a href='{$Web}' target='_blank'>
					{/if}
					{if $LogoLink != ''}
							<img height="100" id='logo' src='{$LogoLink}' />
							<br>
					{/if}
					{if $Web != ''}
							{$Web}
						</a>
						<br>
					{/if}
				</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau tableauPublic' id='tableMatchs'>
							<thead>
								<tr>
									<th>{#Num#}</th>
									<th>{#Cat#}</th>
									<th>{#Date#} - {#Heure#}</th>
									{if $PhaseLibelle == 1}
										<th>{#Poules#}</th>
										<!--<th>{#Intitule#}</th>-->
									{else}
										<th>{#Lieu#}</th>
									{/if}
									<th>{#Terr#}</th>
									<th>{#Equipe_A#}</th>
									<th colspan=2>{#Score#}</th>
									<th>{#Equipe_B#}</th>
									<th class="arb1">{#Arbitre_1#}</th>	
									<th class="arb2">{#Arbitre_2#}</th>	
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayMatchs}
									{assign var='validation' value=$arrayMatchs[i].Validation}
									{assign var='statut' value=$arrayMatchs[i].Statut}
									{assign var='periode' value=$arrayMatchs[i].Periode}
									<tr class='{cycle values="impair2,pair2"} {$arrayMatchs[i].StdOrSelected} {$arrayMatchs[i].past}'>
											<td>{$arrayMatchs[i].Numero_ordre}</td>
											<td>{$arrayMatchs[i].Code_competition}</td>
											<td>{$arrayMatchs[i].Date_match} - {$arrayMatchs[i].Heure_match}</td>
											{if $PhaseLibelle == 1}
												<td>{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
												<!--<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>-->
											{else}
												<td>{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
											{/if}
											<td>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
											<td class="cliquableNomEquipe"><a href="Palmares.php?Equipe={$arrayMatchs[i].NumA}" title="{#Palmares#}">{$arrayMatchs[i].EquipeA|default:'&nbsp;'}</a></td>
											<td colspan=2 class="cliquableScore">
												{if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
													<a href="PdfMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank" title="{#Feuille_marque#}">
													{$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
													</a>
													<br />
													<span class="statutMatch" title="{#END#}">{#END#}</span>
												{elseif $statut == 'ON' && $validation != 'O'}
													<span class="scoreProvisoire" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													<br />
													<span class="statutMatchOn" title="{$smarty.config.$periode}">{$smarty.config.$periode}</span>
												{elseif $statut == 'END' && $validation != 'O'}
													<span class="scoreProvisoire" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													<br />
													<span class="statutMatchOn" title="{#scoreProvisoire#}">{#scoreProvisoire#}</span>
												{else}
													<br />
													<span class="statutMatchATT" title="{#ATT#}">{#ATT#}</span>
												{/if}
											</td>
											<td class="cliquableNomEquipe"><a href="Palmares.php?Equipe={$arrayMatchs[i].NumB}" title="{#Palmares#}">{$arrayMatchs[i].EquipeB|default:'&nbsp;'}</a></td>

											<td class="arb1">{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:' (':'<br>('}{else}&nbsp;{/if}</td>
											<td class="arb2">{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:' (':'<br>('}{else}&nbsp;{/if}</td>
									</tr>
								{sectionelse}
									<tr class='pair' height=30>
										<td colspan=13 align=center><i>{#Aucun_match#}</i></td>
									</tr>
								{/section}
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
		