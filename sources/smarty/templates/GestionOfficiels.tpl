		&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
		<br>
		<div class="main">
			<form method="POST" action="GestionOfficiels.php" name="formOfficiels" id="formOfficiels" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='idEquipeA' id='idEquipeA' Value=''/>
				<input type='hidden' name='idEquipeB' id='idEquipeB' Value=''/>
				<input type='hidden' name='Pub' id='Pub' Value=''/>
				<input type='hidden' name='Verrou' id='Verrou' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Matchs'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>
				
				<div class='titrePage'>{#Liste_des_Matchs#}</div>
					<table id="formMatch">
						<tr class='filtres cadregris'>
							<td align="center">
								<label for="evenement">{#Filtre_evenement#}</label>
								<br>
								<select name="evenement" id="evenement" onChange="submit();">
									{section name=i loop=$arrayEvenement} 
										<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>{$arrayEvenement[i].Libelle}</Option>
									{/section}
								</select>
							</td>
							<td align="center">
								<label for="comboCompet">{#Filtre_competition#}</label>
								{if $profile <= 6 && $AuthModif == 'O'}
                                    <a href="#" id="InitTitulaireCompet"><img height="22" src="../img/b_update.png" alt="Ré-affecter les joueurs présents pour toute la compétition sélectionnée" title="Ré-affecter les joueurs présents pour toute la compétition sélectionnée" /></a>
								{/if}
								<br>
								<select id="comboCompet" name="comboCompet" onChange="changeCompet();" tabindex="1">
									{section name=i loop=$arrayCompet}
										{if $codeCurrentCompet eq $arrayCompet[i].Code}
											<Option Value="{$arrayCompet[i].Code}" Selected>{$arrayCompet[i].Code} - {$arrayCompet[i].Libelle}</Option>
										{else}
											<Option Value="{$arrayCompet[i].Code}">{$arrayCompet[i].Code} - {$arrayCompet[i].Libelle}</Option>
										{/if}
									{/section}
								</select>
							</td>
							<td align="center">
								<label for="comboJournee2">{#Filtre_journee_phase_poule#}</label>
								<br>
								<select id="comboJournee2" name="comboJournee2" onChange="changeCompet();" tabindex="2">
									<Option Value="*" Selected>Toutes...</Option>
									{section name=i loop=$arrayJourneesAutoriseesFiltre}
										{if $idSelJournee eq $arrayJourneesAutoriseesFiltre[i].Id}
											{if $arrayJourneesAutoriseesFiltre[i].Code_typeclt == 'CP'}
												<Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}" Selected>{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Phase|string_format:"%s"}{$arrayJourneesAutoriseesFiltre[i].Niveau|string_format:"(%s)"}</Option>
											{else}
												<Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}" Selected>{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Date_debut|string_format:" le %s "}{$arrayJourneesAutoriseesFiltre[i].Lieu|string_format:"à %s"}</Option>
											{/if}
										{else}
											{if $arrayJourneesAutoriseesFiltre[i].Code_typeclt == 'CP'}
												<Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}">{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Phase|string_format:"%s"}{$arrayJourneesAutoriseesFiltre[i].Niveau|string_format:"(%s)"}</Option>
											{else}
												<Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}">{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Date_debut|string_format:" le %s "}{$arrayJourneesAutoriseesFiltre[i].Lieu|string_format:"à %s"}</Option>
											{/if}
										{/if}
									{/section}
								</select>
							</td>
							<td align="center">
								<label for="filtreMois">{#Filtre_Date_Terrain#}</label>
								<br>
								<select name="filtreJour" id="filtreJour" onChange="submit();">
										<Option Value="" {if $filtreJour == ''}selected{/if}>---{#Tous#}---</Option>
									 {foreach from=$listeJours key=k item=v}
										<Option Value="{$k}" {if $filtreJour == $k}selected{/if}>{$v}</Option>
									 {/foreach}
							    </select>
								<select name="filtreTerrain" id="filtreTerrain" onChange="submit();">
										<Option Value="" {if $filtreTerrain == ''}selected{/if}>---{#Tous#}---</Option>
										<Option Value="1" {if $filtreTerrain == '1'}selected{/if}>{#Terr#}. 1</Option>
										<Option Value="2" {if $filtreTerrain == '2'}selected{/if}>{#Terr#}. 2</Option>
										<Option Value="3" {if $filtreTerrain == '3'}selected{/if}>{#Terr#}. 3</Option>
										<Option Value="4" {if $filtreTerrain == '4'}selected{/if}>{#Terr#}. 4</Option>
										<Option Value="5" {if $filtreTerrain == '5'}selected{/if}>{#Terr#}. 5</Option>
										<Option Value="6" {if $filtreTerrain == '6'}selected{/if}>{#Terr#}. 6</Option>
										<Option Value="7" {if $filtreTerrain == '7'}selected{/if}>{#Terr#}. 7</Option>
										<Option Value="8" {if $filtreTerrain == '8'}selected{/if}>{#Terr#}. 8</Option>
							    </select>
							</td>
							<td align="center">
								<label for="filtreMois">{#Ordre_tri#}</label>
								<br>
								<select name="orderMatchs" onChange="ChangeOrderMatchs('{$idSelJournee}');">
								{section name=i loop=$arrayOrderMatchs}
									{if $orderMatchs eq $arrayOrderMatchs[i].Key}
										<Option Value="{$arrayOrderMatchs[i].Key}" Selected>{$arrayOrderMatchs[i].Value}</Option>
									{else}
										<Option Value="{$arrayOrderMatchs[i].Key}">{$arrayOrderMatchs[i].Value}</Option>
									{/if}
								{/section}
								</select>
							</td>
						</tr>
					</table>
				<div class='blocTop'>
					{if ($profile <= 6) && $AuthModif == 'O'}
					<table id="formMatch">
						<tr class="hideTr">
							<td colspan="4" align="left" title="Intervalle entre chaque début de match">
								<label for="Intervalle_match">{#Intervale_matchs#}</label>
								<input type="text" size="1" name="Intervalle_match" value="{$Intervalle_match}">
							</td>
							
							
							
						</tr>
						
						<tr class="hideTr">
							<td align="left" id='clickup' style="color:#555555" colspan="2">
								<i><u>{#Masquer_le_formulaire#}</u></i>
							</td>
							<td align="center" colspan=2>
								<input type="button" onclick="Add();" id="addMatch" name="addMatch" value="{#Ajouter#}" tabindex="16">
								<input type="button" {if $idMatch eq '-1'} disabled {/if} onclick="Update();" id="updateMatch" name="updateMatch" value="{#Modifier#}" tabindex="17">
								<input type="button" onclick="Raz();" id="razMatch" name="razMatch" value="{#Annuler#}" tabindex="18">
							</td>
							
						</tr>
						<tr id='clickdown'>
							<td colspan="4" align="left" style="color:#555555"><i><u>{#Afficher_le_formulaire#}</u></i></td>
						</tr>
					</table>
					{/if}
				</div>
				<div class='blocMiddle'>
					<table width=100%>
						<tr>
							<td width=480>
						       	<fieldset>
									<label>{#Selection#}:</label>
									&nbsp;
									<a href="#" {$TropDeMatchs} onclick="setCheckboxes('formJournee', 'checkMatch', true);return false;"><img height="22" src="../img/glyphicons-155-more-checked.png" alt="Sélectionner tous" title="Sélectionner tous" /></a>
									<a href="#" {$TropDeMatchs} onclick="setCheckboxes('formJournee', 'checkMatch', false);return false;"><img height="22" src="../img/glyphicons-155-more-windows.png" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
									{if $profile <= 6 && $AuthModif == 'O'}
										<a href="#" {$TropDeMatchs} onclick="RemoveCheckboxes('formJournee', 'checkMatch')" title="Supprimer la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/glyphicons-17-bin.png" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');publiMultiMatchs();" title="Publier/dépublier la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/oeil2.gif" /></a>
										{if $profile <= 4 && $AuthModif == 'O'}
											<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouPubliMultiMatchs();" title="Verrouiller & Publier la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/oeilverrou2.gif" /></a>
											<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouMultiMatchs();" title="Verrouiller/déverrouiller la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/verrou2.gif" /></a>
										{/if}
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');affectMultiMatchs();" title="Affectation auto des équipes et arbitres pour la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/AffectAuto.gif" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');annulMultiMatchs();" title="Annuler l'affectation auto des équipes et arbitres pour la sélection (supprime équipes et arbitres) {$TropDeMatchsMsg}"><img height="25" src="../img/AnnulAuto.gif" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');changeMultiMatchs();" title="Changer de journée / de phase / de poule pour la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/Chang.gif" border="0"></a>
									{/if}
									<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');this.href='FeuilleMatchMulti.php?listMatch='+document.formJournee.ParamCmd.value;" Target="_blank" title="Feuilles de Matchs pour la sélection {$TropDeMatchsMsg}"><img height="25" src="../img/pdf2.png" /></a>
								</fieldset>
							</td>
							<td width=450>
						       	<fieldset>
									<label>{#Tous_les_matchs#}:</label>
									&nbsp;
									<a href="FeuilleListeMatchs.php" {$TropDeMatchs} Target="_blank" title="Liste des Matchs {$TropDeMatchsMsg}"><img height="25" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="FeuilleListeMatchsEN.php" {$TropDeMatchs} Target="_blank" title="Game list (EN) {$TropDeMatchsMsg}"><img height="25" src="../img/ListeEN.gif" /></a>
									&nbsp;
									<a href="FeuilleMatchMulti.php?listMatch={$listMatch}" {$TropDeMatchs} Target="_blank" title="Toutes les feuilles de Matchs {$TropDeMatchsMsg}"><img height="25" src="../img/pdf2.png" /></a>
									&nbsp;
									<a href="tableau_tbs.php" title="Export tableau des matchs (LibreOffice / Excel)"><img height="25" src="../img/ods.png" /></a>
									&nbsp;
									<a href="../PdfListeMatchs.php" {$TropDeMatchs} Target="_blank" title="Liste publique des Matchs {$TropDeMatchsMsg}"><img height="25" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="../PdfListeMatchsEN.php" {$TropDeMatchs} Target="_blank" title="Public Game list (EN) {$TropDeMatchsMsg}"><img height="25" src="../img/ListeEN.gif" /></a>
								</fieldset>
							</td>
							<td>
								&nbsp;&nbsp;
								<span id='reachspan'><i>{#Surligner#}:</i></span><input type=text name='reach' id='reach' size='20'>
							</td>
						</tr>
					</table>
				</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>{#Num#}</th>
									<th>{#Heure#}</th>
									<th>Cat.</th>
									{if $PhaseLibelle == 1}
										<th>{#Phase#}</th>
									{else}
										<th>{#Lieu#}</th>
									{/if}
									<th>{#Terr#}</th>
									<th>{#Equipe#} A<br>
                                        {#Equipe#} B</th>
									<th><img height="25" src="../img/verrou2.gif"></th>
									<th>{#Arbitre#} 1</th>	
									<th>{#Arbitre#} 2</th>	
									<th>{#Ligne#}</th>	
									<th>{#Ligne#}</th>	
									<th>{#Timeshoot#}</th>	
									<th>{#Secretaire#}</th>	
									<th>{#Chronometre#}</th>	
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayMatchs}
									<tr class='{cycle values="impair,pair"} {$arrayMatchs[i].StdOrSelected}'>
										<td><input type="checkbox" name="checkMatch" value="{$arrayMatchs[i].Id}" id="checkDelete{$smarty.section.i.iteration}" /></td>
										{if $arrayMatchs[i].MatchAutorisation == 'O' && $profile <= 6 && $AuthModif == 'O'}
											{if $arrayMatchs[i].Validation != 'O'}
												<td>{$arrayMatchs[i].Numero_ordre}</td>
                                                <td><span class='date'>{$arrayMatchs[i].Date_match}</span><br>
                                                    {$arrayMatchs[i].Heure_match}</td>
                                                <td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
                                                {if $PhaseLibelle == 1}
                                                    <td><span class="phase">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
                                                {else}
                                                    <td><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
                                                {/if}
                                                <td><span class='terrain'>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</span></td>
                                                <td>
                                                    <span>{$arrayMatchs[i].EquipeA}</span>
                                                    <br><br>
                                                    <span>{$arrayMatchs[i].EquipeB}</span>
                                                </td>
                                                <td>
                                                    <img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif">
                                                    {if $arrayMatchs[i].Statut == 'ON'}
                                                        <span class="statutMatchOn">{$arrayMatchs[i].Periode}</span>
                                                        <span class="scoreProvisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                                    {elseif $arrayMatchs[i].Statut == 'END'}
                                                        <span class="statutMatchOn">{$arrayMatchs[i].Statut}</span>
                                                        <span class="scoreProvisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                                    {else}
                                                        <span class="scoreProvisoire">{$arrayMatchs[i].Statut}</span>
                                                    {/if}
                                                </td>
												<td>
													<span class="directInput arbitre{if $arrayMatchs[i].Arbitre_principal != '-1' && $arrayMatchs[i].Matric_arbitre_principal == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_principal" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_principal-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_principal|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>
													<span class="directInput arbitre{if $arrayMatchs[i].Arbitre_secondaire != '-1' && $arrayMatchs[i].Matric_arbitre_secondaire == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_secondaire" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_secondaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_secondaire|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>
													<span class="directInput" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Secretaire" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Secretaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Secretaire}</span>
												</td>
												<td>
													<span class="directInput" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Chronometre" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Secretaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Chronometre}</span>
												</td>
												<td>
													<span class="directInput" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Timeshoot" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Secretaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Timeshoot}</span>
												</td>
												<td>
													<span class="directInput" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Ligne1" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Secretaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Ligne1}</span>
												</td>
												<td>
													<span class="directInput" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Ligne2" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Secretaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Ligne2}</span>
												</td>
											{else}
												<td>{$arrayMatchs[i].Numero_ordre}</td>
												<td><span class='date'>{$arrayMatchs[i].Date_match}</span><br>
													{$arrayMatchs[i].Heure_match}</td>
                                                <td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
												{if $PhaseLibelle == 1}
													<td>{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
												{else}
													<td>{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
												{/if}
												<td><span class='terrain'>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</span></td>
												<td>
                                                    {$arrayMatchs[i].EquipeA}
                                                    <br><br>
                                                    {$arrayMatchs[i].EquipeB}
                                                </td>
												<td class='color{$arrayMatchs[i].Validation}2'>
													{if $profile <= 6 && $AuthModif == 'O'}
														<img class="verrouMatch" data-valeur="{$arrayMatchs[i].Validation}" data-id="{$arrayMatchs[i].Id}" height="25" src="../img/verrou2{$arrayMatchs[i].Validation}.gif" title="{if $arrayMatchs[i].Validation == 'O'}Validé / verrouillé (score public){else}Non validé (score non public){/if}" height="24">
													{else}
														<img height="24" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
													{/if}
													{if $arrayMatchs[i].Statut == 'ON'}
														<span class="statutMatchOn" title="Période {$arrayMatchs[i].Periode}">{$arrayMatchs[i].Periode}</span>
														<span class="scoreProvisoire" title="Score provisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{elseif $arrayMatchs[i].Statut == 'END'}
														<span class="statutMatchOn" title="Match terminé">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="Score provisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{else}
														<span class="scoreProvisoire" title="Match en attente">{$arrayMatchs[i].Statut}</span>
													{/if}
												</td>
												<td>
													<span class="directInputOff arbitre{if $arrayMatchs[i].Arbitre_principal != '-1' && $arrayMatchs[i].Matric_arbitre_principal == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_principal" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_principal-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_principal|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>
													<span class="directInputOff arbitre{if $arrayMatchs[i].Arbitre_secondaire != '-1' && $arrayMatchs[i].Matric_arbitre_secondaire == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_secondaire" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_secondaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_secondaire|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
											{/if}
										{else}
											<td>{$arrayMatchs[i].Numero_ordre}</td>
                                            <td><span class='date'>{$arrayMatchs[i].Date_match}</span><br>
												{$arrayMatchs[i].Heure_match}</td>
											<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
											{if $PhaseLibelle == 1}
												<td><span class="phase">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
												<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>
											{else}
												<td colspan=2><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
											{/if}
											<td><span class='terrain'>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</span></td>
											<td>
                                                {$arrayMatchs[i].EquipeA}
                                                <br><br>
                                                {$arrayMatchs[i].EquipeB}
                                            </td>
											<td>
												<img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif">
                                                {if $arrayMatchs[i].Statut == 'ON'}
                                                    <span class="statutMatchOn">{$arrayMatchs[i].Periode}</span>
                                                    <span class="scoreProvisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                                {elseif $arrayMatchs[i].Statut == 'END'}
                                                    <span class="statutMatchOn">{$arrayMatchs[i].Statut}</span>
                                                    <span class="scoreProvisoire">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                                {else}
                                                    <span class="scoreProvisoire">{$arrayMatchs[i].Statut}</span>
                                                {/if}
											</td>
											<td>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
											<td>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
										{/if}
									</tr>
								{/section}
							</tbody>
						</table>
						<br />
					</div>
						{assign var='nbmatch' value=$smarty.section.i.iteration-1}
						{if $nbmatch > 0}{#Nb_matchs#} : {$nbmatch}{/if}
				</div>
			</form>
		</div>
		