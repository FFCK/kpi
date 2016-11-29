	&nbsp;(<a href="Admin.php">Retour</a>)
	
	<div class="main">
		<form method="POST" action="GestionCalendrier.php" name="formCalendrier" id="formCalendrier" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>
			<input type='hidden' name='Pub' Value=''/>
			<input type='hidden' name='idEvenement' Value='{$idEvenement}'/>

			<div class='titrePage'>Journées / phases</div>
			<div class='blocTop'>
				<table width="100%">
					<tr>
						<td>
							<label for="evenement">Evénement :</label>
							<select name="evenement" id="evenement" onChange="changeEvenement();">
								{section name=i loop=$arrayEvenement} 
									<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>{$arrayEvenement[i].Libelle}</Option>
								{/section}
						    </select>
						</td>
						<td colspan=2>
							<label for="competition">Compétition :</label>
							<select name="competition" id="competition" onChange="changeCompetition();">
								{section name=i loop=$arrayCompetition} 
									<Option Value="{$arrayCompetition[i].Code}" {$arrayCompetition[i].Selection}>{$arrayCompetition[i].Libelle}</Option>
								{/section}
						    </select>
						</td>
						<td align="right" rowspan=2>
							{if $profile <= 3 && $AuthModif == 'O'}
								{if $modeEvenement eq '1'}
							    	Mode normal<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="1" Checked>
							    	<br>Mode association evts<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="2">		
							    {else}
							    	Mode normal<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="1">
							    	<br><font color="FF0000">Mode association evts</font><input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="2" Checked>		
								{/if}
							{else}
								&nbsp;
							{/if}
						</td>
					</tr>
					<tr>
						<td>
							<label for="filtreMois">Mois :</label>
							<select name="filtreMois" onChange="document.formCalendrier.submit();">
									<Option Value="" {if $filtreMois == ''}selected{/if}>---Tous---</Option>
									<Option Value="1" {if $filtreMois == '1'}selected{/if}>Janvier</Option>
									<Option Value="2" {if $filtreMois == '2'}selected{/if}>Février</Option>
									<Option Value="3" {if $filtreMois == '3'}selected{/if}>Mars</Option>
									<Option Value="4" {if $filtreMois == '4'}selected{/if}>Avril</Option>
									<Option Value="5" {if $filtreMois == '5'}selected{/if}>Mai</Option>
									<Option Value="6" {if $filtreMois == '6'}selected{/if}>Juin</Option>
									<Option Value="7" {if $filtreMois == '7'}selected{/if}>Juillet</Option>
									<Option Value="8" {if $filtreMois == '8'}selected{/if}>Août</Option>
									<Option Value="9" {if $filtreMois == '9'}selected{/if}>Septembre</Option>
									<Option Value="10" {if $filtreMois == '10'}selected{/if}>Octobre</Option>
									<Option Value="11" {if $filtreMois == '11'}selected{/if}>Novembre</Option>
									<Option Value="12" {if $filtreMois == '12'}selected{/if}>Décembre</Option>
						    </select>
							<label for="competitionOrder"> Trié par :</label>
							<select name="competitionOrder" onChange="changeCompetitionOrder();">
								{section name=i loop=$arrayCompetitionOrder} 
									<Option Value="{$arrayCompetitionOrder[i].Code}" {$arrayCompetitionOrder[i].Selection}>{$arrayCompetitionOrder[i].Libelle}</Option>
								{/section}
						    </select>
						</td>
						<td>
							<a href="GestionJournee.php?idJournee=*"><img align="absbottom" height="20" src="../img/b_match.png" alt="Tous les matchs" title="Tous les matchs"> Voir tous les Matchs</a>
						</td>
						{if $profile <= 4 && $AuthModif == 'O'}
							<td>
								<a href="#" onclick="ParamJournee(0);"><img align="bottom" height="20" src="../img/glyphicons-191-plus-sign.png" alt="Ajouter une journee" title="Ajouter une journee"> Ajouter une Journée</A></td>
							</td>
						{/if}
					</tr>
				</table>
			</div>

			{if $profile <= 4 && $AuthModif == 'O'}
				<div class='blocMiddle'>
					<table width="100%">
						<tr>
							<td>
								Sélection :&nbsp;
								<a href="#" onclick="setCheckboxes('formCalendrier', 'checkJournee', true);return false;"><img height="22" src="../img/glyphicons-155-more-checked.png" alt="Sélectionner tous" title="Sélectionner tous" border="0"></a>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formCalendrier', 'checkJournee', false);return false;"><img height="22" src="../img/glyphicons-155-more-windows.png" alt="Sélectionner aucun" title="Sélectionner aucun" border="0"></a>
								&nbsp;
								<a href="#" onclick="SelectedCheckboxes('formCalendrier', 'checkJournee');publiMultiJournees();" alt="Publier/dépublier les journées/phases cochées" title="Publier/dépublier les journées/phases cochées"><img height="25" src="../img/oeil2.gif" alt="Publier/Dépublier la sélection" title="Publier/Dépublier la sélection" border="0"></a>
								&nbsp;
								<a href="#" onclick="RemoveCheckboxes('formCalendrier', 'checkJournee')" alt="Supprimer les journées cochées" title="Supprimer les journées/phases cochées"><img height="25" src="../img/glyphicons-17-bin.png" alt="Supprimer la sélection" title="Supprimer la sélection" border="0"></a>
							</td>
						</tr>
					</table>
				</div>
			{/if}
			<div class='blocBottom'>
				<div class='blocTable'>
					<table class='tableau'>
						<thead>
							<tr>
								{if $profile <= 3 && $AuthModif == 'O'}
									<th>&nbsp;</th>
								{/if}
								<th width=18><img height="18" src="../img/oeil2.gif" alt="Publier ?" title="Publier ?" border="0"></th>
								<th>N°</th>
								<th>&nbsp;</th>
								<th>Compét.</th>
								<th>Niv.</th>
								<th>Type</th>
								<th>Nom</th>
								<th>Date(s)</th>
								<th>Lieu</th>
								<th>Dpt.</th>
								<th colspan="2">Officiels</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						
						<tbody>
							{section name=i loop=$arrayJournees} 
								<tr class='{cycle values="impair,pair"}'>
									{if $modeEvenement eq '1'}
										{if $arrayJournees[i].Autorisation eq true && $profile <= 4 && $AuthModif == 'O'}
											{if $profile <= 3 && $AuthModif == 'O'}
												<td><input type="checkbox" name="checkJournee" value="{$arrayJournees[i].Id}" id="checkDelete{$smarty.section.i.iteration}" /></td>
											{/if}
											<td class="color{$arrayJournees[i].Publication|default:'N'}2">
												<img class="publiJournee" data-valeur="{$arrayJournees[i].Publication}" data-id="{$arrayJournees[i].Id}" height="25" src="../img/oeil2{$arrayJournees[i].Publication|default:'N'}.gif" alt="Publier O/N" title="{if $arrayJournees[i].Publication == 'O'}Public{else}Non public{/if}" />
											</td>
											<td align="left">{$arrayJournees[i].Id}</td> 
											<td width=70>
												<a href="#" onclick="ParamJournee({$arrayJournees[i].Id});"><img height="16" src="../img/glyphicons-31-pencil.png" alt="Modifier" title="Modifier les Paramètres de la journée" border="0"></a>
												<a href="#" onclick="duplicate({$arrayJournees[i].Id});"><img height="20" src="../img/glyphicons-511-duplicate.png" alt="Dupliquer" title="Dupliquer" border="0"></a>
												<a href='GestionJournee.php?idJournee={$arrayJournees[i].Id}'><img height="20" src="../img/b_match.png" alt="Accès aux matchs - journée {$arrayJournees[i].Id}" title="Accès aux matchs - journée {$arrayJournees[i].Id}" border="0"></a>
											</td>
										{else}
											{if $profile <= 3 && $AuthModif == 'O'}
												<td>&nbsp;</td>
											{/if}
											<td class="color{$arrayJournees[i].Publication|default:'N'}2">
												<img height="25" src="../img/oeil2{$arrayJournees[i].Publication|default:'N'}.gif" alt="Publier O/N" title="Publier O/N" border="0">
											</td>
											<td align="left">{$arrayJournees[i].Id}</td> 
											<td align="left">
												<a href='GestionJournee.php?idJournee={$arrayJournees[i].Id}'>
													<img align="absbottom" height="20" src="../img/b_match.png" alt="Accès aux matchs - journée {$arrayJournees[i].Id}" title="Accès aux matchs - journée {$arrayJournees[i].Id}" border="0">
												</a> 
											</td>
										{/if}
									{elseif $arrayJournees[i].Autorisation eq true && $profile <= 4 && $AuthModif == 'O'}
										{if $profile <= 3 && $AuthModif == 'O'}
											<td>&nbsp;</td>
										{/if}
										<td class="color{$arrayJournees[i].Publication|default:'N'}2">
											<img height="25" src="../img/oeil2{$arrayJournees[i].Publication|default:'N'}.gif" alt="Publier O/N" title="Publier O/N">
										</td>
										<td align="left">{$arrayJournees[i].Id}</td> 
										<td class="rouge">
											<input type="checkbox" class="checkassoc2" data-id="{$arrayJournees[i].Id}" {$arrayJournees[i].Checked} />
										</td>
									{else}
										{if $profile <= 3 && $AuthModif == 'O'}
											<td>&nbsp;</td>
										{/if}
										<td class="color{$arrayJournees[i].Publication|default:'N'}2">
											<img height="25" src="../img/oeil2{$arrayJournees[i].Publication|default:'N'}.gif" alt="Publier O/N" title="Publier O/N">
										</td>
										<td align="left">{$arrayJournees[i].Id}</td> 
										<td class="rouge">&nbsp;</td>
									{/if}
									
									<td>{$arrayJournees[i].Code_competition}{if $arrayJournees[i].Phase != ''} - {$arrayJournees[i].Phase}{/if}</td>
									<td>{$arrayJournees[i].Niveau}</td>
									{if $profile <= 3 && $AuthModif == 'O'}
										<td><img class="typeJournee" data-valeur="{$arrayJournees[i].Type}" data-id="{$arrayJournees[i].Id}" src="../img/type{$arrayJournees[i].Type}.png" title="{if $arrayJournees[i].Type == 'C'}Classement{else}Elimination{/if}" /></td>
									{else}
										<td><img src="../img/type{$arrayJournees[i].Type}.png" title="{if $arrayJournees[i].Type == 'C'}Classement{else}Elimination{/if}" /></td>
									{/if}
									<td>{$arrayJournees[i].Nom}</td>
									<td>{$arrayJournees[i].Date_debut}
									{if $arrayJournees[i].Date_debut != $arrayJournees[i].Date_fin} - {$arrayJournees[i].Date_fin}{/if}</td>
									<td>{$arrayJournees[i].Lieu|default:'&nbsp;'}</td>
									<td>{$arrayJournees[i].Departement|default:'&nbsp;'}</td>
                                                                        <td><a href="GestionInstances.php?idJournee={$arrayJournees[i].Id}" title="Officiels"><img height="18" src="../img/glyphicons-28-search.png" alt="Officiels"></a>
                                                                        <td>
										{if $arrayJournees[i].Responsable_insc != ''}RC: {$arrayJournees[i].Responsable_insc}<br />{/if}
										{if $arrayJournees[i].Responsable_R1 != ''}R1: {$arrayJournees[i].Responsable_R1}<br />{/if}
										{if $arrayJournees[i].Delegue != ''}Délégué: {$arrayJournees[i].Delegue}<br />{/if}
										{if $arrayJournees[i].ChefArbitre != ''}Chef arbitres: {$arrayJournees[i].ChefArbitre}{/if}
									</td>
									
									{if $arrayJournees[i].Autorisation eq true && $profile <= 4 && $AuthModif == 'O'}
										<td><a href="#" onclick="RemoveCheckbox('formCalendrier', '{$arrayJournees[i].Id}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" alt="Supprimer" title="Supprimer"></a></td>
									{else}
										<td>&nbsp;</td>
									{/if}
								</tr>
							{/section}
						</tbody>
					</table>
				</div>
	        </div>
		</form>			
				
	</div>	  	   
