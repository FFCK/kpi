		&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
	
		<div class="main">
			<form method="POST" action="GestionParamJournee.php" name="formParamJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='duppliThis' Value=''/>
				<input type='hidden' name='PrevSaison' Value='{$J_saison}'/>
				<input type='hidden' name='PrevCompetition' Value='{$J_competition}'/>
				<input type='hidden' name='PrevDate' Value='{$Date_debut}'/>
				<input type='hidden' name='idJournee' Value='{$idJournee}'/>
				<div class='blocRight Right3'>
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=4>
								{if $Num_Journee == 0}
								<label>Créer une journée/phase</label>
								{else}
								<label>Modifier la journée/phase</label>
								{/if}
							</th>
						</tr>
						{if $profile <= 2}
							<tr>
								<td><label for="J_saison">Saison</label>
									<img hspace="2" width="18" height="18" src="../img/danger.png" alt="Attention aux conséquences d'une modification" title="Attention aux conséquences d'une modification" border="0">
									<select name="J_saison" onchange="alert('Attention, aux conséquences d une modification de ce paramètre')">
										{section name=i loop=$arraySaisons}
											<Option Value="{$arraySaisons[i].Code}" {if $arraySaisons[i].Code == $J_saison}selected{/if}>{$arraySaisons[i].Code}</Option>
										{/section}
								    </select>
								</td>
								<td colspan=3><label for="J_competition">Competition</label>
									<img width="18" height="18" src="../img/danger.png" alt="Attention aux conséquences d'une modification" title="Attention aux conséquences d'une modification" border="0">
									<select name="J_competition" onchange="alert('Attention, aux conséquences d une modification de ce paramètre')">
                                        {section name=i loop=$arrayCompetition}
                                            {assign var='options' value=$arrayCompetition[i].options}
                                            {assign var='label' value=$arrayCompetition[i].label}
                                            <optgroup label="{$smarty.config.$label|default:$label}">
                                                {section name=j loop=$options}
                                                    {assign var='optionLabel' value=$options[j].Code}
                                                    <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code} - {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                                                {/section}
                                            </optgroup>
                                        {/section}
								    </select>
								</td>
							</tr>
						{else}
							<tr>
								<td><label for="J_saison">Saison : </label>{$J_saison}<input type='hidden' name='J_saison' Value='{$J_saison}'/></td>
								<td colspan=3><label for="J_competition">Competition : </label>{$J_competition}<input type='hidden' name='J_competition' Value='{$J_competition}'/></td>
							</tr>
						{/if}
						<tr>
							<td width="50%">
								<label for="Phase">Phase (compétition type Coupe)</label><input type="text" name="Phase" value="{$Phase}"/>
								<select id="PhaseList" name="PhaseList" onChange="Phase.value=this.options[this.selectedIndex].value">
									<optgroup label="Modèles FR (EN plus bas)">
										<Option Value="">(Sélectionnez...)</Option>
										<Option Value="Poule A">Poule A</Option>
										<Option Value="Poule B">Poule B</Option>
										<Option Value="Poule C">Poule C</Option>
										<Option Value="Poule D">Poule D</Option>
										<Option Value="Poule E">Poule E</Option>
										<Option Value="Poule F">Poule F</Option>
										<Option Value="Poule G">Poule G</Option>
										<Option Value="Poule H">Poule H</Option>
										<Option Value="Poule I">Poule I</Option>
										<Option Value="Poule J">Poule J</Option>
										<Option Value="Poule K">Poule K</Option>
										<Option Value="Poule L">Poule L</Option>
										<Option Value="Poule M">Poule M</Option>
										<Option Value="Poule N">Poule N</Option>
										<Option Value="Poule O">Poule O</Option>
										<Option Value="Classement">Classement</Option>
										<Option Value="1/8 finale">1/8 finale</Option>
										<Option Value="1/4 finale">1/4 finale</Option>
										<Option Value="1/2 finale">1/2 finale</Option>
										<Option Value="27ème place">27ème place</Option>
										<Option Value="25ème place">25ème place</Option>
										<Option Value="23ème place">23ème place</Option>
										<Option Value="21ème place">21ème place</Option>
										<Option Value="19ème place">19ème place</Option>
										<Option Value="17ème place">17ème place</Option>
										<Option Value="15ème place">15ème place</Option>
										<Option Value="13ème place">13ème place</Option>
										<Option Value="11ème place">11ème place</Option>
										<Option Value="9ème place">9ème place</Option>
										<Option Value="7ème place">7ème place</Option>
										<Option Value="5ème place">5ème place</Option>
										<Option Value="3ème place">3ème place</Option>
										<Option Value="Finale">Finale</Option>
									</optgroup>
									<optgroup label="pause">
										<Option Value="PAUSE">PAUSE</Option>
									</optgroup>
									<optgroup label="Models (EN)">
										<Option Value="Group A">Group A</Option>
										<Option Value="Group B">Group B</Option>
										<Option Value="Group C">Group C</Option>
										<Option Value="Group D">Group D</Option>
										<Option Value="Group E">Group E</Option>
										<Option Value="Group F">Group F</Option>
										<Option Value="Group G">Group G</Option>
										<Option Value="Group H">Group H</Option>
										<Option Value="Group I">Group I</Option>
										<Option Value="Group J">Group J</Option>
										<Option Value="Group K">Group K</Option>
										<Option Value="Group L">Group L</Option>
										<Option Value="Group M">Group M</Option>
										<Option Value="Group N">Group N</Option>
										<Option Value="Group O">Group O</Option>
										<Option Value="Classifying">Classifying</Option>
										<Option Value="1/8 final">1/8 final</Option>
										<Option Value="1/4 final">1/4 final</Option>
										<Option Value="1/2 final">1/2 final</Option>
										<Option Value="27th place">27th place</Option>
										<Option Value="25th place">25th place</Option>
										<Option Value="23rd place">23rd place</Option>
										<Option Value="21st place">21st place</Option>
										<Option Value="19th place">19th place</Option>
										<Option Value="17th place">17th place</Option>
										<Option Value="15th place">15th place</Option>
										<Option Value="13th place">13th place</Option>
										<Option Value="11th place">11th place</Option>
										<Option Value="9th place">9th place</Option>
										<Option Value="7th place">7th place</Option>
										<Option Value="5th place">5th place</Option>
										<Option Value="3rd place">3rd place</Option>
										<Option Value="Final">Final</Option>
									</optgroup>
								</select>
							</td>
							<td width="17%">
								<label for="Niveau">Niveau</label>
                                <img width="12" src="../img/b_help.png" title="Importance de la phase dans le classement géléral. Par exemple :
                                     <br>-Les premières poules sont toujours au niveau 1,
                                     <br>-La finale est toujours au niveau le plus élevé,
                                     <br>-Les demi-finales auront un niveau supérieur au match pour la 5ème place,
                                     <br>même si elles se jouent avant.">
								<select size=1 id="Niveau" name="Niveau">
								{section name=foo start=1 loop=30}
									<Option Value="{$smarty.section.foo.index}"{if $smarty.section.foo.index==$Niveau} selected{/if}>{$smarty.section.foo.index}</Option>
								{/section}
								</select>
							</td>
							<td width="17%">
								<label for="Etape">Etape</label>
                                <img width="12" src="../img/b_help.png" title="Etape de la phase dans le schéma du système de jeu. Par exemple :
                                     <br>-Les premières poules sont toujours à l'étape 1,
                                     <br>-La finale est toujours à l'étape la plus élevée,
                                     <br>-Plusieurs phases de niveau différents peuvent être à la même étape
                                     <br>(la finale peut être à la même étape que la phase pour la 3ème place).">
								<select id="Etape" name="Etape">
								{section name=foo start=1 loop=20}
									<Option Value="{$smarty.section.foo.index}"{if $smarty.section.foo.index==$Etape} selected{/if}>{$smarty.section.foo.index}</Option>
								{/section}
								</select>
							</td>
							<td width="16%">
								<label for="Nbequipes">Nb éq.</label>
                                <img width="12" src="../img/b_help.png" title="Nombre d'équipes (pour les poules de classement)">
								<select id="Nbequipes" name="Nbequipes">
								{section name=foo start=1 loop=20}
									<Option Value="{$smarty.section.foo.index}"{if $smarty.section.foo.index==$Nbequipes} selected{/if}>{$smarty.section.foo.index}</Option>
								{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=4>
								<label for="Type">Type de matchs : </label>
								<span title="Matchs de poule, classement par points, égalité possible"><img src="../img/typeC.png" style="vertical-align: middle" />Classement</span><input type="radio" name="Type" value="C" checked />
								&nbsp;&nbsp;<span title="Matchs éliminatoires, égalité impossible, prolongation si nécessaire"><img src="../img/typeE.png" style="vertical-align: middle" />Elimination</span><input type="radio" name="Type" value="E" {if $Type == 'E'}checked{/if} />
							</td>
						</tr>
						<tr>
							<td colspan=4 class="vert" align='center'><label><b>Paramètres apparents dans le calendrier public</b></label></td>
						</tr>
						<tr>
							<td class="vert">
								<label for="Date_debut"><b>Date_debut</b></label>
								<input type="text" class='date' name="Date_debut" value="{$Date_debut}" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" >
								<input type="hidden" name="Date_origine" value="{$Date_debut}" >
							</td>
							<td colspan=3 class="vert"><label for="Date_fin"><b>Date_fin</b></label><input type="text" class='date' name="Date_fin" value="{$Date_fin}" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" ></td>
						</tr>
						<tr>
							<td class="vert"><label for="Lieu"><b>Lieu</b> (commune)</label><input type="text" name="Lieu" id="Lieu" value="{$Lieu}" placeholder="Commune ou code postal" /></td>
							<td colspan=3 class="vert"><label for="Departement"><b>Département ou code pays (CIO)</b></label><input type="text" class='dpt' name="Departement" id="Departement" value="{$Departement}" placeholder="N° département ou code pays (CIO)" /></td>
						</tr>
						<tr>
							<td colspan=4 class="vert">
								<label for="Nom"><b>Nom journée (CHPT) ou Nom compétition (CP)</b></label>
								<img border="0" title="Nom qui apparaîtra dans le calendrier public." 
								alt="Nom qui apparaîtra dans le calendrier public." 
								src="../img/b_help.png" onclick="alert('Nom qui apparaîtra dans le calendrier public.')">
								<input type="text" name="Nom" id="Nom" value="{$Nom}" placeholder="Nom qui apparaîtra dans le calendrier public" />
								<br>
								<label><i><u>Exemples :</u><br />
									Championnat N1F, J4<br />
									Championnat N3H, J2-B<br />
									Coupe Senior H, 1T-N<br />
									10th Veurne International Canoepolo Tournament</i><br>
								</label>
							</td>
						</tr>
						<tr>
							<td><br><label for="Organisateur">Club Organisateur</label><input type="text" name="Organisateur" id="Organisateur" value="{$Organisateur}" placeholder="Nom ou numéro de club" /></td>
							<td colspan=3><br><label for="Plan_eau">Plan d'eau</label><input type="text" name="Plan_eau" value="{$Plan_eau}"/></td>
						</tr>
						<tr>
							<td><label for="Responsable_insc">Responsable compétition RC</label><input type="text" id="Responsable_insc" name="Responsable_insc" value="{$Responsable_insc}" placeholder="Nom prénom ou numéro de licence" /></td>
							<td colspan=3><label for="Responsable_R1">Responsable local R1</label><input type="text" id="Responsable_R1" name="Responsable_R1" value="{$Responsable_R1}" placeholder="Nom prénom ou numéro de licence" /></td>
						</tr>
						<tr>
							<td><label for="Delegue">Délégué fédéral CNA</label><input type="text" id="Delegue" name="Delegue" value="{$Delegue}" placeholder="Nom prénom ou numéro de licence" /></td>
							<td colspan=3><label for="ChefArbitre">Chef des arbitres</label><input type="text" id="ChefArbitre" name="ChefArbitre" value="{$ChefArbitre}" placeholder="Nom prénom ou numéro de licence" /></td>
						</tr>
						<tr>
							{if $Num_Journee == 0}
								<td colspan=4><input type="button" onclick="Ok();" name="Sauvegarder" value="Insérer la journée"></td>
							{else}
								<td colspan=4>
									<input type="button" onclick="Ok();" name="Sauvegarder" value="Sauvegarder les modifications">
									<hr>
								</td>
							{/if}
						</tr>
						<tr>
							<td><label for="AvecMatchs">Inclure les matchs</label><input type="checkbox" name="AvecMatchs" value="oui"></td>
							<td colspan=3><label for="CodMatchs">Encoder les matchs de poule *</label><input type="checkbox" name="CodMatchs" value="oui"></td>
						</tr>
						<tr>
							<td colspan=4 align="right"><label>(* Uniquement s'ils ne sont pas déjà encodés)</label></td>
						</tr>
						<tr>
							<td colspan=4>
								<input type="button" onclick="Duppli();" name="Dupliquer" value="Sauvegarder comme nouvelle journée (duppliquer)">
								<hr>
							</td>
						</tr>
					</table>
					{if $Code_typeclt == 'CP'}
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=2 width="100%">
								<label>Appliquer ces paramètres sur les autres phases de la compétition</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<b>Autres phases de la compétition {$J_competition} :</b>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formParamJournee', 'checkListJournees', true);return false;"><img hspace="2" width="21" height="19" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" border="0"></a>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formParamJournee', 'checkListJournees', false);return false;"><img hspace="2" width="21" height="19" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" border="0"></a>
								<br>
								<i>(sauf Phase et Niveau)</i>
								<br>
							</td>
						</tr>
						{section name=i loop=$ListJournees}
							<tr>
								<td>
									<input type="checkbox" name="checkListJournees" value="{$ListJournees[i].Id}"/>
								</td>
								<td>
									N°{$ListJournees[i].Id} ({$ListJournees[i].Date_debut}) => {$ListJournees[i].Phase}
								</td>
							</tr>
						{/section}
						<tr>
							<td colspan=2>
								<input type="button" onclick="DuppliListJournees()" name="Duppliquer" value="Appliquer ces paramètres sur les phases sélectionnées" title="Enregistrer les nouveaux paramètres avant de les duppliquer !">
								<hr>
							</td>
						</tr>
					</table>
					{/if}
					<table class='tableau2' width="100%">
						<tr>
							<th class='titreForm' colspan=2 width="100%">
								<label>Ajuster les dates des matchs</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" onclick="AjustDates();" name="Ajuster" value="Ajuster la date des matchs à la date de la journée">
							</td>
						</tr>
					</table>
		        </div>
			</form>			
		</div>	  	   
