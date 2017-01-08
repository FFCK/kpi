		<div class="main">
			<form method="POST" action="GestionCompetition.php" name="formCompet" id="formCompet" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' id="Cmd" Value=''/>
						<input type='hidden' name='ParamCmd' id="ParamCmd" Value=''/>
						<input type='hidden' name='verrouCompet' id="verrouCompet" Value=''/>
						<input type='hidden' name='Verrou' id="Verrou" Value=''/>
						<input type='hidden' name='Pub' id="Pub" Value=''/>

			{if $profile != 9}
				<div class='blocLeft'>
					<h3 class='titrePage'>Compétitions / poules</h3>
					<br>
					<div class='liens'>		
						<label for="saisonTravail">Saison :</label>
						<select name="saisonTravail"  id="saisonTravail" onChange="sessionSaison()">
							{section name=i loop=$arraySaison} 
								<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} (Travail){/if}</Option>
							{/section}
						</select>
						<label for="AfficheCompet">Afficher :</label>
						<select name="AfficheNiveau" onChange="changeAffiche()">
							<Option Value="" selected>Tous les niveaux</Option>
							<Option Value="INT"{if $AfficheNiveau == 'INT'} selected{/if}>Compétitions Internationales</Option>
							<Option Value="NAT"{if $AfficheNiveau == 'NAT'} selected{/if}>Compétitions Nationales</Option>
							<Option Value="REG"{if $AfficheNiveau == 'REG'} selected{/if}>Compétitions Régionales</Option>
						</select>
						<select name="AfficheCompet" onChange="changeAffiche()">
							<Option Value="" selected>Toutes les compétitions</Option>
							<Option Value="N"{if $AfficheCompet == 'N'} selected{/if}>Championnat de France</Option>
							<Option Value="CF"{if $AfficheCompet == 'CF'} selected{/if}>Coupe de France</Option>
							<Option Value="NCF"{if $AfficheCompet == 'NCF'} selected{/if}>Championnat & Coupe de France</Option>
							<Option Value="REG"{if $AfficheCompet == 'REG'} selected{/if}>Championnats régionaux</Option>
							<Option Value="DEP"{if $AfficheCompet == 'DEP'} selected{/if}>Championnats départementaux</Option>
							<Option Value="T-"{if $AfficheCompet == 'T-'} selected{/if}>Tournois Internationaux</Option>
							{if $profile <= 4}
								<Option Value="M"{if $AfficheCompet == 'M'} selected{/if}>Modèles</Option>
							{/if}
						</select>
					</div>
					<div class='blocTable' id='blocCompet'>
						<table class='tableau' id='tableCompet'>
							<thead> 
								<tr>
									<th width=18><img hspace="2" width="19" height="16" src="../img/oeil2.gif" alt="Publication de la compétition" title="Publication de la compétition" border="0"></th>
									<th width=63 title="Code">Code</th>
									<th title="Modifier">&nbsp;</th>
									<th title="Niveau">Niv.</th>
									<th title="Titre de la compétition">Libelle</th>
									<th width=63 title="Compétition de référence">Groupe</th>
									<th title="Tour/Phase">Tour</th>
									<th title="Type de compétition (Championnat ou Coupe/Tournoi)">Type</th>
									<th title="Statut">Statut</th>
									<th title="Nombre d'équipes affectées">Equipes</th>
									<th><img width="19" height="16" src="../img/verrou2.gif" alt="Verrouiller les feuilles de présence" title="Verrouiller les feuilles de présence" border="0"></th>
									<!--
									<th><img width="16" height="16" src="../img/up.gif" alt="Nb d'équipes qualifiées" title="Nb d'équipes qualifiées" border="0"></th>
									<th><img width="16" height="16" src="../img/down.gif" alt="Nb d'équipes éliminées" title="Nb d'équipes éliminées" border="0"></th>
									-->
									<th title="Nombre de matchs">Matchs</th>
									<th title="Suppression">&nbsp;</th>
								</tr>
							</thead> 
							
							<tbody>
								{section name=i loop=$arrayCompet} 
									<tr class='{cycle values="impair,pair"} {$arrayCompet[i].StdOrSelected}'>
										{*
										<td><input type="checkbox" name="checkCompet" value="{$arrayCompet[i].Code}" id="checkDelete{$smarty.section.i.iteration}" /></td>
										*}
										
										<td class='color{$arrayCompet[i].Publication}2'>
											{if $profile <= 4 && $AuthModif == 'O' && $arrayCompet[i].Code_ref != 'M'}
												<img class="publiCompet" data-valeur="{$arrayCompet[i].Publication}" data-id="{$arrayCompet[i].Code}" width="24" src="../img/oeil2{$arrayCompet[i].Publication|default:'N'}.gif" alt="Publier O/N" title="{if $arrayCompet[i].Publication == 'O'}Public{else}Non public{/if}" />
											{elseif $arrayCompet[i].Code_ref != 'M'}
												<img width="24" src="../img/oeil2{$arrayCompet[i].Publication}.gif" alt="Publier O/N" title="Publier O/N" />
											{else}-{/if}
										</td>
										<td>{$arrayCompet[i].Code}</td>
										{if $profile <= 3 && $AuthModif == 'O'}
											<td><a href="#" Id="Param{$arrayCompet[i].Code}" onclick="paramCompet('{$arrayCompet[i].Code}')"><img hspace="2" width="18" height="18" src="../img/glyphicons-31-pencil.png" alt="Modifier" title="Modifier" border="0"></a></td>
										{else}<td>&nbsp;</td>{/if}
										<td>{$arrayCompet[i].Code_niveau|default:'&nbsp;'}</td>
										<td	class="cliquableNomEquipe"
											title="<center>
											{if $arrayCompet[i].Titre_actif == 'O'}{$arrayCompet[i].Libelle}<br>{else}{$arrayCompet[i].Soustitre}<br>{/if}
											{$arrayCompet[i].Soustitre2}
											<br>- - -
											<br>- - -
											<br>Qualifiés : {$arrayCompet[i].Qualifies}
											<br>Eliminés : {$arrayCompet[i].Elimines}
											<br>- - -
											<br><i>{$arrayCompet[i].commentairesCompet}</i><br><br>
											</center>"
										><a href='GestionDoc.php?Compet={$arrayCompet[i].Code}'>{if $arrayCompet[i].Titre_actif != 'O' && $arrayCompet[i].Soustitre != ''}{$arrayCompet[i].Soustitre}{else}{$arrayCompet[i].Libelle}{/if}{if $arrayCompet[i].Soustitre2 != ''}<br />{$arrayCompet[i].Soustitre2}{/if}</a></td>
										<td>{$arrayCompet[i].Code_ref|default:'&nbsp;'}</td>
										<td>{if $arrayCompet[i].Code_tour == '10'}F{else}{$arrayCompet[i].Code_tour|default:'&nbsp;'}{/if}</td>
										<td>{$arrayCompet[i].codeTypeClt|default:'&nbsp;'}</td>
										<td title="Changer le statut">
											{if $profile <= 3 && $AuthModif == 'O'}
												<span class="statutCompet statutCompet{$arrayCompet[i].Statut}" data-id="{$arrayCompet[i].Code}" >{$arrayCompet[i].Statut}</span>
											{else}
												<span class="statutCompet{$arrayCompet[i].Statut}">{$arrayCompet[i].Statut}</span>
											{/if}
										</td>
										<td>{$arrayCompet[i].Nb_equipes|default:'&nbsp;'}</td>
										<td alt="Verrouiller les feuilles de présence" title="Verrouiller les feuilles de présence">
											{if $profile <= 3 && $AuthModif == 'O'}
											<!--
												<a href="#" Id="Verrou{$arrayCompet[i].Code}" onclick="verrou('{$arrayCompet[i].Code}', '{$arrayCompet[i].Verrou}')">
													<img width="24" height="24" src="../img/verrou2{$arrayCompet[i].Verrou|default:'N'}.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" border="0">
												</a>
											-->
												<img class="verrouCompet" data-valeur="{$arrayCompet[i].Verrou}" data-id="{$arrayCompet[i].Code}" width="24" src="../img/verrou2{$arrayCompet[i].Verrou|default:'N'}.gif" alt="Verrouiller O/N" title="{if $arrayCompet[i].Verrou == 'O'}Feuilles de présence verrouillées{else}Feuilles de présence modifiables{/if}" />
											{else}
												<img width="24" src="../img/verrou2{$arrayCompet[i].Verrou|default:'N'}.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" />
											{/if}
										</td>
										<!--
										<td>{$arrayCompet[i].Qualifies|default:'&nbsp;'}</td>
										<td>{$arrayCompet[i].Elimines|default:'&nbsp;'}</td>
										-->
										<td>{$arrayCompet[i].nbMatchs|default:'&nbsp;'}</td>
										{if $profile <= 2 && $AuthModif == 'O'}
											<td><a href="#" onclick="RemoveCheckbox('formCompet', '{$arrayCompet[i].Code}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" alt="Supprimer" title="Supprimer" border="0"></a></td>
										{else}<td>&nbsp;</td>{/if}
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
									<label class='maxWith'>Accès direct feuille de marque</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="accesFeuille" class='maxWith'>Identifiant feuille de marque : </label>
									<img border="0" title="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) " 
									alt="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque)" 
									src="../img/b_help.png" 
									onclick="alert('Saisissez l\'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) ')">
								</td>
							</tr>
							<tr>
								<td width="60%">
									<input class='maxWith newInput' type="tel" name="idMatch" maxlength=15 id="idMatch" />
								</td>
								<td>
									<input class='maxWith newBtn' type="submit" value="Accès" />
								</td>
							</tr>
						</table>
					{elseif $profile <= 6}
						<table width="100%" class='vert'>
							<tr>
								<th class='titreForm' colspan=2>
									<label class='maxWith'>Accès direct feuille de marque</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="accesFeuille" class='maxWith'>Identifiant feuille de marque : </label>
									<img border="0" title="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) " 
									alt="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque)" 
									src="../img/b_help.png" 
									onclick="alert('Saisissez l\'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) ')">
								</td>
							</tr>
							<tr>
								<td width="60%">
									<input class='maxWith newInput' type="tel" name="accesFeuille" maxlength=15 id="accesFeuille" />
								</td>
								<td>
									<input class='maxWith newBtn' type="button" name="accesFeuilleBtn" id="accesFeuilleBtn" value="Accès" />
								</td>
							</tr>
						</table>
					{/if}
					{if $profile <= 3 && $AuthModif == 'O'}
						<table width="100%">
							<tr>
								<th class='titreForm' colspan=4>
									<label class='maxWith'>{if $editCompet == ''}Ajouter une {else}Modifier la {/if}compétition</label>
								</th>
							</tr>
							{if $editCompet == ''}
							<tr>
								<td colspan=4>
									<label for="choixCompet" class='maxWith'>Sélectionner la compétition : </label>
									<img border="0" title="Saisissez les premier caractères d'un code compétition, sélectionnez un code existant et ajustez tous les paramètres. Si la compétition a créer n'existe pas, demander au webmaster." 
									alt="Saisissez les premier caractères d'un code compétition, sélectionnez un code existant et ajustez tous les paramètres. Si la compétition a créer n'existe pas, demander au webmaster." 
									src="../img/b_help.png" 
									onclick="alert('Saisissez les premier caractères d\'un code compétition, \nsélectionnez un code existant \net ajustez tous les paramètres.\n\nSi la compétition a créer n\'existe pas, demandez au webmaster.')">
									<input class='maxWith' type="text" name="choixCompet" maxlength=50 id="choixCompet" />
								</td>
							</tr>
							<tr>
								<td width=55% colspan=2>
									<label for="codeCompet">Code :</label>
									<input type="text" name="codeCompet" maxlength=12 id="codeCompet" {if $user == '42054' or $user == '63155'}class='gris'{else}readonly{/if} {if $editCompet != ''}value="{$codeCompet}"{/if} />
								</td>
								<td colspan=2>
									<label for="niveauCompet">Niveau : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG"{if $niveauCompet == 'REG'} selected{/if}>REG-Régional</Option>
										<Option Value="NAT"{if $niveauCompet == 'NAT' or $niveauCompet == ''} selected{/if}>NAT-National</Option>
										<Option Value="INT"{if $niveauCompet == 'INT'} selected{/if}>INT-International</Option>
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
									<label for="niveauCompet">Niveau : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG"{if $niveauCompet == 'REG'} selected{/if}>REG-Régional</Option>
										<Option Value="NAT"{if $niveauCompet == 'NAT' or $niveauCompet == ''} selected{/if}>NAT-National</Option>
										<Option Value="INT"{if $niveauCompet == 'INT'} selected{/if}>INT-International</Option>
									</select>
								</td>
							</tr>
							{/if}
							<tr>
								<td colspan=4>
									<label for="labelCompet">Libellé : </label>
									<input type="text" name="labelCompet" value="{$labelCompet}" maxlength=50 id="labelCompet" {if $user == '42054' or $user == '63155'}class='gris'{else}readonly{/if} />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Exemple : <br>ICF World Championships - Milan (ITA)<br>'>
									<hr>
									<label for="soustitre">Libellé 2<br>
									<i>Titre public - Ville (CODE PAYS CIO)</i></label>
									<input type="text" name="soustitre" id="soustitre" maxlength=80 value="{$soustitre}" />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Exemples : <br>Women U21, Men, Tournoi 1, 2nd Division<br>'>
									<label for="soustitre2">Catégorie (compétitions internationales)<br>
									<i>Men, Women U21, Tournoi 1...</i></label>
									<input type="text" name="soustitre2" id="soustitre2" maxlength=80 value="{$soustitre2}" />
								</td>
							</tr>
							<tr>
								<td colspan=3>
									<label for="codeRef">Groupe :</label>
									<select name="codeRef" id="codeRef">
										<Option Value="">= OBLIGATOIRE =</Option>
										{section name=i loop=$arrayGroupCompet} 
											<Option Value="{$arrayGroupCompet[i].Groupe}"{if $arrayGroupCompet[i].Groupe == $codeRef} selected{/if}>{$arrayGroupCompet[i].Groupe} - {$arrayGroupCompet[i].Libelle}</Option>
										{/section}
									</select>
								</td>
								<td>
									<label for="groupOrder">Ordre :</label>
									<input type="text" name="groupOrder" value="{$groupOrder}" maxlength=1 id="groupOrder" />
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<label for="codeTypeClt">Type : </label>
									<select name="codeTypeClt" id="codeTypeClt" onChange="changeCodeTypeClt();">
										{section name=i loop=$arrayTypeClt} 
										<Option Value="{$arrayTypeClt[i][0]}"{if $arrayTypeClt[i][0] == $codeTypeClt} selected{/if}>{$arrayTypeClt[i][1]}</Option>
										{/section}
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2 width=55%>
									<label for="etape">Tour/Phase :</label>
									<select name="etape" id="etape">
										{section name=i loop=6 start=1} 
											<Option Value="{$smarty.section.i.index}"{if $smarty.section.i.index == $etape} selected{/if}>{$smarty.section.i.index}</Option>
										{/section}
											<Option Value="10"{if $etape == 10 or $etape == ''} selected{/if}>Unique/Finale</Option>
									</select>
								</td>
								<td>
									<label for="qualifies">Qualifiés</label>
									<input type="text" name="qualifies" id="qualifies" value="{$qualifies|default:'3'}" />
								</td>
								<td>
									<label for="elimines">Eliminés</label>
									<input type="text" name="elimines" id="elimines" value="{$elimines|default:'0'}" />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Points pour chaque match Gagné, Null, Perdu, Forfait'>
									<label for="points">Points G-N-P-F : </label>
									<input type="radio" name="points" value='4-2-1-0' {if $points == '4-2-1-0'}checked{/if}><label>4-2-1-0</label>
									<input type="radio" name="points" value='3-1-0-0' {if $points == '3-1-0-0'}checked{/if}><label>3-1-0-0</label>
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<hr />
									<label for="web">Site Web</label>
									<input type="text" name="web" id="web" maxlength=80 value="{$web}" />
								</td>
							</tr>
							{if $editCompet == ''}
								<tr>
									<td colspan=4>
										<label for="logoLink">Lien image logo compet :</label>
										<input type="text" id="logoLink" name="logoLink">
										<br>
										<img hspace="2" width="200" src="" border="0" id='logoprovisoire'>
										<br>
									</td>
								</tr>
								{if $profile <= 2 && $AuthModif == 'O'}
									<tr>
										<td colspan=4>
											<label for="sponsorLink">Lien image sponsor compet :</label>
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
-->									<tr>
										<td colspan=4>
											<label for="logo_actif">Activer :</label>
											<br>
											<input type="checkbox" name="kpi_ffck_actif" id="kpi_ffck_actif" value='O' {if $kpi_ffck_actif != ''}checked{/if}><label>Logo KPI/FFCK</label>
											<br>
											<input type="checkbox" name="titre_actif" id="titre_actif" value='O' {if $titre_actif != ''}checked{/if}><label>Libellé (sinon : libellé 2)</label>
											<br>
											<input type="checkbox" name="logo_actif" id="logo_actif" value='O' {if $logo_actif == 'O'}checked{/if}><label>Logo compétition</label>
											<br>
											<input type="checkbox" name="sponsor_actif" id="sponsor_actif" value='O' {if $sponsor_actif == 'O'}checked{/if}><label>Image Sponsor</label>
											<br>
											<input type="checkbox" name="en_actif" id="en_actif" value='O' {if $en_actif != ''}checked{/if}><label>Compétition en Anglais</label>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<label for="statut">Statut :</label>
										</td>
										<td colspan="2">
											<select name="statut" id="statut">
												<option value="ATT" {if $statut == 'ATT'}selected{/if}>Attente (ATT)</option>
												<option value="ON" {if $statut == 'ON'}selected{/if}>En cours (ON)</option>
												<option value="END" {if $statut == 'END'}selected{/if}>Terminée (END)</option>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="4">
											<label>Publier la compétition</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O' {if $publierCompet == 'O'}checked{/if}>
										</td>
									</tr>
								{/if}
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<hr>
										<label><b>Insertion dans le calendrier public</b>
										<img border="0" title="Si vous remplissez cette section, une journée sera insérée dans le calendrier mais non publiée." 
										alt="Si vous remplissez cette section, une journée sera insérée dans le calendrier mais non publiée." 
										src="../img/b_help.png" 
										onclick="alert('Si vous remplissez cette section, une journée sera insérée dans le calendrier\n mais non publiée.')">
										<br>(journée unique, optionnel)</label>
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<label for="TitreJournee">Nom de la journée</label>
										<input type="text" name="TitreJournee" id="TitreJournee" value="">
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=2>
										<label for="Date_debut">Date_debut</label>
										<input type="text" class='date' name="Date_debut" id="Date_debut" value="{$Date_debut}" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" >
									</td>
									<td colspan=2>
										<label for="Date_fin">Date_fin</label>
										<input type="text" class='date' name="Date_fin" id="Date_fin" value="{$Date_fin}" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" >
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=3>
										<label for="Lieu">Lieu</label>
										<input type="text" name="Lieu" id="Lieu" value="{$Lieu}"/>
									</td>
									<td>
										<label for="Departement">Dpt/Pays</label>
										<input type="text" class='dpt' name="Departement" id="Departement" value="{$Departement}"/>
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<label>Publier la journee</label><input type="checkbox" name="publierJournee" id="publierJournee" value='O'>
									</td>
								</tr>
								<tr>
									<td colspan=4>
										<br>
										<input type="button" onclick="Add();" name="addCompet" value="<< Ajouter">
									</td>
								</tr>
							{else}
								<tr>
									<td colspan=4 align=center>
										<label for="logoLink"><b>Lien image logo :</b></label>
										<input type="text" id="logoLink" name="logoLink" value="{$logoLink}">
											<img hspace="2" id='logoprovisoire' width="200" src="{$logo}" alt="Logo actuel de la compétition" title="Logo actuel de la compétition" border="0">
										<br>
										<label for="sponsorLink"><b>Lien image sponsor :</b></label>
										<input type="text" id="sponsorLink" name="sponsorLink" value="{$sponsorLink}">
											<img hspace="2" id='sponsorprovisoire' width="200" src="{$sponsor}" alt="Sponsor actuel de la compétition" title="Sponsor actuel de la compétition" border="0">
									</td>
								</tr>
								{*
									<label for="codeTour">Tour :</label>
									<input type="text" name="codeTour" maxlength=8 id="codeTour"/>
									<label for="sexe">Sexe :</label>
									<input type="text" name="sexe" maxlength=1 id="sexe"/> 
								<tr>
									<td colspan=4>
										<label for="toutGroup">Attribuer à :</label>
										<br>
										<input type="checkbox" name="toutGroup" id="toutGroup" value='O' {if $toutGroup == 'O'}checked{/if}><label>tout le groupe</label>
										<input type="checkbox" name="touteSaisons" id="touteSaisons" value='O' {if $touteSaisons == 'O'}checked{/if}><label>toutes les saisons</label>
										<hr>
									</td>
								</tr>
								*}
								<tr>
									<td colspan=4>
										<label for="logo_actif">Activer :</label>
										<br>
										<input type="checkbox" name="kpi_ffck_actif" id="kpi_ffck_actif" value='O' {if $kpi_ffck_actif != ''}checked{/if}><label>Logo KPI/FFCK</label>
										<br>
										<input type="checkbox" name="titre_actif" id="titre_actif" value='O' {if $titre_actif != ''}checked{/if}><label>Libellé (sinon : libellé 2)</label>
										<br>
										<input type="checkbox" name="logo_actif" id="logo_actif" value='O' {if $logo_actif == 'O'}checked{/if}><label>Logo compétition</label>
										<br>
										<input type="checkbox" name="sponsor_actif" id="sponsor_actif" value='O' {if $sponsor_actif == 'O'}checked{/if}><label>Image Sponsor</label>
										<br>
										<input type="checkbox" name="en_actif" id="en_actif" value='O' {if $en_actif != ''}checked{/if}><label>Compétition en Anglais</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="statut">Statut :</label>
									</td>
									<td colspan="2">
										<select name="statut" id="statut">
											<option value="ATT" {if $statut == 'ATT'}selected{/if}>Attente (ATT)</option>
											<option value="ON" {if $statut == 'ON'}selected{/if}>En cours (ON)</option>
											<option value="END" {if $statut == 'END'}selected{/if}>Terminée (END)</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<label>Publier la compétition</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O' {if $publierCompet == 'O'}checked{/if}>
									</td>
								</tr>
								<tr>
									<td colspan=4>
										<label for="commentairesCompet">Commentaires (non public) :</label>
										<br>
										<textarea name="commentairesCompet" rows=5 cols=27 id="commentairesCompet" wrap="soft">{$commentairesCompet}</textarea>
									</td>
								</tr>
								<tr>
									<td colspan=2>
										<br>
										<input type="button" onclick="updateCompet()" id="updateCompetition" name="updateCompetition" value="<< Modifier">
									</td>
									<td colspan=2>
										<br>
										<input type="button" onclick="razCompet()" id="razCompetition" name="razCompetition" value="Annuler">
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
					{if $profile <= 4}
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Copie de structure de matchs</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionCopieCompetition.php">Copier la struture des matchs d'une compétition à l'autre...</a>
							</td>
						</tr>
					</table>
					{/if}
					{if $profile <= 2 && $AuthModif == 'O'}
					<br>
					<br>
					<hr>
					<div align='center' class='rouge'><i>Profils 1 - 2</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer de saison (publique)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="saisonActive"><b>Saison active :</b></label>
								<select name="saisonActive" onChange="activeSaison()">
									{section name=i loop=$arraySaison} 
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Etat=='A'}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Etat=='A'} (Active){/if}</Option>
									{/section}
								</select>
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Ajouter une saison</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Saison :</label>
								<input type="text" name="newSaison">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Debut National</label>
								<input type="text" class='date' name="newSaisonDN" onfocus="displayCalendar(document.forms[0].newSaisonDN,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFN">Fin National</label>
								<input type="text" class='date' name="newSaisonFN" onfocus="displayCalendar(document.forms[0].newSaisonFN,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td>
								<label for="newSaisonDI">Debut International</label>
								<input type="text" class='date' name="newSaisonDI" onfocus="displayCalendar(document.forms[0].newSaisonDI,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFI">Fin International</label>
								<input type="text" class='date' name="newSaisonFI" onfocus="displayCalendar(document.forms[0].newSaisonFI,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" name="AjoutSaison" onclick="AddSaison();" value="Créer">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Fusionner des licenciés</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="FusionSource">Source (sera supprimé)</label>
								<input type="hidden" name="numFusionSource" id="numFusionSource">
								<input type="text" name="FusionSource" size=40 id="FusionSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="FusionCible">Cible (sera conservé)</label>
								<input type="hidden" name="numFusionCible" id="numFusionCible">
								<input type="text" name="FusionCible" size=40 id="FusionCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionJoueurs" id="FusionJoueurs" value="Fusionner">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Renommer une équipe</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="RenomSource">Source (ancien nom)</label>
								<input type="hidden" name="numRenomSource" id="numRenomSource">
								<input type="text" name="RenomSource" size=40 id="RenomSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="RenomCible">Cible (nouveau nom)</label>
								<input type="text" name="RenomCible" size=40 id="RenomCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="RenomEquipe" id="RenomEquipe" value="Renommer">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Fusionner deux équipes</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="FusionEquipeSource">Source (sera supprimé)</label>
								<input type="hidden" name="numFusionEquipeSource" id="numFusionEquipeSource">
								<input type="text" name="FusionEquipeSource" size=40 id="FusionEquipeSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="FusionEquipeCible">Cible (sera conservé)</label>
								<input type="hidden" name="numFusionEquipeCible" id="numFusionEquipeCible">
								<input type="text" name="FusionEquipeCible" size=40 id="FusionEquipeCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionEquipes" id="FusionEquipes" value="Fusionner">
							</td>
						</tr>
					</table>
					{/if}
					{if $profile <= 4}
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Verrou saisons précédentes</label>
							</th>
						</tr>
						<tr>
							<td colspan=2 align="center">
								<label for="AuthSaison">Verrou</label>
								{if $AuthSaison == 'O'}<b><i>INACTIF</i></b>
								{else}<b>ACTIF</b>
								{/if}
							</td>
						</tr>
						{if $profile <= 2}
						<tr>
							<td>
								<input type="button" name="ChangeAuthSaison" id="ChangeAuthSaison" onclick="changeAuthSaison()" value="Changer">
							</td>
						</tr>
						{/if}
						<tr>
							<td colspan=2 align="center">
								{if $AuthModif == 'O'}<b><i>Compétitions déverrouillées.</i></b>
								{else}<b>Par mesure de sécurité, les compétitions des saisons précédentes sont verrouillées.</b>
								{/if}
							</td>
						</tr>
					</table>
					<br>
					{/if}
					{if $user == '42054' or $user == '63155'}
					<hr>
					<div align='center' class='rouge'><i>User Laurent</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Tester un autre profil</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="profilTest">Tester un autre profil</label>
								<select name="profilTest" onChange="submit();">
									<option value="1" {if $profile == 1}Selected{/if}>1 - Webmaster / Président</option>
									<option value="2" {if $profile == 2}Selected{/if}>2 - Bureau</option>
									<option value="3" {if $profile == 3}Selected{/if}>3 - Resp. Compétition</option>
									<option value="4" {if $profile == 4}Selected{/if}>4 - Resp. Poule</option>
									<option value="5" {if $profile == 5}Selected{/if}>5 - Délégué fédéral</option>
									<option value="6" {if $profile == 6}Selected{/if}>6 - Organisateur Journée</option>
									<option value="7" {if $profile == 7}Selected{/if}>7 - Resp. Club / Equipe</option>
									<option value="8" {if $profile == 8}Selected{/if}>8 - Consultation simple</option>
									<option value="9" {if $profile == 9}Selected{/if}>9 - Table de Marque</option>
									<option value="10" {if $profile == 10}Selected{/if}>10 - Non utilisé</option>
								</select>
							</td>
						</tr>
					</table>
					{/if}
		        </div>
			</form>			
		</div>
	