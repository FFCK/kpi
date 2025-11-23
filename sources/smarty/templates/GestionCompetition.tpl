		<div class="main">
			<form method="POST" action="GestionCompetition.php" name="formCompet" id="formCompet" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id="Cmd" Value='' />
				<input type='hidden' name='ParamCmd' id="ParamCmd" Value='' />
				<input type='hidden' name='verrouCompet' id="verrouCompet" Value='' />
				<input type='hidden' name='Verrou' id="Verrou" Value='' />
				<input type='hidden' name='Pub' id="Pub" Value='' />

				{if $profile != 9}
					<div class='blocLeft'>
						<h3 class='titrePage'>{#Competitions_poules#}</h3>
						<br>
						<div class='liens'>
							<label for="saisonTravail">{#Saison#} :</label>
							<select name="saisonTravail" id="saisonTravail" onChange="sessionSaison()">
								{section name=i loop=$arraySaison}
									<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>
										{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} ({#Travail#}){/if}</Option>
								{/section}
							</select>
							<label for="AfficheCompet">{#Afficher#} :</label>
							<select name="AfficheNiveau" onChange="changeAffiche()">
								<Option Value="" selected>{#Tous_les_niveaux#}</Option>
								<Option Value="INT" {if $AfficheNiveau == 'INT'} selected{/if}>{#Competitions_Internationales#}</Option>
								<Option Value="NAT" {if $AfficheNiveau == 'NAT'} selected{/if}>{#Competitions_Nationales#}</Option>
								<Option Value="REG" {if $AfficheNiveau == 'REG'} selected{/if}>{#Competitions_Regionales#}</Option>
							</select>
							<select name="AfficheCompet" onChange="changeAffiche()">
								<Option Value="" selected>{#Toutes_les_competitions#}</Option>
								<Option Value="N" {if $AfficheCompet == 'N'} selected{/if}>{#Championnat_de_France#}</Option>
								<Option Value="CF" {if $AfficheCompet == 'CF'} selected{/if}>{#Coupe_de_France#}</Option>
								{section name=i loop=10}
									{if isset($sectionLabels[i])}
										{assign var='temp' value=$sectionLabels[i]}
										<Option Value="{$smarty.section.i.index}" {if $AfficheCompet == $smarty.section.i.index} selected{/if}>
											{$smarty.config.$temp|default:$temp}</Option>
									{/if}
								{/section}
								{if $profile <= 4}
									<Option Value="M" {if $AfficheCompet == 'M'} selected{/if}>{#Modeles#}</Option>
								{/if}
							</select>
						</div>
						<div class='blocTable' id='blocCompet'>
							<table class='tableau' id='tableCompet'>
								<thead>
									<tr>
										<th width=18><img hspace="2" width="19" height="16" src="../img/oeil2.gif" title="{#Publier#} ?"
												border="0"></th>
										<th width=63 title="Code">Code</th>
										<th title="{#Modifier#}">&nbsp;</th>
										<th title="{#Niveau#}">{#Niv#}</th>
										<th colspan=2>{#Nom#}</th>
										<th width=63>{#Groupe#}</th>
										<th title="{#Tour#}/Phase">{#Tour#}</th>
										<th>Type</th>
										<th title="{#Statut#}">{#Statut#}</th>
										<th>{#Equipes#}</th>
										<th><img width="19" height="16" src="../img/verrou2.gif"
												title="{#Verrouiller#} {#Feuilles_de_presence#}" border="0"></th>
										<!--
									<th><img width="16" height="16" src="../img/up.gif" alt="Nb d'équipes qualifiées" title="Nb d'équipes qualifiées" border="0"></th>
									<th><img width="16" height="16" src="../img/down.gif" alt="Nb d'équipes éliminées" title="Nb d'équipes éliminées" border="0"></th>
									-->
										<th title="{#Nb_matchs#}">{#Matchs#}</th>
										<th title="{#Suppression#}">&nbsp;</th>
									</tr>
								</thead>

								<tbody>
									{assign var='j' value=null}
									{section name=i loop=$arrayCompet}
										{if $arrayCompet[i].section != $j}
											{assign var='sectionLabel' value=$arrayCompet[i].sectionLabel}
											<tr class="gris2">
												<th colspan="14">{$smarty.config.$sectionLabel|default:$sectionLabel}</th>
											</tr>
										{/if}
										{assign var='j' value=$arrayCompet[i].section}
										<tr class='{cycle values="impair,pair"} {$arrayCompet[i].StdOrSelected}'>
											{*
										<td><input type="checkbox" name="checkCompet" value="{$arrayCompet[i].Code}" id="checkDelete{$smarty.section.i.iteration}" /></td>
										*}

											<td class='color{$arrayCompet[i].Publication}2'>
												{if $profile <= 4 && $AuthModif == 'O' && $arrayCompet[i].Code_ref != 'M'}
													<img class="publiCompet" data-valeur="{$arrayCompet[i].Publication}" data-id="{$arrayCompet[i].Code}"
														width="24" src="../img/oeil2{$arrayCompet[i].Publication|default:'N'}.gif"
														title="{if $arrayCompet[i].Publication == 'O'}{#Public#}{else}{#Prive#}{/if}" />
												{elseif $arrayCompet[i].Code_ref != 'M'}
													<img width="24" src="../img/oeil2{$arrayCompet[i].Publication}.gif"
														title="{if $arrayCompet[i].Publication == 'O'}{#Public#}{else}{#Prive#}{/if}" />
												{else}-
												{/if}
											</td>
											<td>{$arrayCompet[i].Code}</td>
											{if $profile <= 3 && $AuthModif == 'O'}
												<td><a href="#" Id="Param{$arrayCompet[i].Code}" onclick="paramCompet('{$arrayCompet[i].Code}')"><img
															hspace="2" width="18" height="18" src="../img/glyphicons-31-pencil.png" title="{#Editer#}"
															border="0"></a></td>
											{else}<td>&nbsp;</td>
											{/if}
											<td>{$arrayCompet[i].Code_niveau|default:'&nbsp;'}</td>
											<td>
												<a href="GestionRc.php?Compet={$arrayCompet[i].Code}" title="{#RC#}">
													<img src="../img/orga{$arrayCompet[i].rcs}.png" alt="RC" height="18">
												</a>
											</td>
											<td class="cliquableNomEquipe" title="{if isset($arrayCompet[i].checktitre) && $arrayCompet[i].checktitre == 'O'}{$arrayCompet[i].Libelle}{else}{$arrayCompet[i].Soustitre}{/if}
											| {$arrayCompet[i].Soustitre2}
											| {#Qualifies#} : {$arrayCompet[i].Qualifies}
											| {#Elimines#} : {$arrayCompet[i].Elimines}
											| {$arrayCompet[i].commentairesCompet}
											"><a href='GestionDoc.php?Compet={$arrayCompet[i].Code}'>{if isset($arrayCompet[i].checktitre) && $arrayCompet[i].checktitre != 'O' && $arrayCompet[i].Soustitre != ''}{$arrayCompet[i].Soustitre}{else}{$arrayCompet[i].Libelle}{/if}{if $arrayCompet[i].Soustitre2 != ''}<br />{$arrayCompet[i].Soustitre2}{/if}</a>
											</td>
											<td>{$arrayCompet[i].Code_ref|default:'&nbsp;'}</td>
											<td>{if $arrayCompet[i].Code_tour == '10'}F{else}{$arrayCompet[i].Code_tour|default:'&nbsp;'}{/if}</td>
											<td>{$arrayCompet[i].codeTypeClt|default:'&nbsp;'}</td>
											<td title="{#Detail_statut#}">
												{if $profile <= 3 && $AuthModif == 'O'}
													<span class="statutCompet statutCompet{$arrayCompet[i].Statut}"
														data-id="{$arrayCompet[i].Code}">{$arrayCompet[i].Statut}</span>
												{else}
													<span class="statutCompet{$arrayCompet[i].Statut}">{$arrayCompet[i].Statut}</span>
												{/if}
											</td>
											<td>{$arrayCompet[i].Nb_equipes|default:'&nbsp;'}</td>
											<td title="{#Verrouiller#} {#Feuilles_de_presence#}">
												{if $profile <= 3 && $AuthModif == 'O'}
													<img class="verrouCompet" data-valeur="{$arrayCompet[i].Verrou}" data-id="{$arrayCompet[i].Code}"
														width="24" src="../img/verrou2{$arrayCompet[i].Verrou|default:'N'}.gif">
												{else}
													<img width="24" src="../img/verrou2{$arrayCompet[i].Verrou|default:'N'}.gif">
												{/if}
											</td>
											<td>{$arrayCompet[i].nbMatchs|default:'&nbsp;'}</td>
											{if $profile <= 2 && $AuthModif == 'O'}
												<td><a href="#" onclick="RemoveCheckbox('formCompet', '{$arrayCompet[i].Code}');return false;"><img
															height="20" src="../img/glyphicons-17-bin.png" title="{#Supprimer#}" border="0"></a></td>
											{else}<td>&nbsp;</td>
											{/if}
										</tr>
									{/section}
								</tbody>
							</table>
						</div>
					</div>
				{/if}
				<div class='blocRight'>
					{if $profile == 9}
				</form>
				<form method="GET" action="FeuilleMarque2.php" name="formCompet" enctype="multipart/form-data">
					<table width="100%" class='vert'>
						<tr>
							<th class='titreForm' colspan=2>
								<label class='maxWith'>{#Acces_direct3#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="accesFeuille" class='maxWith'>{#Identifiant_match#} : </label>
							</td>
						</tr>
						<tr>
							<td width="60%">
								<input class='maxWith newInput' type="tel" name="idMatch" maxlength=15 id="idMatch" />
							</td>
							<td>
								<input class='maxWith newBtn' type="submit" value="Go" />
							</td>
						</tr>
					</table>
				</form>
			{elseif $profile <= 6}
				<table width="100%" class='vert'>
					<tr>
						<th class='titreForm' colspan=2>
							<label class='maxWith'>{#Acces_direct3#}</label>
						</th>
					</tr>
					<tr>
						<td colspan="2">
							<a href="FeuilleMarque2.php" target="_blank" id="accesFeuillelink">
								<button class='maxWith newBtn' type="button" name="accesFeuilleButton"
									id="accesFeuilleButton">{#Feuille_marque#}</button>
							</a>
						</td>
					</tr>
				</table>
			{/if}
			{if $profile <= 3 && $AuthModif == 'O'}
				<table width="100%">
					<tr>
						<th class='titreForm' colspan=4>
							<label
								class='maxWith'>{if $editCompet == ''}{#Ajouter_une_competition#}{else}{#Modifier_la_competition#}{/if}</label>
						</th>
					</tr>
					{if $editCompet == ''}
						<tr>
							<td colspan=4>
								<label for="choixCompet" class='maxWith'>{#Chercher#} : </label>
								<input class='maxWith' type="text" name="choixCompet" maxlength=50 id="choixCompet" placeholder="Code">
							</td>
						</tr>
						<tr>
							<td width=55% colspan=2>
								<label for="codeCompet">Code :</label>
					<input type="text" name="codeCompet" id="codeCompet" maxlength=12 class='codecompet{if $profile <= 2} gris{/if}'
										{if $profile > 2}readonly{/if} {if $editCompet != ''}value="{$codeCompet}" {/if} />
								</td>
								<td colspan=2>
									<label for="niveauCompet">{#Niveau#} : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG" {if $niveauCompet == 'REG'} selected{/if}>REG-Regional</Option>
										<Option Value="NAT" {if $niveauCompet == 'NAT' or $niveauCompet == ''} selected{/if}>NAT-National</Option>
										<Option Value="INT" {if $niveauCompet == 'INT'} selected{/if}>INT-International</Option>
									</select>
								</td>
							</tr>
						{else}
							<tr>
								<td width=55% colspan=2>
									<label for="codeCompet">Code :</label>
									<input type="text" name="codeCompet" maxlength=12 id="codeCompet" readonly value="{$codeCompet}" />
								</td>
								<td colspan=2>
									<label for="niveauCompet">{#Niveau#} : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG" {if $niveauCompet == 'REG'} selected{/if}>REG-Regional</Option>
										<Option Value="NAT" {if $niveauCompet == 'NAT' or $niveauCompet == ''} selected{/if}>NAT-National</Option>
										<Option Value="INT" {if $niveauCompet == 'INT'} selected{/if}>INT-International</Option>
									</select>
								</td>
							</tr>
						{/if}
						<tr>
							<td colspan=4>
								<label for="labelCompet">Label : </label>
								<input type="text" name="labelCompet" value="{$labelCompet}" maxlength=50 id="labelCompet"
									{if $profile <= 2}class='gris' {else}readonly{/if} />
							</td>
						</tr>
						<tr>
							<td colspan=4 title='{#Exemple#} : <br>ICF World Championships - Milan (ITA)<br>'>
								<hr>
								<label for="soustitre">Label 2<br>
									<i>{#Titre_public#}</i></label>
								<input type="text" name="soustitre" id="soustitre" maxlength=80 value="{$soustitre}" />
							</td>
						</tr>
						<tr>
							<td colspan=4 title='{#Exemple#} : <br>Women U21, Men, Tournoi 1, 2nd Division<br>'>
								<label for="soustitre2">{#Categorie#}<br>
									<i>Men, Women U21, Tournoi 1...</i></label>
								<input type="text" name="soustitre2" id="soustitre2" maxlength=80 value="{$soustitre2}" />
							</td>
						</tr>
						<tr>
							<td colspan=3>
								<label for="codeRef">{#Groupe#} :</label>
								<select name="codeRef" id="codeRef">
									{section name=i loop=$arrayGroupCompet}
										{assign var='options' value=$arrayGroupCompet[i].options}
										{assign var='label' value=$arrayGroupCompet[i].label}
										<optgroup label="{$smarty.config.$label|default:$label}">
											{section name=j loop=$options}
												{assign var='optionLabel' value=$options[j].Groupe}
												<Option Value="{$options[j].Groupe}" {if $options[j].Groupe == $codeRef} selected{/if}>
													{$options[j].Groupe} - {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
											{/section}
										</optgroup>
									{/section}
								</select>
							</td>
							<td>
								<label for="groupOrder">{#Ordre#} :</label>
								<input type="tel" name="groupOrder" id="groupOrder" value="{$groupOrder}" maxlength=1 size="2"/>
							</td>
						</tr>
						<tr>
							<td colspan=4>
								<label for="codeTypeClt">Type : </label>
								<select name="codeTypeClt" id="codeTypeClt" onChange="changeCodeTypeClt();">
									{section name=i loop=$arrayTypeClt}
										<Option Value="{$arrayTypeClt[i][0]}" {if $arrayTypeClt[i][0] == $codeTypeClt} selected{/if}>
											{$arrayTypeClt[i][1]}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr id="pointsGridRow" style="display:{if $codeTypeClt == 'MULTI'}table-row{else}none{/if};">
							<td colspan=4 title='{#Exemple_grille_points_MULTI#}: {ldelim}"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0{rdelim}'>
								<label for="pointsGrid">{#Grille_de_points_MULTI#} : </label>
								<input type="text" name="pointsGrid" id="pointsGrid" maxlength=255 value="{$pointsGrid}"
									{if $profile > 2}readonly{/if} {if $profile <= 2}class='gris'{/if}
									placeholder='{ldelim}"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0{rdelim}' />
								<br><small><i>{#Format_JSON#} : {ldelim}"1":10,"2":6,"3":4,"default":0{rdelim}</i></small>
							</td>
						</tr>
						<tr id="multiCompetitionsRow" style="display:{if $codeTypeClt == 'MULTI'}table-row{else}none{/if};">
							<td colspan=4>
								<label for="multiCompetitions">{#Competitions_sources_MULTI#} : </label>
								<input type="hidden" name="multiCompetitions" id="multiCompetitionsHidden" value="" />
								<select name="multiCompetitionsSelect[]" id="multiCompetitionsSelect" multiple size="15" style="width:100%"
									{if $profile > 2}disabled{/if}>
									{foreach from=$competsBySection key=sectionKey item=sectionData}
										<optgroup label="{$sectionData.sectionLabel}">
											{foreach from=$sectionData.competitions item=compet}
												<option value="{$compet.Code}" {if $compet.Selected}selected{/if}>
													{$compet.Code} - {$compet.Libelle} ({$compet.Type})
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
								<br><small><i>{#Multi_select_instruction#}</i></small>
							</td>
						</tr>
						<tr>
							<td colspan=2 width=55%>
								<label for="etape">{#Tour#}/Phase :</label>
								<select name="etape" id="etape">
									{section name=i loop=6 start=1}
										<Option Value="{$smarty.section.i.index}" {if $smarty.section.i.index == $etape} selected{/if}>
											{$smarty.section.i.index}</Option>
									{/section}
									<Option Value="10" {if $etape == 10 or $etape == ''} selected{/if}>Unique/{#Finale#}</Option>
								</select>
							</td>
							<td>
								<label for="qualifies">{#Qualifies#}</label>
								<input type="tel" name="qualifies" id="qualifies" value="{$qualifies|default:'3'}" maxlength=2 size="2" />
							</td>
							<td>
								<label for="elimines">{#Elimines#}</label>
								<input type="tel" name="elimines" id="elimines" value="{$elimines|default:'0'}" maxlength=2 size="2" />
							</td>
						</tr>
						<tr>
							<td colspan=4 title='{#Points_pour_chaque_match#}'>
								<label for="points">Points : </label>
								<input type="radio" id="points" name="points" value='4-2-1-0'
									{if $points == '4-2-1-0'}checked{/if}><label>4-2-1-0</label>
								<input type="radio" id="points" name="points" value='3-1-0-0'
									{if $points == '3-1-0-0'}checked{/if}><label>3-1-0-0</label>
							</td>
						</tr>
						<tr>
							<td colspan=4 title='{#Goal_average#}'>
								<label for="goalaverage">{#Goal_average#} : </label>
								<input type="radio" id="goalaverage" name="goalaverage" value='gen'
									{if $goalaverage == 'gen'}checked{/if}><label>{#General#}</label>
								<input type="radio" id="goalaverage" name="goalaverage" value='part'
									{if $goalaverage == 'part'}checked{/if}><label>{#Particulier#}</label>
							</td>
						</tr>
						<tr>
							<td colspan=4>
								<hr />
								<label for="web">Web</label>
								<input type="text" name="web" id="web" maxlength=80 value="{$web}" />
							</td>
						</tr>
						{if $editCompet == ''}
							<tr>
								<td colspan=4>
									<label for="bandeauLink">{#Lien_image_bandeau#} (2480x250px) :</label>
									<input type="text" id="bandeauLink" name="bandeauLink">
									<br>
									<img hspace="2" width="200" src="" border="0" id='bandeauprovisoire'>
									<br>
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<label for="logoLink">{#Lien_image_logo#} :</label>
									<input type="text" id="logoLink" name="logoLink">
									<br>
									<img hspace="2" width="200" src="" border="0" id='logoprovisoire'>
									<br>
								</td>
							</tr>
							{if $profile <= 2 && $AuthModif == 'O'}
								<tr>
									<td colspan=4>
										<label for="sponsorLink">{#Lien_image_sponsor#} (2480x250px) :</label>
										<input type="text" id="sponsorLink" name="sponsorLink">
										<br>
										<img hspace="2" width="200" src="" border="0" id='sponsorprovisoire'>
										<br>
									</td>
								</tr>
								<!--									<tr>
										<td colspan=4>
											<label for="toutGroup">Attribuer à :</label>
											<br>
											<input type="checkbox" name="toutGroup" id="toutGroup" value='O' {if $toutGroup == 'O'}checked{/if}><label>tout le groupe</label>
											<input type="checkbox" name="touteSaisons" id="touteSaisons" value='O' {if $touteSaisons == 'O'}checked{/if}><label>toutes les saisons</label>
										</td>
									</tr>
-->
								<tr>
									<td colspan=4>
																			<label>{#Activer#} :</label>
																			<br>
																			<input type="checkbox" name="checktitre" id="checktitre" value="O"
																				{if $checktitre|default:'' != ''}checked{/if}><label>Label ({#sinon#} : Label 2)</label>
																			<br>
																			                                                                                    <input type="checkbox" name="checken" id="checken" value="O"
																			                                                                                            {if $checken|default:'' != ''}checked{/if}><label>{#Competition_en_anglais#}</label>
																			                                                                                    <br>
																			                                                                                    <input type="checkbox" name="checkkpiffck" id="checkkpiffck" value="O"
																			                                                                                            {if $checkkpiffck|default:'' != ''}checked{/if}><label>Logo KPI/FFCK</label>																			<br>
																			<input type="checkbox" name="checkbandeau" id="checkbandeau" value="O"
																				{if $checkbandeau|default:'' != ''}checked{/if}><label>{#Bandeau#}</label>
																			<br>
																			<input type="checkbox" name="checklogo" id="checklogo" value="O"
																				{if $checklogo != ''}checked{/if}><label>Logo</label>
																			<br>
																			<input type="checkbox" name="checksponsor" id="checksponsor" value="O"
																				{if $checksponsor != ''}checked{/if}><label>Sponsor</label>									</td>
								</tr>
								<tr>
									<td>
										<label for="statut">{#Statut#} :</label>
									</td>
									<td colspan="3">
										<select name="statut" id="statut">
											<option value="ATT" {if $statut == 'ATT'}selected{/if}>{#En_attente#} (ATT)</option>
											<option value="ON" {if $statut == 'ON'}selected{/if}>{#En_cours#} (ON)</option>
											<option value="END" {if $statut == 'END'}selected{/if}>{#Termine#} (END)</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<label>{#Publier#}</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O'
											{if $publierCompet == 'O'}checked{/if}>
									</td>
								</tr>
							{/if}
							<tr class='ajoutCalendrier'>
								<td colspan=4>
									<hr>
									<label><b>{#Insertion_dans_calendrier#}</b>
										<br>({#Optionnel#})</label>
								</td>
							</tr>
							<tr class='ajoutCalendrier'>
								<td colspan=4>
									<label for="TitreJournee">{#Nom#}</label>
									<input type="text" name="TitreJournee" id="TitreJournee" value="">
								</td>
							</tr>
							<tr class='ajoutCalendrier'>
								<td colspan=2>
									<label for="Date_debut">{#Date_debut#}</label>
									<input type="text" class='date' name="Date_debut" id="Date_debut" value="{$Date_debut|default:''}"
										onfocus="displayCalendar(document.forms[0].Date_debut,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
								</td>
								<td colspan=2>
									<label for="Date_fin">{#Date_fin#}</label>
									<input type="text" class='date' name="Date_fin" id="Date_fin" value="{$Date_fin|default:''}"
										onfocus="displayCalendar(document.forms[0].Date_fin,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
								</td>
							</tr>
							<tr class='ajoutCalendrier'>
								<td colspan=3>
									<label for="Lieu">{#Lieu#}</label>
									<input type="text" name="Lieu" id="Lieu" value="{$Lieu|default:''}" />
								</td>
								<td>
									<label for="Departement">{#Dpt_Pays#}</label>
									<input type="text" class='dpt' name="Departement" id="Departement" value="{$Departement|default:''}" maxlength="3"/>
								</td>
							</tr>
							<tr class='ajoutCalendrier'>
								<td colspan=4>
									<label>{#Publier#}</label><input type="checkbox" name="publierJournee" id="publierJournee" value='O'>
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<br>
									<input type="button" onclick="Add();" name="addCompet" value="<< {#Ajouter#}">
								</td>
							</tr>
						{else}
							<tr>
								<td colspan=4 align=center>
									<label for="bandeauLink"><b>{#Lien_image_bandeau#} :</b></label>
									<input type="text" id="bandeauLink" name="bandeauLink" value="{$bandeauLink}">
									<img hspace="2" id='bandeauprovisoire' width="200" src="" alt="Bandeau actuel de la compétition"
										title="Bandeau actuel de la compétition" border="0">
									<br>
									<label for="logoLink"><b>{#Lien_image_logo#} :</b></label>
									<input type="text" id="logoLink" name="logoLink" value="{$logoLink}">
									<img hspace="2" id='logoprovisoire' width="200" src="" alt="Logo actuel de la compétition"
										title="Logo actuel de la compétition" border="0">
									<br>
									<label for="sponsorLink"><b>{#Lien_image_sponsor#} :</b></label>
									<input type="text" id="sponsorLink" name="sponsorLink" value="{$sponsorLink}">
									<img hspace="2" id='sponsorprovisoire' width="200" src="" alt="Sponsor actuel de la compétition"
										title="Sponsor actuel de la compétition" border="0">
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<label>{#Activer#} :</label>
									<br>
									<input type="checkbox" name="checktitre" id="checktitre" value="O"
										{if $checktitre != ''}checked{/if}><label>Label ({#sinon#} : Label 2)</label>
									<br>
									<input type="checkbox" name="checken" id="checken" value="O"
										{if $checken != ''}checked{/if}><label>{#Competition_en_anglais#}</label>
									<br>
									                                                                                    <input type="checkbox" name="checkkpiffck" id="checkkpiffck" value="O"
																												                                                                                            {if $checkkpiffck|default:'' != ''}checked{/if}><label>Logo KPI/FFCK</label>									<br>
									<input type="checkbox" name="checkbandeau" id="checkbandeau" value="O"
										{if $checkbandeau|default:'' != ''}checked{/if}><label>{#Bandeau#}</label>
									<br>
									<input type="checkbox" name="checklogo" id="checklogo" value="O"
										{if $checklogo != ''}checked{/if}><label>Logo</label>
									<br>
									<input type="checkbox" name="checksponsor" id="checksponsor" value="O"
										{if $checksponsor != ''}checked{/if}><label>Sponsor</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label for="statut">{#Statut#} :</label>
								</td>
								<td colspan="2">
									<select name="statut" id="statut">
										<option value="ATT" {if $statut == 'ATT'}selected{/if}>{#En_attente#} (ATT)</option>
										<option value="ON" {if $statut == 'ON'}selected{/if}>{#En_cours#} (ON)</option>
										<option value="END" {if $statut == 'END'}selected{/if}>{#Termine#} (END)</option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="4">
									<label>{#Publier#}</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O'
										{if $publierCompet == 'O'}checked{/if}>
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<label for="commentairesCompet">{#Commentaires#} ({#Prive#}) :</label>
									<br>
									<textarea name="commentairesCompet" rows=5 cols=27 id="commentairesCompet"
										wrap="soft">{$commentairesCompet}</textarea>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<br>
									<input type="button" onclick="updateCompet()" id="updateCompetition" name="updateCompetition"
										value="<< {#Modifier#}">
								</td>
								<td colspan=2>
									<br>
									<input type="button" onclick="razCompet()" id="razCompetition" name="razCompetition" value="{#Annuler#}">
								</td>
							</tr>
						{/if}
					</table>
				{else}
					<table width="100%">
						<tr>
							<td align=center>
								<img hspace="2" width="200" src="{$logo}" alt="" border="0">
							</td>
						</tr>
					</table>
				{/if}
				{if $profile <= 3}
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>{#Copie_de_structure#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionCopieCompetition.php">{#Transfert_de_structure#}</a>
							</td>
						</tr>
					</table>
				{/if}
				{if $profile <= 2}
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>{#Gestion_des_RC#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionRc.php">{#Gestion_des_RC#}</a>
							</td>
						</tr>
					</table>
				{/if}
			</div>
			</form>
			<script>
			// Fonction pour afficher/masquer les champs MULTI selon le type de compétition
			function changeCodeTypeClt() {
				var typeCompet = document.getElementById('codeTypeClt');
				var pointsGridRow = document.getElementById('pointsGridRow');
				var multiCompetitionsRow = document.getElementById('multiCompetitionsRow');

				if (typeCompet && pointsGridRow && multiCompetitionsRow) {
					if (typeCompet.value === 'MULTI') {
						pointsGridRow.style.display = 'table-row';
						multiCompetitionsRow.style.display = 'table-row';
					} else {
						pointsGridRow.style.display = 'none';
						multiCompetitionsRow.style.display = 'none';
					}
				}
			}

			// Fonction pour convertir le select multiple en JSON avant la soumission
			function updateCompet() {
				var select = document.getElementById('multiCompetitionsSelect');
				var hidden = document.getElementById('multiCompetitionsHidden');

				if (select && hidden) {
					var selected = [];
					for (var i = 0; i < select.options.length; i++) {
						if (select.options[i].selected) {
							selected.push(select.options[i].value);
						}
					}
					// Stocker la liste en JSON
					hidden.value = JSON.stringify(selected);
					// Renommer le champ hidden pour qu'il soit envoyé comme multiCompetitions
					hidden.name = 'multiCompetitions';
				}

				// Appeler la fonction originale updateCompet si elle existe
				if (typeof window.originalUpdateCompet === 'function') {
					window.originalUpdateCompet();
				} else {
					document.getElementById('Cmd').value = 'UpdateCompet';
					document.forms['formCompet'].submit();
				}
			}

			// Appeler au chargement de la page pour initialiser l'état
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', changeCodeTypeClt);
			} else {
				changeCodeTypeClt();
			}
			</script>
	</div>